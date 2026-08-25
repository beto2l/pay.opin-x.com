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
 * Cada una vive en /step/<slug>/ y replica el diseño de las páginas
 * originales de WordPress: intro, botones de descarga con portada,
 * grupo de Facebook y pasos finales.
 *
 * Los archivos y las portadas se sirven desde el subdominio del usuario
 * (keto.opin-x.com), igual que en las páginas originales.
 */
$FB_GROUP_URL   = 'https://www.facebook.com/groups/recetarioketodigital';
$FB_FOLLOW_URL  = 'https://www.facebook.com/recetarioketodigital/';
$INTRO_TEXT     = 'Por favor leer detenidamente hasta el final, en la parte de abajo se encuentran los enlaces para descargar tu compra. Estos enlaces también llegarán a tu correo electrónico, junto con tus regalos digitales (a veces llega a bandeja de SPAM).';
$CONTACT_TEXT   = '¿Tienes dudas? Escríbenos a ventas@recetarioketo.com';
$DOWNLOAD_NOTE  = 'El recetario puede tardar varios segundos en descargarse, dale clic solo una vez, te recomendamos descargarlo desde una red de wifi para mayor velocidad.';

$THANKYOU_PAGES = [

    'recetario-keto-thank-you' => [
        'slug'                => 'recetario-keto-thank-you',
        'title'               => 'Gracias por tu compra - Recetario Keto',
        'brand'               => 'Recetario Keto',
        'follow_url'          => $FB_FOLLOW_URL,
        'contact'             => $CONTACT_TEXT,
        'intro'               => $INTRO_TEXT,
        'contact_after_intro' => false,
        'instructions_heading'=> '¡Instrucciones para descargar tu Recetario Keto!',
        'instructions_note'   => $DOWNLOAD_NOTE,
        'downloads' => [
            ['image' => 'https://keto.opin-x.com/portada-recetario-keto.jpg', 'label' => 'Descargar Recetario Keto', 'url' => 'https://keto.opin-x.com/LB1-Keto-220.pdf'],
            ['image' => 'https://keto.opin-x.com/portada-menu.jpg',           'label' => 'Descargar Menu semanal',     'url' => 'https://keto.opin-x.com/Menu-semana-keto.pdf'],
            ['image' => 'https://keto.opin-x.com/portada-toma-peso.jpg',      'label' => 'Descargar Tomar peso',       'url' => 'https://keto.opin-x.com/COMO-TOMAR-TU-PESO-Y-MEDIDAS.pdf'],
        ],
        'contact_after_downloads' => true,
        'sections' => [
            ['type' => 'heading_green', 'text' => '¡Accede a nuestro Grupo Privado de Facebook!'],
            ['type' => 'paragraph',     'text' => '1. Ingresa al enlace del grupo en Facebook y dale clic al botón “Unirte al grupo” (como en la imagen de abajo) 👇🏻'],
            ['type' => 'image_link',    'image' => 'https://keto.opin-x.com/grupo-privado.png', 'url' => $FB_GROUP_URL, 'alt' => 'Grupo privado de Facebook'],
            ['type' => 'link_line',     'text' => 'Grupo en Facebook: Recetas keto', 'url' => $FB_GROUP_URL],
            ['type' => 'image',         'image' => 'https://keto.opin-x.com/grupo-privado-fb.png', 'alt' => 'Cómo unirte al grupo'],
            ['type' => 'paragraph',     'text' => 'Recibirás un correo con los enlaces para acceder a tus descargas del Recetario Keto y también obtendrás el acceso a tus regalos digitales.'],
        ],
    ],

    'gracias-por-tu-compra-postres' => [
        'slug'                => 'gracias-por-tu-compra-postres',
        'title'               => 'Gracias por tu compra - Postres y Snacks Keto',
        'brand'               => 'Recetario Keto',
        'follow_url'          => $FB_FOLLOW_URL,
        'contact'             => $CONTACT_TEXT,
        'intro'               => $INTRO_TEXT,
        'contact_after_intro' => true,
        'instructions_heading'=> '¡Instrucciones para descargar tu Recetario Keto!',
        'instructions_note'   => $DOWNLOAD_NOTE,
        'downloads' => [
            ['image' => 'https://keto.opin-x.com/portada-postres-snacks-keto.jpg', 'label' => 'Descargar Postres y Snacks', 'url' => 'https://keto.opin-x.com/Postres_keto_mx_B1.pdf'],
        ],
        'contact_after_downloads' => false,
        'sections' => [
            ['type' => 'heading_green', 'text' => '¡Instrucciones para obtener tus regalos!'],
            ['type' => 'paragraph',     'text' => '1. Ingresa al enlace del grupo en Facebook y dale clic al botón “Unirte al grupo” (como en la imagen de abajo) 👇🏻'],
            ['type' => 'image_link',    'image' => 'https://keto.opin-x.com/grupo-privado.png', 'url' => $FB_GROUP_URL, 'alt' => 'Grupo privado de Facebook'],
            ['type' => 'link_line',     'text' => 'Grupo en Facebook: Recetas keto', 'url' => $FB_GROUP_URL],
            ['type' => 'image',         'image' => 'https://keto.opin-x.com/grupo-privado-fb.png', 'alt' => 'Cómo unirte al grupo'],
            ['type' => 'paragraph',     'text' => '2. Una vez que la solicitud sea aprobada, por favor publica lo siguiente en el grupo:'],
            ['type' => 'quote',         'text' => '“Ya compré el nuevo Recetario Keto, por favor dame acceso a mis regalos digitales”'],
            ['type' => 'heading',       'text' => 'Un miembro del equipo te va a contactar al ver la publicación y te enviará el acceso a tus regalos digitales 🎁'],
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
