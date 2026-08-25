<?php
require_once dirname(__DIR__) . '/scripts/admin-auth.php';
admin_require_auth();

// El secreto de despliegue se lee del .env del servidor y solo se expone
// dentro de esta página, protegida por login. Nunca viaja en una URL pública.
$deploySecret = recetario_env('DEPLOY_SECRET', '');

// ── Información de la versión instalada y últimos cambios ──────────────
// Prioridad: leer data/version.json (escrito por deploy.php) > git directo.
// Esto funciona tanto en servidores con git como en hosting compartido sin git.
$siteRoot = dirname(__DIR__);

/** Ejecuta un comando git dentro de la raíz del sitio y devuelve la salida. */
function admin_git($siteRoot, $args) {
    if (!is_dir($siteRoot . '/.git') || !function_exists('shell_exec')) {
        return null;
    }
    $cmd = 'cd ' . escapeshellarg($siteRoot) . ' && git ' . $args . ' 2>/dev/null';
    $out = @shell_exec($cmd);
    return ($out === null) ? null : trim($out);
}

// Datos de la última versión instalada.
$currentVersion = null;
$currentBranch = null;
$versionFile = $siteRoot . '/data/version.json';

// 1️⃣ Intentar leer data/version.json (escrito por deploy.php en cada actualización).
if (is_file($versionFile)) {
    $versionData = @json_decode(file_get_contents($versionFile), true);
    if ($versionData && is_array($versionData)) {
        $currentVersion = [
            'hash'    => $versionData['commit_hash'] ?? null,
            'date'    => $versionData['commit_date'] ?? $versionData['deployed_at'] ?? null,
            'author'  => $versionData['author'] ?? null,
            'subject' => $versionData['subject'] ?? null,
            'method'  => $versionData['method'] ?? null,
        ];
        $currentBranch = $versionData['branch'] ?? null;
    }
}

// 2️⃣ Si no hay version.json, caer a git directo (servidores con git disponible).
if (!$currentVersion) {
    $rawCurrent = admin_git($siteRoot, "log -1 --date=format:'%Y-%m-%d %H:%M' --format='%h|%cd|%an|%s'");
    if ($rawCurrent) {
        $parts = explode('|', $rawCurrent, 4);
        if (count($parts) === 4) {
            $currentVersion = [
                'hash'    => $parts[0],
                'date'    => $parts[1],
                'author'  => $parts[2],
                'subject' => $parts[3],
                'method'  => 'git',
            ];
        }
    }
    if (!$currentBranch) {
        $currentBranch = admin_git($siteRoot, 'rev-parse --abbrev-ref HEAD');
    }
}

// Log de los últimos cambios (últimos 12 commits).
$changeLog = admin_git($siteRoot, "log -n 12 --date=format:'%Y-%m-%d %H:%M' --format='• %ad  [%h]  %s'");

// Si no hay git disponible (deploy por ZIP), mostramos el final de deploy.log.
$deployLogTail = '';
if ($changeLog === null || $changeLog === '') {
    $logPath = $siteRoot . '/deploy.log';
    if (is_file($logPath)) {
        $lines = @file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines) {
            $deployLogTail = implode("\n", array_slice($lines, -25));
        }
    }
}
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
        .panel + .panel { margin-top: 20px; }
        .version-grid { display: grid; grid-template-columns: 130px 1fr; gap: 10px 14px; font-size: 14px; }
        .version-grid dt { color: #9a9ab0; }
        .version-grid dd { color: #e8e8f2; word-break: break-word; }
        .version-grid dd code {
            background: #0b0b14; border: 1px solid rgba(255,255,255,.14);
            border-radius: 6px; padding: 2px 8px; font-size: 13px; color: #65ff9a;
        }
        .muted { color: #9a9ab0; font-size: 13px; line-height: 1.5; }
        textarea.changelog {
            width: 100%; margin-top: 4px; background: #0b0b14; color: #cfe8d8;
            border: 1px solid rgba(255,255,255,.14); border-radius: 12px;
            padding: 16px; font-size: 13px; line-height: 1.6; resize: vertical;
            min-height: 200px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
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

        <div class="panel">
            <h2>Última versión instalada</h2>
            <?php if ($currentVersion): ?>
                <dl class="version-grid">
                    <?php if (!empty($currentVersion['hash'])): ?>
                    <dt>Versión</dt><dd><code><?= htmlspecialchars($currentVersion['hash']) ?></code></dd>
                    <?php endif; ?>
                    <dt>Fecha</dt><dd><?= htmlspecialchars($currentVersion['date']) ?></dd>
                    <?php if (!empty($currentVersion['author'])): ?>
                    <dt>Autor</dt><dd><?= htmlspecialchars($currentVersion['author']) ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($currentVersion['subject'])): ?>
                    <dt>Cambio</dt><dd><?= htmlspecialchars($currentVersion['subject']) ?></dd>
                    <?php endif; ?>
                    <?php if ($currentBranch): ?>
                    <dt>Rama</dt><dd><?= htmlspecialchars($currentBranch) ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($currentVersion['method'])): ?>
                    <dt>Método</dt><dd><?= htmlspecialchars($currentVersion['method']) ?></dd>
                    <?php endif; ?>
                </dl>
            <?php else: ?>
                <p class="muted">No se pudo determinar la versión instalada. Actualiza el sitio con el botón de arriba para generar la información de versión.</p>
            <?php endif; ?>
        </div>

        <div class="panel">
            <h2>Últimos cambios</h2>
            <p class="desc">Historial de las últimas actualizaciones del sitio.</p>
            <?php if ($changeLog): ?>
                <textarea class="changelog" readonly><?= htmlspecialchars($changeLog) ?></textarea>
            <?php elseif ($deployLogTail): ?>
                <textarea class="changelog" readonly><?= htmlspecialchars($deployLogTail) ?></textarea>
            <?php else: ?>
                <textarea class="changelog" readonly>Aún no hay historial de cambios disponible.</textarea>
            <?php endif; ?>
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
