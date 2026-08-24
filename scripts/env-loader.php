<?php
/**
 * Recetario Keto - Simple .env loader
 * Carga variables sensibles desde un archivo .env sin depender de Composer.
 */

if (!function_exists('recetario_load_env')) {
    function recetario_load_env($path = null) {
        $path = $path ?: dirname(__DIR__) . '/.env';
        if (!is_readable($path)) {
            return false;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $name = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            $value = trim($value, "\"'");

            if ($name !== '') {
                $_ENV[$name] = $value;
                putenv($name . '=' . $value);
            }
        }

        return true;
    }
}

if (!function_exists('recetario_env')) {
    function recetario_env($key, $default = null) {
        $value = getenv($key);
        if ($value === false && isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }
        return ($value === false || $value === null || $value === '') ? $default : $value;
    }
}

recetario_load_env();
?>
