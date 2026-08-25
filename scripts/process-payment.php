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
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$PRODUCT_SLUG = isset($PRODUCT_SLUG) ? $PRODUCT_SLUG : '';
$PRODUCT = recetario_get_product($PRODUCT_SLUG);
if (!$PRODUCT) {
    json_out(['success' => false, 'error' => 'Producto no válido.'], 404);
}

$secretKey = recetario_env('STRIPE_SECRET_KEY', '');

/**
 * Llama a la API de Stripe usando cURL.
 * @return array [status_code, decoded_body]
 */
function stripe_request($endpoint, $params, $secretKey) {
    $ch = curl_init('https://api.stripe.com/v1/' . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($params),
        CURLOPT_USERPWD        => $secretKey . ':',
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
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
 */
function handle_intent($intent) {
    $status = isset($intent['status']) ? $intent['status'] : '';
    if ($status === 'succeeded') {
        json_out(['success' => true, 'redirect' => 'success.php']);
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
if (!$secretKey) {
    json_out(['success' => false, 'error' => 'El servidor de pagos no está configurado (falta STRIPE_SECRET_KEY).'], 500);
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    json_out(['success' => false, 'error' => 'Solicitud inválida.'], 400);
}

// ---- Segundo paso: confirmar un PaymentIntent tras autenticación 3DS ----
if (!empty($input['payment_intent_id'])) {
    list($code, $intent) = stripe_request(
        'payment_intents/' . urlencode($input['payment_intent_id']) . '/confirm',
        [],
        $secretKey
    );
    if (isset($intent['error'])) {
        json_out(['success' => false, 'error' => $intent['error']['message']]);
    }
    handle_intent($intent);
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
], $secretKey);

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
], $secretKey);

if (isset($intent['error'])) {
    json_out(['success' => false, 'error' => $intent['error']['message']]);
}

handle_intent($intent);
?>
