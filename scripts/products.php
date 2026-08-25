<?php
/**
 * Catálogo de productos (una sola fuente de verdad).
 * Cada "step" (carpeta bajo /step/<slug>/) define $PRODUCT_SLUG y luego
 * incluye los scripts compartidos de render/pago que leen de aquí.
 */

require_once __DIR__ . '/env-loader.php';

$PRODUCTS = [

    'recetario-keto' => [
        'slug'                => 'recetario-keto',
        'title'               => 'Checkout Recetario Keto en Español',
        'name'                => 'Recetario Keto',
        'brand'               => 'Recetario Keto',
        'price_display'       => '$25 usd',
        'price_cents'         => 2500,
        'price_text'          => '$25.00',
        'image'               => 'recetario-producto.jpg',
        'stripe_description'  => 'Recetario Keto - Producto digital',
        'offer_desc'          => 'El Recetario Keto es un producto digital, al completar tu compra, recibirás en tu correo los enlaces para poder descargarlo.',
        'success_desc'        => 'Gracias por tu compra. En breve recibirás un correo con el enlace de descarga de tu Recetario Keto.',
        // Página de "gracias" a la que se redirige tras el pago exitoso.
        'thank_you_url'       => '/step/recetario-keto-thank-you/',
    ],

    'compra-nochebuena-keto' => [
        'slug'                => 'compra-nochebuena-keto',
        'title'               => 'Comprar el Recetario Noche Buena Keto - OPIN X LLC',
        'name'                => 'Noche Buena Keto',
        'brand'               => 'Recetario Keto',
        'price_display'       => '$19 usd',
        'price_cents'         => 1900,
        'price_text'          => '$19.00',
        'image'               => 'nochebuena-producto.jpg',
        'stripe_description'  => 'Recetario Noche Buena Keto - Producto digital',
        'offer_desc'          => 'El Recetario de Nochebuena Keto es un producto digital, al completar tu compra, recibirás en tu correo los enlaces para poder descargarlo; los enlaces de descarga son vitalicios.',
        'success_desc'        => 'Gracias por tu compra. En breve recibirás un correo con el enlace de descarga de tu Recetario de Nochebuena Keto.',
        // Noche Buena no tiene página propia: usa la de recetario-keto.
        'thank_you_url'       => '/step/recetario-keto-thank-you/',
    ],

    'postres-y-snacks-keto' => [
        'slug'                => 'postres-y-snacks-keto',
        'title'               => 'Comprar el Recetario de Postres y Snacks Keto - OPIN X LLC',
        'name'                => 'Postres Keto',
        'brand'               => 'Recetario Keto',
        'price_display'       => '$19 usd',
        'price_cents'         => 1900,
        'price_text'          => '$19.00',
        'image'               => 'postres-producto.jpg',
        'stripe_description'  => 'Recetario Postres y Snacks Keto - Producto digital',
        'offer_desc'          => 'El Recetario de Postres y Snacks Keto es un producto digital, al completar tu compra, recibirás en tu correo los enlaces para poder descargarlo, los enlaces de descarga son vitalicios.',
        'success_desc'        => 'Gracias por tu compra. En breve recibirás un correo con el enlace de descarga de tu Recetario de Postres y Snacks Keto.',
        'thank_you_url'       => '/step/gracias-por-tu-compra-postres/',
    ],

];

/**
 * Páginas de "gracias / descarga" (thank you) tras el pago.
 * Cada una vive en /step/<slug>/ y muestra los enlaces de descarga.
 *
 * En 'downloads' agrega cada archivo así:
 *   ['label' => 'Descargar Recetario Keto (PDF)', 'url' => 'https://...'],
 * Si 'downloads' está vacío, la página muestra un mensaje de respaldo
 * (sin enlaces falsos) invitando a revisar el correo.
 */
$THANKYOU_PAGES = [

    'recetario-keto-thank-you' => [
        'slug'      => 'recetario-keto-thank-you',
        'title'     => '¡Gracias por tu compra! - Recetario Keto',
        'brand'     => 'Recetario Keto',
        'heading'   => '¡Gracias por tu compra!',
        'message'   => 'Tu pago se realizó con éxito. Aquí puedes descargar tu compra. También recibirás el recibo de tu pago por correo de parte de Stripe.',
        'downloads' => [
            // ['label' => 'Descargar Recetario Keto (PDF)', 'url' => 'https://...'],
        ],
    ],

    'gracias-por-tu-compra-postres' => [
        'slug'      => 'gracias-por-tu-compra-postres',
        'title'     => '¡Gracias por tu compra! - Postres y Snacks Keto',
        'brand'     => 'Recetario Keto',
        'heading'   => '¡Gracias por tu compra!',
        'message'   => 'Tu pago se realizó con éxito. Aquí puedes descargar tu compra. También recibirás el recibo de tu pago por correo de parte de Stripe.',
        'downloads' => [
            // ['label' => 'Descargar Postres y Snacks Keto (PDF)', 'url' => 'https://...'],
        ],
    ],

];

/**
 * Devuelve la configuración del producto activo o null si no existe.
 */
function recetario_get_product($slug) {
    global $PRODUCTS;
    return isset($PRODUCTS[$slug]) ? $PRODUCTS[$slug] : null;
}

/**
 * Devuelve la configuración de una página de "gracias" o null si no existe.
 */
function recetario_get_thankyou($slug) {
    global $THANKYOU_PAGES;
    return isset($THANKYOU_PAGES[$slug]) ? $THANKYOU_PAGES[$slug] : null;
}
?>
