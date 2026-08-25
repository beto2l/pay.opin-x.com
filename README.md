# 🥑 OPIN X — Checkouts (Recetarios Keto)

Páginas de checkout (réplicas estáticas) para vender productos digitales con
procesamiento de pagos vía **Stripe**, sistema de **auto-deploy** desde GitHub y
un **panel de administración oculto** con botón de actualización por AJAX.

## 🧾 Páginas / productos

| URL | Producto | Precio |
|-----|----------|--------|
| `/step/recetario-keto/` | Recetario Keto | $25 USD |
| `/step/compra-nochebuena-keto/` | Noche Buena Keto | $19 USD |
| `/step/postres-y-snacks-keto/` | Postres y Snacks Keto | $19 USD |

Solo estas páginas (`/step/<slug>/`) son visibles públicamente. **Cualquier otra
URL —incluida la raíz `/`— devuelve una página 404 neutra** y no se muestra ningún
listado de archivos.

## 📁 Estructura del Proyecto

```
pay.opin-x.com/
├── index.php                       # Raíz: devuelve 404 (no expone nada)
├── 404.php                         # Página de error neutra (403/404)
├── deploy.php                      # Auto-actualización desde GitHub
├── .htaccess                       # Apache: HTTPS, -Indexes, bloqueos, headers
├── .env.example                    # Plantilla de variables sensibles
├── scripts/
│   ├── env-loader.php              # Carga variables desde .env (sin Composer)
│   ├── products.php                # Catálogo de productos (fuente única)
│   ├── render-checkout.php         # Plantilla compartida del checkout
│   ├── process-payment.php         # Backend de pago compartido (Stripe/cURL)
│   ├── render-success.php          # Plantilla compartida de "pago exitoso"
│   └── admin-auth.php              # Login/sesión del panel oculto
├── admin/                          # Panel oculto (noindex)
│   ├── index.php                   # Login
│   ├── dashboard.php               # Botón "Actualizar sitio" (AJAX a deploy.php)
│   └── logout.php
├── step/
│   ├── recetario-keto/             # Cada carpeta = wrappers finos
│   ├── compra-nochebuena-keto/     #   index.php / checkout.php / success.php
│   └── postres-y-snacks-keto/      #   (definen $PRODUCT_SLUG e incluyen scripts/)
└── assets/img/                     # Imágenes (logo, garantía, productos, pagos…)
```

Cada carpeta de `step/` contiene solo 3 archivos muy cortos que fijan el `slug` del
producto e incluyen las plantillas compartidas de `scripts/`. Así hay **una sola
fuente de verdad**: para cambiar precios/textos se edita `scripts/products.php`.

## 🔐 Seguridad de credenciales

Todas las claves sensibles (Stripe, secreto de deploy, contraseña del admin) viven
**solo** en el `.env` del servidor. Este archivo:

- **NO** se sube a GitHub (está en `.gitignore`).
- **NO** se sobrescribe ni se borra al actualizar el sitio (`deploy.php` lo excluye).
- Está bloqueado ante acceso web directo (`.htaccess`).

## 💳 Flujo de pago

1. El cliente llena el formulario e ingresa su tarjeta en el **Stripe Card Element**.
2. El checkout crea un `PaymentMethod` con Stripe.js y lo envía a `checkout.php`.
3. `checkout.php` crea el Customer y un PaymentIntent con el **monto del producto** y lo confirma.
4. Si requiere autenticación 3D Secure, el frontend ejecuta `handleNextAction`.
5. Al aprobarse, se redirige a `success.php`.

## 🚀 Actualizar el sitio (2 formas)

- **Panel oculto (recomendado):** entrar a `/admin/`, iniciar sesión y pulsar
  **“Actualizar sitio ahora”**. El botón llama a `deploy.php` por AJAX enviando el
  secreto que se lee del `.env` en el servidor (nunca aparece en una URL pública).
- **URL directa (respaldo):** `https://tu-dominio.com/deploy.php?secret=TU_DEPLOY_SECRET`

Ver `INSTRUCCIONES-DEPLOY.txt` para la configuración completa (incluye cómo crear
el usuario/contraseña del panel).

## 📞 Contacto

- **Email:** ventas@recetarioketo.com

---

© 2026 OPIN X LLC. Todos los derechos reservados.
