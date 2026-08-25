<?php
/**
 * Plantilla compartida de la página de "gracias / descarga".
 * Debe incluirse desde /step/<slug>/index.php tras definir:
 *     $THANKYOU_SLUG = '<slug>';
 *     require ...'/scripts/render-thankyou.php';
 *
 * El contenido (enlaces, portadas, textos y secciones) se define en
 * scripts/products.php (array $THANKYOU_PAGES). Replica el diseño de las
 * páginas originales de WordPress.
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
$sections  = isset($PAGE['sections'])  && is_array($PAGE['sections'])  ? $PAGE['sections']  : [];
$contact   = isset($PAGE['contact']) ? $PAGE['contact'] : '¿Tienes dudas? Escríbenos a ventas@recetarioketo.com';
$followUrl = isset($PAGE['follow_url']) ? $PAGE['follow_url'] : 'https://www.facebook.com/recetarioketodigital/';

/** Renderiza una sección de contenido (contenido de confianza definido en products.php). */
function ty_render_section($s) {
    $type = isset($s['type']) ? $s['type'] : '';
    switch ($type) {
        case 'heading_green':
            echo '<h2 class="ty-green">' . htmlspecialchars($s['text']) . '</h2>';
            break;
        case 'heading':
            echo '<h2 class="ty-h2">' . htmlspecialchars($s['text']) . '</h2>';
            break;
        case 'paragraph':
            echo '<p class="ty-p">' . htmlspecialchars($s['text']) . '</p>';
            break;
        case 'quote':
            echo '<p class="ty-quote">' . htmlspecialchars($s['text']) . '</p>';
            break;
        case 'link_line':
            echo '<p class="ty-p"><a href="' . htmlspecialchars($s['url']) . '" target="_blank" rel="noopener">' . htmlspecialchars($s['text']) . '</a></p>';
            break;
        case 'image':
            echo '<div class="ty-img"><img src="' . htmlspecialchars($s['image']) . '" alt="' . htmlspecialchars(isset($s['alt']) ? $s['alt'] : '') . '" loading="lazy"></div>';
            break;
        case 'image_link':
            echo '<div class="ty-img"><a href="' . htmlspecialchars($s['url']) . '" target="_blank" rel="noopener"><img src="' . htmlspecialchars($s['image']) . '" alt="' . htmlspecialchars(isset($s['alt']) ? $s['alt'] : '') . '" loading="lazy"></a></div>';
            break;
    }
}
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
            background: #fff;
            color: #4a4a4a;
            padding: 40px 15px 60px;
            line-height: 1.6;
        }
        .ty-wrap { max-width: 780px; margin: 0 auto; text-align: center; }
        .check {
            width: 62px; height: 62px; border-radius: 50%;
            background: #2b2b3a; margin: 0 auto 22px;
            display: flex; align-items: center; justify-content: center;
        }
        .check svg { width: 30px; height: 30px; fill: none; stroke: #fff; stroke-width: 3; }
        h1.ty-title { font-size: 34px; color: #33475b; font-weight: 700; margin-bottom: 16px; }
        .ty-intro { font-size: 17px; color: #5a6b7b; max-width: 620px; margin: 0 auto 18px; }
        .ty-contact { font-size: 13px; color: #9aa7b1; margin: 6px auto 10px; }
        .ty-green {
            color: #2ecc71; font-size: 26px; font-weight: 700;
            margin: 40px auto 18px; max-width: 680px;
        }
        .ty-h2 { color: #33475b; font-size: 22px; font-weight: 700; margin: 26px auto 14px; max-width: 640px; }
        .ty-note { font-size: 15px; color: #5a6b7b; max-width: 640px; margin: 0 auto 8px; }
        .ty-sub { font-weight: 700; color: #33475b; margin-bottom: 26px; display: block; }
        .ty-p { font-size: 16px; color: #5a6b7b; max-width: 640px; margin: 0 auto 18px; }
        .ty-p a { color: #2b6cb0; font-weight: 600; }
        .ty-quote {
            font-size: 17px; color: #33475b; font-style: italic; font-weight: 500;
            max-width: 600px; margin: 0 auto 22px; background: #f4f7fa;
            border-left: 4px solid #2ecc71; padding: 14px 20px; text-align: left;
        }
        /* Tarjetas de descarga */
        .downloads {
            display: flex; flex-wrap: wrap; justify-content: center;
            gap: 34px; margin: 10px auto 20px;
        }
        .dl-card { width: 250px; display: flex; flex-direction: column; align-items: center; }
        .dl-card img {
            width: 100%; height: auto; border-radius: 6px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.12); margin-bottom: 16px;
        }
        .btn-download {
            display: inline-block; background: #2b2b3a; color: #fff;
            text-decoration: none; padding: 13px 26px; border-radius: 40px;
            font-size: 15px; font-weight: 600; transition: background 0.2s;
        }
        .btn-download:hover { background: #e06020; }
        .ty-img { margin: 6px auto 22px; }
        .ty-img img { max-width: 100%; height: auto; border-radius: 6px; }
        /* Pasos finales */
        .steps {
            display: flex; flex-wrap: wrap; justify-content: center; gap: 24px;
            margin: 40px auto 10px; max-width: 720px;
        }
        .step { flex: 1 1 200px; max-width: 220px; }
        .step .num {
            width: 44px; height: 44px; border-radius: 50%; background: #2ecc71; color: #fff;
            font-size: 20px; font-weight: 700; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 12px;
        }
        .step h4 { color: #33475b; font-size: 17px; margin-bottom: 6px; }
        .step p { color: #5a6b7b; font-size: 14px; }
        .social { margin-top: 46px; }
        .social h2 { color: #33475b; font-size: 22px; margin-bottom: 16px; }
        .btn-social {
            display: inline-block; background: #1877f2; color: #fff; text-decoration: none;
            padding: 12px 28px; border-radius: 40px; font-size: 15px; font-weight: 600;
        }
        .btn-social:hover { background: #145dbf; }
        @media (max-width: 600px) {
            h1.ty-title { font-size: 27px; }
            .ty-green { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="ty-wrap">
        <div class="check">
            <svg viewBox="0 0 24 24"><polyline points="4 12.5 9.5 18 20 6"></polyline></svg>
        </div>
        <h1 class="ty-title">Gracias por tu compra</h1>
        <p class="ty-intro"><?= htmlspecialchars($PAGE['intro']) ?></p>

        <?php if (!empty($PAGE['contact_after_intro'])): ?>
            <p class="ty-contact"><?= htmlspecialchars($contact) ?></p>
        <?php endif; ?>

        <?php if (!empty($PAGE['instructions_heading'])): ?>
            <h2 class="ty-green"><?= htmlspecialchars($PAGE['instructions_heading']) ?></h2>
        <?php endif; ?>
        <?php if (!empty($PAGE['instructions_note'])): ?>
            <p class="ty-note"><?= htmlspecialchars($PAGE['instructions_note']) ?></p>
        <?php endif; ?>
        <span class="ty-sub">Aquí tienes los enlaces de descarga:</span>

        <?php if (!empty($downloads)): ?>
            <div class="downloads">
                <?php foreach ($downloads as $dl): ?>
                    <?php if (!empty($dl['url']) && !empty($dl['label'])): ?>
                        <div class="dl-card">
                            <?php if (!empty($dl['image'])): ?>
                                <img src="<?= htmlspecialchars($dl['image']) ?>" alt="<?= htmlspecialchars($dl['label']) ?>" loading="lazy">
                            <?php endif; ?>
                            <a class="btn-download" href="<?= htmlspecialchars($dl['url']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($dl['label']) ?> &rsaquo;</a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($PAGE['contact_after_downloads'])): ?>
            <p class="ty-contact"><?= htmlspecialchars($contact) ?></p>
        <?php endif; ?>

        <?php foreach ($sections as $s) { ty_render_section($s); } ?>

        <!-- Pasos finales (comunes) -->
        <div class="steps">
            <div class="step">
                <div class="num">1</div>
                <h4>Revisa tu correo</h4>
                <p>En tu bandeja de entrada recibirás el comprobante de tu compra.</p>
            </div>
            <div class="step">
                <div class="num">2</div>
                <h4>Haz clic en el enlace</h4>
                <p>Para poder acceder al recetario digital keto.</p>
            </div>
            <div class="step">
                <div class="num">3</div>
                <h4>Disfrútalo</h4>
                <p>Comienza a preparar las recetas que más te gusten.</p>
            </div>
        </div>

        <div class="social">
            <h2>¡Síguenos en redes sociales!</h2>
            <a class="btn-social" href="<?= htmlspecialchars($followUrl) ?>" target="_blank" rel="noopener">Síguenos en Facebook</a>
        </div>
    </div>
</body>
</html>
