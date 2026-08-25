<?php
/**
 * Autenticación mínima para el panel oculto de administración.
 * El único objetivo del panel es exponer un botón de "Actualizar sitio"
 * que dispara deploy.php por AJAX (sin usar URLs públicas con el secreto).
 *
 * Credenciales en el .env del servidor:
 *   ADMIN_USER            -> usuario (por defecto: admin)
 *   ADMIN_PASSWORD_HASH   -> hash generado con password_hash()
 */

require_once __DIR__ . '/env-loader.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function admin_is_authenticated() {
    return !empty($_SESSION['recetario_admin_ok']);
}

/**
 * Verifica usuario/contraseña contra el .env.
 * Acepta ADMIN_PASSWORD_HASH (recomendado) o, como respaldo,
 * ADMIN_PASSWORD en texto plano si no hay hash configurado.
 */
function admin_check_login($user, $pass) {
    $envUser = recetario_env('ADMIN_USER', 'admin');
    $hash    = recetario_env('ADMIN_PASSWORD_HASH', '');
    $plain   = recetario_env('ADMIN_PASSWORD', '');

    if (!hash_equals($envUser, (string) $user)) {
        return false;
    }

    if ($hash !== '') {
        return password_verify((string) $pass, $hash);
    }
    if ($plain !== '') {
        return hash_equals($plain, (string) $pass);
    }
    return false;
}

function admin_login() {
    session_regenerate_id(true);
    $_SESSION['recetario_admin_ok'] = true;
}

function admin_logout() {
    $_SESSION = [];
    session_destroy();
}

function admin_require_auth() {
    if (!admin_is_authenticated()) {
        header('Location: index.php');
        exit;
    }
}
?>
