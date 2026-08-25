<?php
/**
 * Plantilla compartida de la página de "gracias / descarga".
 * Debe incluirse desde /step/<slug>/index.php tras definir:
 *     $THANKYOU_SLUG = '<slug>';
 *     require ...'/scripts/render-thankyou.php';
 *
 * Los enlaces de descarga se configuran en scripts/products.php
 * (array $THANKYOU_PAGES). Si no hay enlaces, se muestra un mensaje
 * de respaldo sin enlaces falsos.
 */

require_once dirname(__DIR__) . '/scripts/products.php';

$THANKYOU_SLUG = isset($THANKYOU_SLUG) ? $THANKYOU_SLUG : '';
$PAGE = recetario_get_thankyou($THANKYOU_SLUG);
if (!$PAGE) {
    http_response_code(404);
    echo 'No encontrado';
    exit;
}
$downloads = isset($PAGE['downloads']) && is_array($PAGE['downloads']) ? $PAGE['downloads'] : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title><?= htmlspecialchars($PAGE['title']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #cde5f4;
            color: #333;
            padding: 40px 15px;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }
        .card {
            max-width: 620px;
            width: 100%;
            background: #fff;
            border-radius: 8px;
            padding: 46px 34px;
            box-shadow: 0 6px 24px rgba(0,0,0,0.08);
            text-align: center;
        }
        .logo-wrap { margin-bottom: 30px; }
        .logo-wrap img { max-width: 260px; width: 65%; height: auto; }
        .check {
            width: 92px; height: 92px; border-radius: 50%;
            background: #33a652; margin: 0 auto 24px;
            display: flex; align-items: center; justify-content: center;
        }
        .check svg { width: 48px; height: 48px; fill: none; stroke: #fff; stroke-width: 3; }
        h1 { font-size: 30px; color: #222; font-weight: 700; margin-bottom: 14px; }
        p.lead { font-size: 16px; color: #555; margin-bottom: 30px; line-height: 1.6; }
        .downloads { margin: 0 auto 10px; max-width: 420px; }
        .btn-download {
            display: block;
            background: #e06020;
            color: #fff;
            text-decoration: none;
            padding: 15px 24px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 14px;
            transition: background 0.2s;
        }
        .btn-download:hover { background: #cc541a; }
        .note {
            background: #f4f7fa; border: 1px solid #e0e6ec; border-radius: 6px;
            padding: 16px 18px; font-size: 14px; color: #555; line-height: 1.6; margin: 0 auto 20px; max-width: 460px;
        }
        .footer-note { margin-top: 24px; font-size: 13px; color: #999; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo-wrap">
            <img src="../../assets/img/logo.svg" alt="Logotipo <?= htmlspecialchars($PAGE['brand']) ?>">
        </div>
        <div class="check">
            <svg viewBox="0 0 24 24"><polyline points="4 12.5 9.5 18 20 6"></polyline></svg>
        </div>
        <h1><?= htmlspecialchars($PAGE['heading']) ?></h1>
        <p class="lead"><?= htmlspecialchars($PAGE['message']) ?></p>

        <?php if (!empty($downloads)): ?>
            <div class="downloads">
                <?php foreach ($downloads as $dl): ?>
                    <?php if (!empty($dl['url']) && !empty($dl['label'])): ?>
                        <a class="btn-download" href="<?= htmlspecialchars($dl['url']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($dl['label']) ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="note">
                En unos instantes tendrás disponible aquí tu descarga. Si no la ves,
                revisa tu correo o escríbenos y con gusto te la enviamos.
            </div>
        <?php endif; ?>

        <div class="footer-note">Si tienes dudas, escríbenos a ventas@recetarioketo.com</div>
    </div>
</body>
</html>
