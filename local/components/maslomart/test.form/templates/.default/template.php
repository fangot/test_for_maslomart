<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
CJSCore::Init(array('fx'));

$this->addExternalCss('https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
$this->addExternalJS('https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js');

$this->addExternalCss($templateFolder . '/style.css');
$this->addExternalJS($templateFolder . '/script.js');

echo '<script>BX.message(' . CUtil::PhpToJSObject([
    'BRANDS_DATA' => $arResult['BRANDS']
]) . ');</script>';
?>

<div class="custom-form-container">
    <?php if ($arResult['SUCCESS']): ?>
        <div class="alert alert-success"><?= $arParams['OK_TEXT'] ?></div>
    <?php else: ?>
        <?php if (!empty($arResult['ERRORS'])): ?>
            <div class="alert alert-danger">
                <?php foreach ($arResult['ERRORS'] as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST"
              action="<?= POST_FORM_ACTION_URI ?>" 
              class="needs-validation" novalidate
              enctype="multipart/form-data" 
              id="custom-form">
            <?= bitrix_sessid_post() ?>

            <!-- Заголовок заявки -->
            <div class="mb-3">
                <label for="title" class="form-label"><?= Loc::getMessage("CUSTOM_FORM_TITLE") ?> *</label>
                <input type="text" class="form-control" id="title" name="title" required value="<?= htmlspecialcharsbx($arResult['FORM_DATA']['title'] ?? '') ?>">
                <div class="invalid-feedback"><?= Loc::getMessage("CUSTOM_FORM_TITLE_ERROR") ?></div>
            </div>

            <!-- Категория -->
            <div class="mb-3">
                <label class="form-label"><?= Loc::getMessage("CUSTOM_FORM_CATEGORIES") ?> *</label>
                <?php foreach ($arResult['CATEGORIES'] as $code => $name): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="category" id="category_<?= $code ?>" value="<?= $name ?>" required>
                        <label class="form-check-label" for="category_<?= $code ?>">
                            <?= htmlspecialcharsbx($name) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
                <div class="invalid-feedback"><?= Loc::getMessage("CUSTOM_FORM_CATEGORIES_ERROR") ?></div>
            </div>

            <!-- Вид заявки -->
            <div class="mb-3">
                <label class="form-label"><?= Loc::getMessage("CUSTOM_FORM_REQUEST_TYPES") ?> *</label>
                <?php foreach ($arResult['REQUEST_TYPES'] as $code => $name): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="request_type" id="request_type_<?= $code ?>" value="<?= $name ?>" required>
                        <label class="form-check-label" for="request_type_<?= $code ?>">
                            <?= htmlspecialcharsbx($name) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
                <div class="invalid-feedback"><?= Loc::getMessage("CUSTOM_FORM_REQUEST_TYPES_ERROR") ?></div>
            </div>

            <!-- Склад поставки -->
            <div class="mb-3">
                <label for="warehouse" class="form-label"><?= Loc::getMessage("CUSTOM_FORM_WAREHOUSES") ?></label>
                <select class="form-select" id="warehouse" name="warehouse">
                    <option value=""><?= Loc::getMessage("CUSTOM_FORM_WAREHOUSES_NONE") ?></option>
                    <?php foreach ($arResult['WAREHOUSES'] as $code => $name): ?>
                        <option value="<?= $name ?>" <?= ($arResult['FORM_DATA']['warehouse'] ?? '') === $code ? 'selected' : '' ?>>
                            <?= $name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Состав заявки -->
            <div class="mb-3">
                <label class="form-label"><?= Loc::getMessage("CUSTOM_FORM_ITEMS") ?> *</label>
                <div class="table-responsive">
                    <table class="table table-bordered" id="request-items">
                        <thead>
                            <tr>
                                <th><?= Loc::getMessage("CUSTOM_FORM_ITEMS_BRAND") ?></th>
                                <th><?= Loc::getMessage("CUSTOM_FORM_ITEMS_NAME") ?></th>
                                <th><?= Loc::getMessage("CUSTOM_FORM_ITEMS_QUANTITY") ?></th>
                                <th><?= Loc::getMessage("CUSTOM_FORM_ITEMS_PACKING") ?></th>
                                <th><?= Loc::getMessage("CUSTOM_FORM_ITEMS_CLIENT") ?></th>
                                <th width="40"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($arResult['FORM_DATA']['items'])): ?>
                                <?php foreach ($arResult['FORM_DATA']['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <select class="form-select" name="items[][brand]" required>
                                                <option value=""><?= Loc::getMessage("CUSTOM_FORM_ITEMS_BRAND_NONE") ?></option>
                                                <?php foreach ($arResult['BRANDS'] as $code => $name): ?>
                                                    <option value="<?= $name ?>"><?= htmlspecialcharsbx($name) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="items[][name]" value="<?= htmlspecialcharsbx($item['name'] ?? '') ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="items[][quantity]" value="<?= htmlspecialcharsbx($item['quantity'] ?? '') ?>">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="items[][packing]" value="<?= htmlspecialcharsbx($item['packing'] ?? '') ?>">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="items[][client]" value="<?= htmlspecialcharsbx($item['client'] ?? '') ?>">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-item">×</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td>
                                        <select class="form-select" name="items[][brand]" required>
                                            <option value=""><?= Loc::getMessage("CUSTOM_FORM_ITEMS_BRAND_NONE") ?></option>
                                            <?php foreach ($arResult['BRANDS'] as $code => $name): ?>
                                                <option value="<?= $name ?>"><?= htmlspecialcharsbx($name) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="items[][name]" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="items[][quantity]">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="items[][packing]">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="items[][client]">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-item">×</button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <button type="button" id="add-item" class="btn btn-secondary btn-sm"><?= Loc::getMessage("CUSTOM_FORM_ITEMS_ADD") ?></button>
            </div>

            <!-- Файлы -->
            <div class="mb-3">
                <label for="files" class="form-label"><?= Loc::getMessage("CUSTOM_FORM_ITEMS_FILE") ?></label>
                <input class="form-control" type="file" id="files" name="files[]" multiple>
            </div>

            <!-- Комментарий -->
            <div class="mb-3">
                <label for="comment" class="form-label"><?= Loc::getMessage("CUSTOM_FORM_ITEMS_COMMENT") ?></label>
                <textarea class="form-control" id="comment" name="comment" rows="3"><?= htmlspecialcharsbx($arResult['FORM_DATA']['comment'] ?? '') ?></textarea>
            </div>

            <!-- Кнопка отправки -->
            <div class="mb-3">
                <button type="submit" class="btn btn-primary"><?= Loc::getMessage("CUSTOM_FORM_SUBMIT") ?></button>
            </div>
        </form>
    <?php endif; ?>
</div>