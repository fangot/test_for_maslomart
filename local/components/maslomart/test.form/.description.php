<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arComponentDescription = array(
    "NAME" => Loc::getMessage("CUSTOM_FORM_NAME"),
    "DESCRIPTION" => Loc::getMessage("CUSTOM_FORM_DESCRIPTION"),
    "ICON" => "/images/icon.gif",
    "PATH" => array(
        "ID" => "maslomart",
        "NAME" => "Масломарт"
    ),
    "CACHE_PATH" => "Y",
    "COMPLEX" => "N"
);
?>