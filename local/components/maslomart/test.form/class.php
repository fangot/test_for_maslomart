<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Mail\Event;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

class CustomForm extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        Loc::loadMessages(__FILE__);
        $arParams['EMAIL_TO'] = trim($arParams['EMAIL_TO']);
        $arParams['AJAX_MODE'] = $arParams['AJAX_MODE'] === 'Y';
        return $arParams;
    }

    public function executeComponent()
    {
        $request = $this->getRequestObject();
        
        if ($request->isPost() && check_bitrix_sessid()) {
            $this->processForm($request);
            
            if ($this->arParams['AJAX_MODE'] && $this->isAjaxRequest()) {
                $this->sendAjaxResponse();
                return;
            }
            
            if ($this->arResult['SUCCESS']) {
                LocalRedirect($this->arParams['SEF_FOLDER'] ?: $this->getCurrentPage());
            }
        }
        
        $this->prepareResult($request);
        $this->includeComponentTemplate();
    }

    protected function getRequestObject()
    {
        try {
            return Context::getCurrent()->getRequest();
        } catch (\Exception $e) {
            return Application::getInstance()->getContext()->getRequest();
        }
    }

    protected function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function getCurrentPage()
    {
        global $APPLICATION;
        return $APPLICATION->GetCurPage();
    }

    protected function sendAjaxResponse()
    {
        global $APPLICATION;
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $APPLICATION->RestartBuffer();
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        
        $response = [
            'success' => $this->arResult['SUCCESS'],
            'errors' => $this->arResult['ERRORS'] ?? [],
            'message' => $this->arResult['SUCCESS'] ? ($this->arParams['OK_TEXT'] ?? Loc::getMessage("CUSTOM_FORM_SUCCESS_SEND")) : Loc::getMessage("CUSTOM_FORM_ERROR_SEND_FORM")
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        exit();
    }

    protected function prepareResult($request)
    {
        $this->arResult = [
            'CATEGORIES' => $this->parseMultilineParam(
                !empty($this->arParams['CATEGORIES']) 
                ? $this->arParams['CATEGORIES'] 
                : $this->getDefaultParam('CATEGORIES')
            ),
            'REQUEST_TYPES' => $this->parseMultilineParam(
                !empty($this->arParams['REQUEST_TYPES']) 
                ? $this->arParams['REQUEST_TYPES'] 
                : $this->getDefaultParam('REQUEST_TYPES')
            ),
            'WAREHOUSES' => $this->parseMultilineParam(
                !empty($this->arParams['WAREHOUSES']) 
                ? $this->arParams['WAREHOUSES'] 
                : $this->getDefaultParam('WAREHOUSES')
            ),
            'BRANDS' => $this->parseMultilineParam(
                !empty($this->arParams['BRANDS']) 
                ? $this->arParams['BRANDS'] 
                : $this->getDefaultParam('BRANDS')
            ),
            'FORM_DATA' => $request->getPostList()->toArray(),
            'SUCCESS' => false,
            'ERRORS' => []
        ];
    }

    protected function parseMultilineParam($paramValue)
    {
        $result = array();
        $lines = preg_split('/\r\n|\r|\n/', $paramValue);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $result[md5($line)] = $line;
            }
        }
        
        return $result;
    }

    protected function processForm($request)
    {
        $isAjax = $request->get('is_ajax') === 'Y' || $request->isAjaxRequest();
        
        $errors = $this->validateForm($request);
        
        if (empty($errors)) {
            $this->sendEmail($request);
            $this->arResult['SUCCESS'] = true;
        } else {
            $this->arResult['ERRORS'] = $errors;
            $this->arResult['SUCCESS'] = false;
        }
        
        if ($isAjax) {
            $this->sendAjaxResponse();
        }
    }

    protected function validateForm($request)
    {
        $errors = [];
        
        if (empty($request->getPost('title'))) {
            $errors[] = Loc::getMessage("CUSTOM_FORM_EMPTY_TITLE");
        }
        
        if (empty($request->getPost('category'))) {
            $errors[] = Loc::getMessage("CUSTOM_FORM_EMPTY_CATEGORIES");
        }
        
        if (empty($request->getPost('request_type'))) {
            $errors[] = Loc::getMessage("CUSTOM_FORM_EMPTY_REQUEST_TYPES");
        }
        
        $items = $request->getPost('items');
        if (empty($items) || !is_array($items)) {
            $errors[] = Loc::getMessage("CUSTOM_FORM_EMPTY_ITEMS");
        } else {
            foreach ($items as $index => $item) {
                if (isset($item['brand']) && empty($item['brand'])) {
                    $errors[] = Loc::getMessage("CUSTOM_FORM_EMPTY_ITEM_BRAND") . ($index + 1);
                }
                if (isset($item['name']) && empty($item['name'])) {
                    $errors[] = Loc::getMessage("CUSTOM_FORM_EMPTY_ITEM_NAME") . ($index + 1);
                }
            }
        }
        
        return $errors;
    }

    protected function sendEmail($request)
    {
        try {
            $items = $request->getPost('items') ?: [];
            
            if (empty($items) || !is_array($items)) {
                throw new \Exception(Loc::getMessage("CUSTOM_FORM_ERROR_ITEMS"));
            }
            $emailData = [
                'TITLE' => htmlspecialcharsbx($request->getPost('title')),
                'CATEGORY' => htmlspecialcharsbx($request->getPost('category')),
                'REQUEST_TYPE' => htmlspecialcharsbx($request->getPost('request_type')),
                'WAREHOUSE' => htmlspecialcharsbx($request->getPost('warehouse')),
                'ITEMS' => $this->prepareItems($items),
                'COMMENT' => htmlspecialcharsbx($request->getPost('comment')),
                'EMAIL_TO' => $this->arParams['EMAIL_TO'],
                'EMAIL_FROM' => COption::GetOptionString('main', 'email_from'),
                'SITE_NAME' => $_SERVER['SERVER_NAME'],
                'SERVER_NAME' => $_SERVER['SERVER_NAME']
            ];

            $emailBody = $this->renderEmailTemplate($emailData);
            $emailSubject = Loc::getMessage("CUSTOM_FORM_MAIL_TITLE") . ': ' . $emailData['TITLE'];
            $files = $this->prepareAttachments();

            $sendResult = CEvent::Send('FEEDBACK_FORM', SITE_ID, [
                'EMAIL_TO' => $emailData['EMAIL_TO'],
                'BODY' => $emailBody,
                'SUBJECT' => $emailSubject,
            ], 'Y', '', $files);

            if (!$sendResult) {
                throw new \Exception(Loc::getMessage("CUSTOM_FORM_ERROE_CEVENT_SEND"));
            }

            return true;
        } catch (\Exception $e) {
            AddMessage2Log(Loc::getMessage("CUSTOM_FORM_ERROR_SEND_MAIL") . ": " . $e->getMessage(), 'custom.form');
            
            if ($request->get('is_ajax') === 'Y' || $request->isAjaxRequest()) {
                $this->arResult['ERRORS'][] = Loc::getMessage("CUSTOM_FORM_ERROR_SEND_MAIL");
                $this->arResult['SUCCESS'] = false;
                $this->sendAjaxResponse();
            }
            
            return false;
        }
    }

    protected function prepareItems($items)
    {
        $prepared = [];
        $tmp = [];
        foreach ($items as $i => $item) {
            if (isset($item['brand'])) {
               $tmp['brand'] = htmlspecialcharsbx($item['brand'] ?? '');
            }
            if (isset($item['name'])) {
               $tmp['name'] = htmlspecialcharsbx($item['name'] ?? '');
            }
            if (isset($item['packing'])) {
               $tmp['packing'] = htmlspecialcharsbx($item['packing'] ?? '');
            }
            if (isset($item['client'])) {
               $tmp['client'] = htmlspecialcharsbx($item['client'] ?? '');
            }
            if (isset($item['quantity'])) {
               $tmp['quantity'] = (int)($item['quantity'] ?? 0);
            }
            if ($i % 4 == 0 && $i > 0) {
                $prepared[] = $tmp;
                $tmp = [];
            }
        }
        return $prepared;
    }

    protected function prepareAttachments()
    {
        $files = [];
        if (!empty($_FILES['files']['tmp_name'])) {
            foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['files']['error'][$index] === UPLOAD_ERR_OK && is_uploaded_file($tmpName)) {
                    $files[] = [
                        'path' => $tmpName,
                        'name' => $_FILES['files']['name'][$index],
                        'type' => $_FILES['files']['type'][$index],
                    ];
                }
            }
        }
        return $files;
    }

    protected function renderEmailTemplate($emailData)
    {
        ob_start();

        $this->InitComponentTemplate();
        $templateFile = $_SERVER['DOCUMENT_ROOT'] . $this->GetTemplate()->GetFolder() . '/email_template.php';
        if (file_exists($templateFile)) {
            extract($emailData, EXTR_SKIP);
            include $templateFile;
        }
        
        return ob_get_clean();
    }
}
?>