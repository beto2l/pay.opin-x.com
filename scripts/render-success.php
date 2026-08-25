<?php
/**
 * Plantilla compartida de la página de éxito.
 * Debe incluirse desde /step/<slug>/success.php tras definir:
 *     $PRODUCT_SLUG = '<slug>';
 *     require ...'/scripts/render-success.php';
 */

require_once dirname(__DIR__) . '/scripts/products.php';

$PRODUCT_SLUG = isset($PRODUCT_SLUG) ? $PRODUCT_SLUG : '';
$PRODUCT = recetario_get_product($PRODUCT_SLUG);
if (!$PRODUCT) {
    http_response_code(404);
    echo 'No encontrado';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Pago exitoso! - <?= htmlspecialchars($PRODUCT['name']) ?></title>
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
        .btn-home {
            display: inline-block;
            background: #e06020;
            color: #fff;
            text-decoration: none;
            padding: 14px 34px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-home:hover { background: #cc541a; }
        .footer-note { margin-top: 30px; font-size: 13px; color: #999; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo-wrap">
            <img src="../../assets/img/logo.svg" alt="Logotipo <?= htmlspecialchars($PRODUCT['brand']) ?>">
        </div>
        <div class="check">
            <svg viewBox="0 0 24 24"><polyline points="4 12.5 9.5 18 20 6"></polyline></svg>
        </div>
        <h1>¡Pago exitoso!</h1>
        <p class="lead"><?= htmlspecialchars($PRODUCT['success_desc']) ?></p>
        <a class="btn-home" href="index.php">Volver al inicio</a>
        <div class="footer-note">Si tienes dudas, escríbenos a ventas@recetarioketo.com</div>
    </div>
</body>
</html>
