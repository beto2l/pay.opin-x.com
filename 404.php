<?php
/**
 * Cualquier página no encontrada (404) o prohibida (403) redirige
 * a la primera página de checkout, en lugar de mostrar un error.
 * El panel /admin/, las páginas /step/<slug>/ y /assets/ se sirven normalmente
 * (quedan excluidos en el .htaccess).
 */
require_once __DIR__ . '/scripts/env-loader.php';
$base = rtrim(recetario_env('SITE_URL', 'https://pay.opin-x.com'), '/');
header('Location: ' . $base . '/step/recetario-keto/', true, 302);
exit;
