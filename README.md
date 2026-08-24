# 🥑 Recetario Keto — Checkout

Página de checkout (réplica) para la venta del producto digital **Recetario Keto** ($25 USD), con procesamiento de pagos vía **Stripe** y sistema de auto-deploy desde GitHub.

## 📁 Estructura del Proyecto

```
pay.opin-x.com/
├── deploy.php                      # Script de auto-actualización desde GitHub
├── .htaccess                       # Configuración Apache (HTTPS, seguridad, DirectoryIndex)
├── .env.example                    # Plantilla de variables sensibles (copiar a .env)
├── .gitignore                      # Excluye .env y logs del repositorio
├── scripts/
│   └── env-loader.php              # Carga variables desde .env sin Composer
├── step/
│   └── recetario-keto/
│       ├── index.php               # Página de checkout con Stripe.js
│       ├── checkout.php            # Backend que procesa el pago (Stripe API vía cURL)
│       └── success.php             # Página de confirmación de pago
└── assets/
    └── img/                        # Imágenes (logo, garantía, producto, logos de pago…)
```

## 🔐 Seguridad de credenciales

Todas las claves sensibles (Stripe Publishable/Secret Key, secreto de deploy, etc.) viven
**solo** en el archivo `.env` del servidor. Este archivo:

- **NO** se sube a GitHub (está en `.gitignore`).
- **NO** se sobrescribe ni se borra al actualizar el sitio (`deploy.php` lo excluye).
- Está bloqueado ante acceso web directo (`.htaccess`).

## 💳 Flujo de pago

1. El cliente llena el formulario e ingresa su tarjeta en el **Stripe Card Element**.
2. `index.php` crea un `PaymentMethod` con Stripe.js y lo envía a `checkout.php`.
3. `checkout.php` crea el Customer y un PaymentIntent (`$25.00 USD`) y lo confirma.
4. Si requiere autenticación 3D Secure, el frontend ejecuta `handleNextAction`.
5. Al aprobarse, se redirige a `success.php`.

## 🚀 Auto-Deploy desde GitHub

```
https://tu-dominio.com/deploy.php?secret=TU_DEPLOY_SECRET
```

Ver `INSTRUCCIONES-DEPLOY.txt` para la configuración completa.

## 📞 Contacto

- **Email:** ventas@recetarioketo.com

---

© 2026 Recetario Keto. Todos los derechos reservados.
