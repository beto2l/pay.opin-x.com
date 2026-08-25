<?php
/**
 * Raíz del sitio.
 * No mostramos ningún listado ni contenido: cualquier acceso a la raíz
 * (o a una URL desconocida) redirige a la primera página de checkout.
 */
require_once __DIR__ . '/scripts/env-loader.php';
$base = rtrim(recetario_env('SITE_URL', 'https://pay.opin-x.com'), '/');
header('Location: ' . $base . '/step/recetario-keto/', true, 302);
exit;
