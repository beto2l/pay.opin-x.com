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

// Protección contra fuerza bruta.
if (!defined('MAX_LOGIN_ATTEMPTS')) {
    define('MAX_LOGIN_ATTEMPTS', 5);
}
if (!defined('LOGIN_LOCKOUT_TIME')) {
    define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos en segundos
}

function admin_is_authenticated() {
    return !empty($_SESSION['recetario_admin_ok']);
}

/**
 * Ruta del archivo donde se guardan los intentos fallidos por IP.
 * Se ubica en /data (protegido por .htaccess y fuera del deploy).
 */
function admin_attempts_file() {
    return dirname(__DIR__) . '/data/login-attempts.json';
}

function admin_client_ip() {
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
}

/**
 * Lee el registro de intentos desde el archivo JSON.
 */
function admin_read_attempts() {
    $file = admin_attempts_file();
    if (!is_readable($file)) {
        return [];
    }
    $raw = file_get_contents($file);
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Guarda el registro de intentos en el archivo JSON.
 */
function admin_write_attempts($data) {
    $file = admin_attempts_file();
    $dir  = dirname($file);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    @file_put_contents($file, json_encode($data), LOCK_EX);
}

/**
 * Devuelve los segundos restantes de bloqueo para la IP actual,
 * o 0 si no está bloqueada.
 */
function admin_lockout_remaining() {
    $ip = admin_client_ip();
    $data = admin_read_attempts();
    if (empty($data[$ip])) {
        return 0;
    }
    $rec = $data[$ip];
    $count = isset($rec['count']) ? (int) $rec['count'] : 0;
    $last  = isset($rec['last'])  ? (int) $rec['last']  : 0;
    if ($count < MAX_LOGIN_ATTEMPTS) {
        return 0;
    }
    $elapsed = time() - $last;
    if ($elapsed >= LOGIN_LOCKOUT_TIME) {
        return 0; // el bloqueo ya expiró
    }
    return LOGIN_LOCKOUT_TIME - $elapsed;
}

/**
 * ¿La IP actual está bloqueada?
 */
function admin_is_locked_out() {
    return admin_lockout_remaining() > 0;
}

/**
 * Registra un intento fallido para la IP actual.
 * Devuelve el número de intentos acumulados.
 */
function admin_record_failed_attempt() {
    $ip = admin_client_ip();
    $data = admin_read_attempts();
    $now = time();

    if (empty($data[$ip])) {
        $data[$ip] = ['count' => 0, 'last' => $now];
    }
    // Si el bloqueo previo ya expiró, reiniciamos el conteo.
    $last = isset($data[$ip]['last']) ? (int) $data[$ip]['last'] : 0;
    if (($data[$ip]['count'] ?? 0) >= MAX_LOGIN_ATTEMPTS && ($now - $last) >= LOGIN_LOCKOUT_TIME) {
        $data[$ip] = ['count' => 0, 'last' => $now];
    }

    $data[$ip]['count'] = (int) ($data[$ip]['count'] ?? 0) + 1;
    $data[$ip]['last']  = $now;

    admin_write_attempts($data);
    return $data[$ip]['count'];
}

/**
 * Intentos restantes antes del bloqueo para la IP actual.
 */
function admin_remaining_attempts() {
    $ip = admin_client_ip();
    $data = admin_read_attempts();
    $count = isset($data[$ip]['count']) ? (int) $data[$ip]['count'] : 0;
    $left = MAX_LOGIN_ATTEMPTS - $count;
    return $left > 0 ? $left : 0;
}

/**
 * Limpia los intentos fallidos de la IP actual (tras login exitoso).
 */
function admin_clear_attempts() {
    $ip = admin_client_ip();
    $data = admin_read_attempts();
    if (isset($data[$ip])) {
        unset($data[$ip]);
        admin_write_attempts($data);
    }
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
