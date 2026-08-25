<?php
require_once dirname(__DIR__) . '/scripts/admin-auth.php';
admin_require_auth();

// El secreto de despliegue se lee del .env del servidor y solo se expone
// dentro de esta página, protegida por login. Nunca viaja en una URL pública.
$deploySecret = recetario_env('DEPLOY_SECRET', '');
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #0b0b14; color: #e8e8f2; min-height: 100vh; padding: 40px 20px;
        }
        .wrap { max-width: 640px; margin: 0 auto; }
        .top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; }
        .top h1 { font-size: 20px; font-weight: 600; }
        .logout { color: #9a9ab0; font-size: 13px; text-decoration: none; }
        .logout:hover { color: #fff; }
        .panel {
            background: #15151f; border: 1px solid rgba(255,255,255,.12);
            border-radius: 14px; padding: 30px 26px;
        }
        .panel h2 { font-size: 17px; font-weight: 600; margin-bottom: 8px; }
        .panel p.desc { font-size: 14px; color: #9a9ab0; margin-bottom: 22px; line-height: 1.5; }
        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            background: #e06020; color: #fff; border: none; border-radius: 10px;
            padding: 14px 26px; font-size: 15px; font-weight: 600; cursor: pointer;
        }
        .btn-primary:hover { background: #cc541a; }
        .btn-primary:disabled { opacity: .65; cursor: not-allowed; }
        .warn {
            background: #2a1e14; border: 1px solid #7a4a1a; color: #ffb27a;
            border-radius: 10px; padding: 14px 16px; font-size: 13px; margin-bottom: 20px;
        }
        pre {
            display: none; white-space: pre-wrap; margin-top: 18px; background: #0b0b14;
            border: 1px solid rgba(255,255,255,.14); border-radius: 12px; padding: 16px;
            max-height: 340px; overflow: auto; color: #e8e8f2; font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="top">
            <h1>Panel de administración</h1>
            <a class="logout" href="logout.php">Cerrar sesión</a>
        </div>
        <div class="panel">
            <h2>Actualizar sitio</h2>
            <p class="desc">Descarga la última versión del código desde GitHub y actualiza el sitio en el servidor. El proceso puede tardar unos segundos.</p>
            <?php if ($deploySecret === ''): ?>
                <div class="warn">No se encontró <code>DEPLOY_SECRET</code> en el archivo <code>.env</code> del servidor. Configúralo para poder actualizar desde aquí.</div>
            <?php endif; ?>
            <button type="button" id="deployBtn" class="btn-primary" <?= $deploySecret === '' ? 'disabled' : '' ?>>
                <span>🔄 Actualizar sitio ahora</span>
            </button>
            <pre id="deployResult"></pre>
        </div>
    </div>

    <script>
        (function () {
            const deployBtn = document.getElementById('deployBtn');
            if (!deployBtn) return;
            const deployResult = document.getElementById('deployResult');
            const DEPLOY_SECRET = <?= json_encode($deploySecret) ?>;

            deployBtn.addEventListener('click', async () => {
                const originalHtml = deployBtn.innerHTML;
                deployBtn.disabled = true;
                deployBtn.innerHTML = '<span>⏳ Actualizando...</span>';
                deployResult.style.display = 'block';
                deployResult.style.color = '#e8e8f2';
                deployResult.textContent = 'Conectando con el servidor...';

                try {
                    const response = await fetch('../deploy.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json'
                        },
                        body: new URLSearchParams({ secret: DEPLOY_SECRET })
                    });
                    const text = await response.text();
                    let json = null;
                    try { json = JSON.parse(text); } catch (e) {}

                    if (json && json.success) {
                        deployResult.style.color = '#65ff9a';
                        deployResult.textContent = '✅ ' + (json.message || 'Sitio actualizado correctamente') +
                            '\n\n' + JSON.stringify(json, null, 2);
                    } else {
                        deployResult.style.color = '#ff7777';
                        deployResult.textContent = '❌ No se pudo actualizar.\n\n' +
                            (json ? JSON.stringify(json, null, 2) : text);
                    }
                } catch (error) {
                    deployResult.style.color = '#ff7777';
                    deployResult.textContent = '❌ Error de conexión: ' + error.message;
                } finally {
                    deployBtn.disabled = false;
                    deployBtn.innerHTML = originalHtml;
                }
            });
        })();
    </script>
</body>
</html>
