<?php
/**
 * Raíz del sitio.
 * No mostramos ningún listado ni contenido: solo las páginas /step/<slug>/
 * deben ser visibles. Cualquier acceso a la raíz devuelve la página 404.
 */
http_response_code(404);
require __DIR__ . '/404.php';
exit;
