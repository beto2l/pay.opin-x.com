<?php
require_once dirname(__DIR__, 2) . '/scripts/env-loader.php';
$stripe_pk = recetario_env('STRIPE_PUBLISHABLE_KEY', '');
// Parámetros de campaña (para métricas / metadata en el pago)
$k1 = isset($_GET['k1']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['k1']) : '';
$k2 = isset($_GET['k2']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['k2']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="stripe-pk" content="<?= htmlspecialchars($stripe_pk) ?>">
    <title>Checkout Recetario Keto en Español</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #cde5f4;
            color: #333333;
            line-height: 1.5;
            padding: 30px 15px;
        }
        .checkout-card {
            max-width: 780px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
        }

        /* ===== Encabezado ===== */
        .logo-wrap { text-align: center; margin-bottom: 24px; }
        .logo-wrap img { max-width: 300px; width: 70%; height: auto; }

        .offer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }
        .offer-text { flex: 1; min-width: 240px; }
        .offer-text .offer-label { font-size: 26px; color: #444; font-weight: 400; margin-bottom: 14px; }
        .offer-text .offer-price { font-size: 34px; font-weight: 700; color: #33a652; margin-bottom: 14px; }
        .offer-text .offer-desc { font-size: 14px; color: #777; max-width: 340px; }
        .offer-badge { flex-shrink: 0; }
        .offer-badge img { width: 210px; height: auto; }

        /* ===== Formulario 2 columnas ===== */
        .checkout-grid {
            display: flex;
            gap: 30px;
            align-items: flex-start;
            flex-wrap: wrap;
        }
        .col-left { flex: 1 1 55%; min-width: 300px; }
        .col-right { flex: 1 1 38%; min-width: 260px; }

        h3.section-title {
            font-size: 20px;
            font-weight: 500;
            color: #111;
            margin: 22px 0 14px;
        }
        h3.section-title:first-child { margin-top: 0; }

        .form-field { margin-bottom: 12px; }
        .form-row-2 { display: flex; gap: 12px; }
        .form-row-2 .form-field { flex: 1; }

        input.txt {
            width: 100%;
            height: 44px;
            padding: 8px 12px;
            border: 1px solid #d4d4d4;
            border-radius: 4px;
            font-size: 15px;
            font-family: 'Roboto', sans-serif;
            color: #333;
            background: #fff;
        }
        input.txt::placeholder { color: #9ca3af; }
        input.txt:focus { outline: none; border-color: #33a652; }

        /* ===== Payment box ===== */
        .payment-box {
            border: 1px solid #d4d4d4;
            border-radius: 6px;
            margin-bottom: 16px;
            overflow: hidden;
        }
        .payment-option-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px;
        }
        .payment-option-head input[type=radio] { width: 16px; height: 16px; accent-color: #e06020; }
        .payment-option-head label { font-size: 15px; color: #333; font-weight: 400; cursor: pointer; }
        .card-logos { display: flex; gap: 6px; margin-left: auto; align-items: center; }
        .card-logos img { height: 22px; width: auto; }
        .payment-body {
            background: #f9f9f9;
            padding: 16px;
            border-top: 1px solid #e5e7eb;
        }
        .payment-body .pay-note { font-size: 13px; color: #666; margin-bottom: 12px; }
        #card-element {
            background: #fff;
            padding: 14px 12px;
            border: 1px solid #d4d4d4;
            border-radius: 4px;
        }
        #card-errors { color: #d13030; font-size: 13px; margin-top: 8px; min-height: 16px; }

        .privacy-note { font-size: 13px; color: #666; margin: 16px 0; }
        .privacy-note a { color: #e06020; text-decoration: none; }

        .terms-row { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 18px; }
        .terms-row input { width: 16px; height: 16px; margin-top: 2px; accent-color: #e06020; }
        .terms-row label { font-size: 14px; color: #333; }
        .terms-row a { color: #e06020; text-decoration: none; }
        .terms-row .req { color: #d13030; }

        /* ===== Botón ===== */
        .btn-order {
            width: 100%;
            background: #e06020;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 18px;
            font-size: 18px;
            font-weight: 500;
            font-family: 'Roboto', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s;
        }
        .btn-order:hover { background: #cc541a; }
        .btn-order:disabled { opacity: 0.7; cursor: not-allowed; }
        #form-error { color: #d13030; font-size: 14px; margin-top: 12px; text-align: center; display: none; }

        /* ===== Your order ===== */
        .order-box {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 0;
            overflow: hidden;
        }
        .order-head {
            display: flex;
            justify-content: space-between;
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 500;
            font-size: 14px;
            color: #111;
        }
        .order-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        .order-item img { width: 46px; height: 46px; border-radius: 4px; object-fit: cover; }
        .order-item .oi-name { flex: 1; font-size: 14px; color: #333; }
        .order-item .oi-qty { font-size: 14px; color: #666; }
        .order-item .oi-price { font-size: 14px; color: #333; min-width: 60px; text-align: right; }
        .order-sub {
            display: flex;
            justify-content: space-between;
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            color: #333;
        }
        .order-total {
            display: flex;
            justify-content: space-between;
            padding: 16px;
            font-size: 18px;
            font-weight: 700;
            color: #111;
        }

        /* ===== Trust ===== */
        .trust-section { max-width: 560px; margin: 40px auto 0; }
        .trust-item { margin-bottom: 26px; }
        .trust-item h4 { font-size: 19px; font-weight: 500; color: #222; margin-bottom: 6px; }
        .trust-item p { font-size: 14px; color: #666; }

        /* ===== Testimonios ===== */
        .testi-title { text-align: center; font-size: 26px; font-weight: 500; color: #222; margin: 44px 0 34px; }
        .testi-grid { display: flex; gap: 30px; flex-wrap: wrap; justify-content: center; }
        .testi { flex: 1 1 40%; min-width: 260px; text-align: center; }
        .testi .stars { margin-bottom: 14px; }
        .testi .stars img { height: 22px; }
        .testi .quote { font-style: italic; font-size: 15px; color: #444; margin-bottom: 20px; }
        .testi .avatar { width: 78px; height: 78px; border-radius: 50%; object-fit: cover; margin: 0 auto 8px; display: block; }
        .testi .name { font-size: 14px; color: #333; }

        /* ===== Pagos seguros ===== */
        .secure-title { text-align: center; font-size: 26px; color: #444; margin: 50px 0 30px; font-family: 'Open Sans', sans-serif; }
        .secure-logos {
            display: flex; flex-wrap: wrap; justify-content: center; align-items: center;
            gap: 26px 40px;
        }
        .secure-logos img { height: 42px; width: auto; opacity: 0.95; }

        /* ===== Footer ===== */
        .site-footer { text-align: center; margin-top: 30px; }
        .footer-brand { font-size: 22px; color: #888; margin-bottom: 18px; font-family: 'Open Sans', sans-serif; }
        .social-row { display: flex; justify-content: center; gap: 10px; margin-bottom: 22px; }
        .social-row a {
            width: 34px; height: 34px; border-radius: 4px; display: flex;
            align-items: center; justify-content: center; color: #fff;
        }
        .social-row .fb { background: #3b5998; }
        .social-row .ig { background: #ea2c59; }
        .social-row .tk { background: #010101; }
        .social-row .yt { background: #ff0000; }
        .social-row svg { width: 18px; height: 18px; fill: #fff; }
        .footer-contact-label { font-size: 15px; color: #888; margin-bottom: 4px; }
        .footer-email { font-size: 14px; color: #999; margin-bottom: 18px; }
        .footer-terms { display: block; font-size: 14px; color: #4169e1; text-decoration: none; margin-bottom: 16px; }
        .footer-copy { font-size: 13px; color: #aaa; }

        @media (max-width: 600px) {
            .checkout-card { padding: 20px; }
            .offer-text .offer-label { font-size: 22px; }
            .offer-text .offer-price { font-size: 28px; }
            .offer-badge img { width: 150px; }
        }
    </style>
</head>
<body>
    <div class="checkout-card">
        <!-- Logo -->
        <div class="logo-wrap">
            <img src="../../assets/img/logo.svg" alt="Logotipo Recetario Keto">
        </div>

        <!-- Encabezado de oferta -->
        <div class="offer-header">
            <div class="offer-text">
                <div class="offer-label">HOY, todo por solo</div>
                <div class="offer-price">$25 usd</div>
                <div class="offer-desc">El Recetario Keto es un producto digital, al completar tu compra, recibirás en tu correo los enlaces para poder descargarlo.</div>
            </div>
            <div class="offer-badge">
                <img src="../../assets/img/garantia.webp" alt="Garantía de reembolso 100%">
            </div>
        </div>

        <!-- Formulario -->
        <form id="checkout-form" novalidate>
            <div class="checkout-grid">
                <!-- Columna izquierda -->
                <div class="col-left">
                    <h3 class="section-title">Customer information</h3>
                    <div class="form-field">
                        <input class="txt" type="email" id="email" name="email" placeholder="Email Address *" required>
                    </div>

                    <h3 class="section-title">Billing details</h3>
                    <div class="form-row-2">
                        <div class="form-field">
                            <input class="txt" type="text" id="first_name" name="first_name" placeholder="First name *" required>
                        </div>
                        <div class="form-field">
                            <input class="txt" type="text" id="last_name" name="last_name" placeholder="Last name">
                        </div>
                    </div>
                    <div class="form-field">
                        <input class="txt" type="tel" id="phone" name="phone" placeholder="Phone">
                    </div>

                    <h3 class="section-title">Payment</h3>
                    <div class="payment-box">
                        <div class="payment-option-head">
                            <input type="radio" id="pm-stripe" name="payment_method" checked>
                            <label for="pm-stripe">Credit Card (Stripe)</label>
                            <span class="card-logos">
                                <img src="../../assets/img/visa.svg" alt="Visa">
                                <img src="../../assets/img/mastercard.svg" alt="Mastercard">
                                <img src="../../assets/img/amex.svg" alt="Amex">
                                <img src="../../assets/img/discover.svg" alt="Discover">
                            </span>
                        </div>
                        <div class="payment-body">
                            <div class="pay-note">Pay with your credit card via Stripe</div>
                            <div id="card-element"><!-- Stripe Card Element --></div>
                            <div id="card-errors" role="alert"></div>
                        </div>
                    </div>

                    <p class="privacy-note">Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our <a href="#">privacy policy</a>.</p>

                    <div class="terms-row">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I have read and agree to the website <a href="#">terms and conditions</a> <span class="req">*</span></label>
                    </div>

                    <button type="submit" class="btn-order" id="place-order">
                        <span class="lock">🔒</span>
                        <span class="btn-label">Realizar pedido</span>
                    </button>
                    <div id="form-error"></div>
                </div>

                <!-- Columna derecha -->
                <div class="col-right">
                    <h3 class="section-title">Your order</h3>
                    <div class="order-box">
                        <div class="order-head">
                            <span>Product</span>
                            <span>Subtotal</span>
                        </div>
                        <div class="order-item">
                            <img src="../../assets/img/recetario-producto.jpg" alt="Recetario Keto">
                            <span class="oi-name">Recetario Keto</span>
                            <span class="oi-qty">&times; 1</span>
                            <span class="oi-price">$25.00</span>
                        </div>
                        <div class="order-sub">
                            <span>Subtotal</span>
                            <span>$25.00</span>
                        </div>
                        <div class="order-total">
                            <span>Total</span>
                            <span>$25.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Trust -->
        <div class="trust-section">
            <div class="trust-item">
                <h4>Pago Seguro</h4>
                <p>Cada pedido se procesa a través de una puerta de enlace de procesamiento de pagos cifrada segura de 256 bits para garantizar su privacidad.</p>
            </div>
            <div class="trust-item">
                <h4>Garantía de Satisfacción</h4>
                <p>Si no está 100% satisfecho con su producto, solo contáctenos y haremos la devolución de cada centavo invertido.</p>
            </div>
            <div class="trust-item">
                <h4>Acceso Inmediato</h4>
                <p>El acceso al producto se libera después de la aprobación del pago con tarjeta y PayPal.</p>
            </div>
        </div>

        <!-- Testimonios -->
        <div class="testi-title">¡Únete a más de 10,000 clientes!</div>
        <div class="testi-grid">
            <div class="testi">
                <div class="stars"><img src="../../assets/img/5-estrellas.webp" alt="5 estrellas"></div>
                <div class="quote">Me dio confianza por que vi que muchas personas estaban tendiendo resultados y eso esta padre!</div>
                <img class="avatar" src="../../assets/img/u3-g.jpg" alt="Mariana Sánchez">
                <div class="name">Mariana Sánchez</div>
            </div>
            <div class="testi">
                <div class="stars"><img src="../../assets/img/5-estrellas.webp" alt="5 estrellas"></div>
                <div class="quote">¡Cuando hice mi compra me gusto que me contactaron para entregarme mi programa y bueno ya llevo 4 kilos Muchas Gracias!</div>
                <img class="avatar" src="../../assets/img/u3-g.jpg" alt="Julia Barrios">
                <div class="name">Julia Barrios</div>
            </div>
        </div>

        <!-- Pagos seguros -->
        <div class="secure-title">Pagos seguros vía</div>
        <div class="secure-logos">
            <img src="../../assets/img/ssl.webp" alt="SSL Secure">
            <img src="../../assets/img/pp.webp" alt="PayPal">
            <img src="../../assets/img/jcb.webp" alt="JCB">
            <img src="../../assets/img/mt.webp" alt="Maestro">
            <img src="../../assets/img/https.webp" alt="HTTPS">
            <img src="../../assets/img/mf.webp" alt="McAfee">
            <img src="../../assets/img/ns.webp" alt="Norton">
            <img src="../../assets/img/am.webp" alt="American Express">
            <img src="../../assets/img/vs.webp" alt="Visa">
            <img src="../../assets/img/mc.webp" alt="Mastercard">
        </div>
    </div>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-brand">Recetario Keto Digital &reg;</div>
        <div class="social-row">
            <a class="fb" href="#" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.99 3.66 9.13 8.44 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99C18.34 21.13 22 16.99 22 12z"/></svg></a>
            <a class="ig" href="#" aria-label="Instagram"><svg viewBox="0 0 24 24"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.16-.42-.36-1.06-.41-2.23-.06-1.27-.07-1.65-.07-4.85s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41 1.27-.06 1.65-.07 4.85-.07M12 0C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63c-.79.31-1.46.72-2.12 1.38C1.36 2.67.95 3.34.63 4.14.33 4.9.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.15.56 2.91.31.79.72 1.46 1.38 2.12.66.66 1.33 1.07 2.12 1.38.76.3 1.64.5 2.91.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.27-.06 2.15-.26 2.91-.56.79-.31 1.46-.72 2.12-1.38.66-.66 1.07-1.33 1.38-2.12.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.15-.56-2.91-.31-.79-.72-1.46-1.38-2.12C21.33 1.36 20.66.95 19.86.63 19.1.33 18.22.13 16.95.07 15.67.01 15.26 0 12 0zm0 5.84A6.16 6.16 0 1 0 18.16 12 6.16 6.16 0 0 0 12 5.84zM12 16a4 4 0 1 1 4-4 4 4 0 0 1-4 4zm6.41-10.85a1.44 1.44 0 1 0 1.44 1.44 1.44 1.44 0 0 0-1.44-1.44z"/></svg></a>
            <a class="tk" href="#" aria-label="TikTok"><svg viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/></svg></a>
            <a class="yt" href="#" aria-label="YouTube"><svg viewBox="0 0 24 24"><path d="M23.5 6.19a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.5A3.02 3.02 0 0 0 .5 6.19 31.6 31.6 0 0 0 0 12a31.6 31.6 0 0 0 .5 5.81 3.02 3.02 0 0 0 2.12 2.14c1.88.5 9.38.5 9.38.5s7.5 0 9.38-.5a3.02 3.02 0 0 0 2.12-2.14A31.6 31.6 0 0 0 24 12a31.6 31.6 0 0 0-.5-5.81zM9.6 15.6V8.4l6.2 3.6z"/></svg></a>
        </div>
        <div class="footer-contact-label">Correo de contacto</div>
        <div class="footer-email">ventas@recetarioketo.com</div>
        <a class="footer-terms" href="#">Términos de Uso &amp; Política de Privacidad</a>
        <div class="footer-copy">&copy; 2026 Recetario Keto &ndash; Todos los derechos reservados.</div>
    </footer>

    <script>
        (function () {
            var pk = document.querySelector('meta[name="stripe-pk"]').getAttribute('content');
            var form = document.getElementById('checkout-form');
            var formError = document.getElementById('form-error');
            var cardErrors = document.getElementById('card-errors');
            var btn = document.getElementById('place-order');
            var btnLabel = btn.querySelector('.btn-label');
            var K1 = <?= json_encode($k1) ?>;
            var K2 = <?= json_encode($k2) ?>;

            function showFormError(msg) {
                formError.textContent = msg;
                formError.style.display = 'block';
            }
            function clearFormError() {
                formError.textContent = '';
                formError.style.display = 'none';
            }
            function setLoading(loading) {
                btn.disabled = loading;
                btnLabel.textContent = loading ? 'Procesando…' : 'Realizar pedido';
            }

            if (!pk) {
                showFormError('La configuración de pago no está disponible. Falta la clave pública de Stripe.');
                return;
            }

            var stripe = Stripe(pk);
            var elements = stripe.elements();
            var style = {
                base: {
                    fontSize: '15px',
                    color: '#333333',
                    fontFamily: 'Roboto, sans-serif',
                    '::placeholder': { color: '#9ca3af' }
                },
                invalid: { color: '#d13030' }
            };
            var card = elements.create('card', { style: style, hidePostalCode: true });
            card.mount('#card-element');

            card.on('change', function (event) {
                cardErrors.textContent = event.error ? event.error.message : '';
            });

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                clearFormError();

                var email = document.getElementById('email').value.trim();
                var firstName = document.getElementById('first_name').value.trim();
                var lastName = document.getElementById('last_name').value.trim();
                var phone = document.getElementById('phone').value.trim();
                var terms = document.getElementById('terms').checked;

                if (!email) { showFormError('Por favor ingresa tu correo electrónico.'); return; }
                if (!firstName) { showFormError('Por favor ingresa tu nombre.'); return; }
                if (!terms) { showFormError('Debes aceptar los términos y condiciones para continuar.'); return; }

                setLoading(true);

                stripe.createPaymentMethod({
                    type: 'card',
                    card: card,
                    billing_details: {
                        name: (firstName + ' ' + lastName).trim(),
                        email: email,
                        phone: phone
                    }
                }).then(function (result) {
                    if (result.error) {
                        setLoading(false);
                        cardErrors.textContent = result.error.message;
                        return;
                    }
                    sendToServer(result.paymentMethod.id, email, firstName, lastName, phone);
                });
            });

            function sendToServer(paymentMethodId, email, firstName, lastName, phone) {
                fetch('checkout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: email,
                        first_name: firstName,
                        last_name: lastName,
                        phone: phone,
                        payment_method_id: paymentMethodId,
                        k1: K1,
                        k2: K2
                    })
                })
                .then(function (r) { return r.json(); })
                .then(function (data) { handleServerResponse(data); })
                .catch(function () {
                    setLoading(false);
                    showFormError('Ocurrió un error de conexión. Inténtalo de nuevo.');
                });
            }

            function handleServerResponse(data) {
                if (data.error) {
                    setLoading(false);
                    showFormError(data.error);
                    return;
                }
                if (data.requires_action && data.client_secret) {
                    stripe.handleNextAction({ clientSecret: data.client_secret })
                        .then(function (result) {
                            if (result.error) {
                                setLoading(false);
                                showFormError(result.error.message);
                            } else {
                                confirmOnServer(result.paymentIntent.id);
                            }
                        });
                    return;
                }
                if (data.success) {
                    window.location.href = data.redirect || 'success.php';
                    return;
                }
                setLoading(false);
                showFormError('No se pudo procesar el pago. Inténtalo de nuevo.');
            }

            function confirmOnServer(paymentIntentId) {
                fetch('checkout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ payment_intent_id: paymentIntentId })
                })
                .then(function (r) { return r.json(); })
                .then(function (data) { handleServerResponse(data); })
                .catch(function () {
                    setLoading(false);
                    showFormError('Ocurrió un error de conexión. Inténtalo de nuevo.');
                });
            }
        })();
    </script>
</body>
</html>
