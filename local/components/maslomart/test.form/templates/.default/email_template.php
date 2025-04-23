<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= $emailData['SUBJECT'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #0066cc; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background-color: #f5f5f5; text-align: left; padding: 8px; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; min-width: 150px; display: inline-block; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #eee; font-size: 0.9em; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Новая заявка</h1>
        
        <p><span class="label">Заголовок:</span> <?= htmlspecialcharsbx($emailData['TITLE']) ?></p>
        <p><span class="label">Категория:</span> <?= htmlspecialcharsbx($emailData['CATEGORY']) ?></p>
        <p><span class="label">Вид заявки:</span> <?= htmlspecialcharsbx($emailData['REQUEST_TYPE']) ?></p>
        <p><span class="label">Склад поставки:</span> <?= htmlspecialcharsbx($emailData['WAREHOUSE']) ?></p>
        
        <h2>Состав заявки</h2>
        <table>
            <thead>
                <tr>
                    <th>Бренд</th>
                    <th>Наименование</th>
                    <th>Количество</th>
                    <th>Фасовка</th>
                    <th>Клиент</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emailData['ITEMS'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialcharsbx($arParams['BRANDS'][$item['brand']] ?? $item['brand']) ?></td>
                        <td><?= htmlspecialcharsbx($item['name']) ?></td>
                        <td><?= htmlspecialcharsbx($item['quantity']) ?></td>
                        <td><?= htmlspecialcharsbx($item['packing']) ?></td>
                        <td><?= htmlspecialcharsbx($item['client']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (!empty($emailData['COMMENT'])): ?>
            <h2>Комментарий</h2>
            <p><?= nl2br(htmlspecialcharsbx($emailData['COMMENT'])) ?></p>
        <?php endif; ?>
        
        <?php if (!empty($emailData['FILES'])): ?>
            <h2>Прикрепленные файлы</h2>
            <ul>
                <?php foreach ($emailData['FILES']['name'] as $fileName): ?>
                    <li><?= htmlspecialcharsbx($fileName) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <div class="footer">
            <p>Письмо отправлено с сайта <?= $emailData['SITE_NAME'] ?> (<?= $emailData['SERVER_NAME'] ?>)</p>
            <p>Дата отправки: <?= date('d.m.Y H:i:s') ?></p>
        </div>
    </div>
</body>
</html>