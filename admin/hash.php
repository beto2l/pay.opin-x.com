<?php
/**
 * Generador de hash para la contraseña del panel.
 * Escribe una contraseña, genera el hash con password_hash() (bcrypt)
 * y cópialo en el .env del servidor como:
 *   ADMIN_PASSWORD_HASH=...
 *
 * Esta página no revela ningún secreto: solo transforma el texto que
 * escribas en su hash. Vive dentro de /admin para no aparecer al público.
 */

$hash = '';
$plain = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plain = isset($_POST['pass']) ? (string) $_POST['pass'] : '';
    if ($plain !== '') {
        $hash = password_hash($plain, PASSWORD_DEFAULT);
    }
}
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Generar hash de contraseña</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #0b0b14; color: #e8e8f2;
            min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .card {
            width: 100%; max-width: 520px; background: #15151f;
            border: 1px solid rgba(255,255,255,.12); border-radius: 14px; padding: 34px 28px;
        }
        h1 { font-size: 20px; font-weight: 600; margin-bottom: 8px; text-align: center; }
        p.sub { font-size: 13px; color: #9a9ab0; text-align: center; margin-bottom: 22px; line-height: 1.5; }
        label { display: block; font-size: 13px; color: #9a9ab0; margin: 14px 0 6px; }
        input, textarea {
            width: 100%; padding: 10px 12px; border-radius: 8px;
            border: 1px solid rgba(255,255,255,.16); background: #0b0b14; color: #fff; font-size: 15px;
            font-family: inherit;
        }
        input { height: 44px; }
        input:focus, textarea:focus { outline: none; border-color: #e06020; }
        textarea { resize: vertical; min-height: 70px; font-family: monospace; font-size: 13px; }
        button {
            width: 100%; margin-top: 22px; height: 46px; border: none; border-radius: 8px;
            background: #e06020; color: #fff; font-size: 15px; font-weight: 600; cursor: pointer;
        }
        button:hover { background: #cc541a; }
        button.copy { margin-top: 10px; background: #2b2b3a; }
        button.copy:hover { background: #3a3a4d; }
        .result { margin-top: 22px; }
        .hint {
            margin-top: 14px; font-size: 12px; color: #9a9ab0; line-height: 1.6;
            background: #0b0b14; border: 1px solid rgba(255,255,255,.1); border-radius: 8px; padding: 12px 14px;
        }
        code { color: #e0a060; }
        .ok { color: #6fdc8c; font-size: 13px; text-align: center; margin-top: 10px; display: none; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Generar hash de contraseña</h1>
        <p class="sub">Escribe la contraseña que quieres usar para el panel.<br>Se generará el hash para pegarlo en el archivo <code>.env</code>.</p>

        <form method="post" autocomplete="off">
            <label for="pass">Contraseña</label>
            <input type="text" id="pass" name="pass" value="<?= htmlspecialchars($plain) ?>" required autofocus>
            <button type="submit">Generar hash</button>
        </form>

        <?php if ($hash !== ''): ?>
        <div class="result">
            <label for="hashout">Tu hash (cópialo completo)</label>
            <textarea id="hashout" readonly onclick="this.select()"><?= htmlspecialchars($hash) ?></textarea>
            <button type="button" class="copy" onclick="copyHash()">Copiar hash</button>
            <div class="ok" id="okmsg">¡Copiado!</div>
            <div class="hint">
                Pega esta línea en el archivo <code>.env</code> del servidor:<br><br>
                <code>ADMIN_PASSWORD_HASH=<?= htmlspecialchars($hash) ?></code><br><br>
                No uses comillas ni espacios extra. Guarda el archivo y prueba entrar
                con esa contraseña en el panel.
            </div>
        </div>
        <script>
        function copyHash() {
            var t = document.getElementById('hashout');
            t.select();
            t.setSelectionRange(0, 99999);
            try {
                navigator.clipboard.writeText(t.value);
            } catch (e) {
                document.execCommand('copy');
            }
            document.getElementById('okmsg').style.display = 'block';
        }
        </script>
        <?php endif; ?>
    </div>
</body>
</html>
