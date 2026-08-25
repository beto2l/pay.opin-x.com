<?php
require_once dirname(__DIR__) . '/scripts/admin-auth.php';

// Si ya está autenticado, ir directo al panel.
if (admin_is_authenticated()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$locked = admin_is_locked_out();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($locked) {
        $mins = (int) ceil(admin_lockout_remaining() / 60);
        $error = 'Demasiados intentos fallidos. Vuelve a intentarlo en ' . $mins . ' minuto(s).';
    } else {
        $user = isset($_POST['user']) ? trim($_POST['user']) : '';
        $pass = isset($_POST['pass']) ? (string) $_POST['pass'] : '';
        if (admin_check_login($user, $pass)) {
            admin_clear_attempts();
            admin_login();
            header('Location: dashboard.php');
            exit;
        }
        // Registrar el intento fallido y avisar de los intentos restantes.
        admin_record_failed_attempt();
        $locked = admin_is_locked_out();
        if ($locked) {
            $mins = (int) ceil(admin_lockout_remaining() / 60);
            $error = 'Demasiados intentos fallidos. Cuenta bloqueada por ' . $mins . ' minuto(s).';
        } else {
            $left = admin_remaining_attempts();
            $error = 'Usuario o contraseña incorrectos. Te quedan ' . $left . ' intento(s).';
        }
        // Pequeña espera para dificultar fuerza bruta.
        sleep(1);
    }
} elseif ($locked) {
    $mins = (int) ceil(admin_lockout_remaining() / 60);
    $error = 'Demasiados intentos fallidos. Vuelve a intentarlo en ' . $mins . ' minuto(s).';
}
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Acceso</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #0b0b14; color: #e8e8f2;
            min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .login {
            width: 100%; max-width: 360px; background: #15151f;
            border: 1px solid rgba(255,255,255,.12); border-radius: 14px; padding: 34px 28px;
        }
        h1 { font-size: 20px; font-weight: 600; margin-bottom: 22px; text-align: center; }
        label { display: block; font-size: 13px; color: #9a9ab0; margin: 14px 0 6px; }
        input {
            width: 100%; height: 44px; padding: 10px 12px; border-radius: 8px;
            border: 1px solid rgba(255,255,255,.16); background: #0b0b14; color: #fff; font-size: 15px;
        }
        input:focus { outline: none; border-color: #e06020; }
        button {
            width: 100%; margin-top: 22px; height: 46px; border: none; border-radius: 8px;
            background: #e06020; color: #fff; font-size: 15px; font-weight: 600; cursor: pointer;
        }
        button:hover { background: #cc541a; }
        .err { margin-top: 16px; color: #ff7777; font-size: 13px; text-align: center; }
    </style>
</head>
<body>
    <form class="login" method="post" autocomplete="off">
        <h1>Panel de administración</h1>
        <label for="user">Usuario</label>
        <input type="text" id="user" name="user" required autofocus>
        <label for="pass">Contraseña</label>
        <input type="password" id="pass" name="pass" required>
        <button type="submit">Entrar</button>
        <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    </form>
</body>
</html>
