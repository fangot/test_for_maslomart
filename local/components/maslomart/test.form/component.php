<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CustomFormComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        $this->__includeComponent();
    }

    protected function includeComponentClass()
    {
        if ($this->initComponentTemplate()) {
            $this->__prepareComponentParams($this->arParams);
            
            $component = new CustomForm();
            $component->initComponent($this->getName());
            $component->setTemplate($this->getTemplate());
            $component->setTemplateName($this->getTemplateName());
            $component->arParams = $this->arParams;
            
            $component->executeComponent();
        }
    }
}
?>