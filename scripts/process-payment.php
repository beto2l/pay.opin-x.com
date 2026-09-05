<?php
/**
 * Procesamiento de pago compartido con Stripe (sin Composer, vía cURL).
 * Debe incluirse desde /step/<slug>/checkout.php tras definir:
 *     $PRODUCT_SLUG = '<slug>';
 *     require ...'/scripts/process-payment.php';
 * El monto y la descripción se toman del catálogo de productos.
 */

require_once dirname(__DIR__) . '/scripts/products.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * Responde JSON y termina.
 */
function json_out($data, $code = 200) {
    if (function_exists('status_header')) {
        status_header($code);
    } else {
        http_response_code($code);
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$PRODUCT_SLUG = isset($PRODUCT_SLUG) ? $PRODUCT_SLUG : '';
$PRODUCT = recetario_get_product($PRODUCT_SLUG);
if (!$PRODUCT) {
    json_out(['success' => false, 'error' => 'Producto no válido.'], 404);
}

// URL de la página de "gracias" tras el pago (por producto).
$THANK_YOU_URL = isset($PRODUCT['thank_you_url']) && $PRODUCT['thank_you_url'] !== ''
    ? $PRODUCT['thank_you_url']
    : 'success.php';

$secretKey = recetario_env('STRIPE_SECRET_KEY', '');

/**
 * Llama a la API de Stripe usando cURL.
 * @return array [status_code, decoded_body]
 */
function stripe_request($endpoint, $params, $secretKey, $idempotencyKey = '') {
    $ch = curl_init('https://api.stripe.com/v1/' . $endpoint);
    $headers = ['Content-Type: application/x-www-form-urlencoded'];
    if ($idempotencyKey !== '') {
        $headers[] = 'Idempotency-Key: ' . $idempotencyKey;
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($params),
        CURLOPT_USERPWD        => $secretKey . ':',
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 45,
    ]);
    $response = curl_exec($ch);
    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return [0, ['error' => ['message' => 'Error de conexión con Stripe: ' . $err]]];
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$httpCode, json_decode($response, true)];
}

/**
 * Interpreta un PaymentIntent y devuelve la respuesta apropiada al frontend.
 * $redirect: URL a la que enviar al cliente cuando el pago es exitoso.
 */
function handle_intent($intent, $redirect = 'success.php') {
    $status = isset($intent['status']) ? $intent['status'] : '';
    if ($status === 'succeeded') {
        json_out(['success' => true, 'redirect' => $redirect]);
    }
    if ($status === 'requires_action' || $status === 'requires_source_action') {
        json_out([
            'requires_action' => true,
            'client_secret'   => $intent['client_secret'],
        ]);
    }
    json_out(['success' => false, 'error' => 'El pago no pudo completarse (estado: ' . $status . ').']);
}

// ---- Validaciones iniciales ----
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_out(['success' => false, 'error' => 'Método no permitido.'], 405);
}

if (!$secretKey) {
    json_out(['success' => false, 'error' => 'El servidor de pagos no está configurado (falta STRIPE_SECRET_KEY).'], 500);
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    json_out(['success' => false, 'error' => 'Solicitud inválida.'], 400);
}

if (function_exists('wp_verify_nonce')) {
    $checkoutToken = isset($input['checkout_token']) ? (string) $input['checkout_token'] : '';
    if (!$checkoutToken || !wp_verify_nonce($checkoutToken, 'recetario_checkout_' . $PRODUCT_SLUG)) {
        json_out(['success' => false, 'error' => 'La sesión de pago expiró. Actualiza la página e inténtalo nuevamente.'], 403);
    }
}

$idempotencyKey = isset($input['idempotency_key']) ? trim((string) $input['idempotency_key']) : '';
if (!preg_match('/^[A-Za-z0-9._:-]{12,120}$/', $idempotencyKey)) {
    json_out(['success' => false, 'error' => 'No se pudo identificar de forma segura este intento de pago.'], 400);
}

if (function_exists('get_transient') && function_exists('set_transient')) {
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $salt = function_exists('wp_salt') ? wp_salt('nonce') : __FILE__;
    $rateKey = 'lw_pay_rate_' . substr(hash_hmac('sha256', $remote, $salt), 0, 32);
    $attempts = (int) get_transient($rateKey);
    if ($attempts >= 12) {
        json_out(['success' => false, 'error' => 'Demasiados intentos. Espera unos minutos e inténtalo nuevamente.'], 429);
    }
    set_transient($rateKey, $attempts + 1, 10 * MINUTE_IN_SECONDS);
}

// ---- Segundo paso: confirmar un PaymentIntent tras autenticación 3DS ----
if (!empty($input['payment_intent_id'])) {
    list($code, $intent) = stripe_request(
        'payment_intents/' . urlencode($input['payment_intent_id']) . '/confirm',
        [],
        $secretKey,
        $idempotencyKey . '-confirm'
    );
    if (isset($intent['error'])) {
        json_out(['success' => false, 'error' => $intent['error']['message']]);
    }
    handle_intent($intent, $THANK_YOU_URL);
}

// ---- Primer paso: crear customer + PaymentIntent ----
$email      = isset($input['email']) ? trim($input['email']) : '';
$firstName  = isset($input['first_name']) ? trim($input['first_name']) : '';
$lastName   = isset($input['last_name']) ? trim($input['last_name']) : '';
$phone      = isset($input['phone']) ? trim($input['phone']) : '';
$paymentMethodId = isset($input['payment_method_id']) ? trim($input['payment_method_id']) : '';
$k1 = isset($input['k1']) ? trim($input['k1']) : '';
$k2 = isset($input['k2']) ? trim($input['k2']) : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_out(['success' => false, 'error' => 'Por favor ingresa un correo electrónico válido.'], 400);
}
if ($firstName === '') {
    json_out(['success' => false, 'error' => 'Por favor ingresa tu nombre.'], 400);
}
if ($paymentMethodId === '') {
    json_out(['success' => false, 'error' => 'No se recibió la información de la tarjeta.'], 400);
}

// 1) Crear Customer
list($custCode, $customer) = stripe_request('customers', [
    'email' => $email,
    'name'  => trim($firstName . ' ' . $lastName),
    'phone' => $phone,
], $secretKey, $idempotencyKey . '-customer');

if (isset($customer['error'])) {
    json_out(['success' => false, 'error' => $customer['error']['message']]);
}
$customerId = isset($customer['id']) ? $customer['id'] : null;

// 2) Crear y confirmar PaymentIntent (monto y descripción según el producto)
list($piCode, $intent) = stripe_request('payment_intents', [
    'amount'                => (int) $PRODUCT['price_cents'],
    'currency'              => 'usd',
    'customer'              => $customerId,
    'payment_method'        => $paymentMethodId,
    'confirm'               => 'true',
    'confirmation_method'   => 'automatic',
    'description'           => $PRODUCT['stripe_description'],
    'receipt_email'         => $email,
    'metadata[product]'     => $PRODUCT['slug'],
    'metadata[first_name]'  => $firstName,
    'metadata[last_name]'   => $lastName,
    'metadata[phone]'       => $phone,
    'metadata[k1]'          => $k1,
    'metadata[k2]'          => $k2,
], $secretKey, $idempotencyKey . '-payment-intent');

if (isset($intent['error'])) {
    json_out(['success' => false, 'error' => $intent['error']['message']]);
}

handle_intent($intent, $THANK_YOU_URL);
?>
