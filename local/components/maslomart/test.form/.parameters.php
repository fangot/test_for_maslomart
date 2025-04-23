<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arComponentParameters = array(
    "PARAMETERS" => array(
        "EMAIL_TO" => array(
            "NAME" => Loc::getMessage("CUSTOM_FORM_EMAIL_TO"),
            "TYPE" => "STRING",
            "DEFAULT" => "test@test.test",
            "PARENT" => "BASE",
        ),
        "CATEGORIES" => array(
            "NAME" => Loc::getMessage("CUSTOM_FORM_CATEGORIES"),
            "TYPE" => "STRING",
            "ROWS" => "10",
            "COLS" => "50",
            "DEFAULT" => "Масла, автохимия, фильтры\nАвтоаксессуары, обогреватели, запчасти, сопутствующие товары\nШины, диски",
            "PARENT" => "BASE",
        ),
        "REQUEST_TYPES" => array(
            "NAME" => Loc::getMessage("CUSTOM_FORM_REQUEST_TYPES"),
            "TYPE" => "STRING",
            "ROWS" => "10",
            "COLS" => "50",
            "DEFAULT" => "Запрос цены и сроков поставки\nПополнение складов\nСпецзаказ",
            "PARENT" => "BASE",
        ),
        "WAREHOUSES" => array(
            "NAME" => Loc::getMessage("CUSTOM_FORM_WAREHOUSES"),
            "TYPE" => "STRING",
            "ROWS" => "10",
            "COLS" => "50",
            "DEFAULT" => "Основной склад\nДополнительный склад",
            "PARENT" => "BASE",
        ),
        "BRANDS" => array(
            "NAME" => Loc::getMessage("CUSTOM_FORM_BRANDS"),
            "TYPE" => "STRING",
            "ROWS" => "10",
            "COLS" => "50",
            "DEFAULT" => "Бренд 1\nБренд 2\nБренд 3",
            "PARENT" => "BASE",
        ),
        "OK_TEXT" => array(
            "NAME" => Loc::getMessage("CUSTOM_FORM_OK_TEXT"),
            "TYPE" => "STRING",
            "DEFAULT" => "Ваша заявка успешно отправлена!",
        )
    )
);
?>