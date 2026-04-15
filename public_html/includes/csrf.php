<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_input')) {
    function csrf_input(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('csrf_validate')) {
    function csrf_validate(): bool
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $formToken = $_POST['csrf_token'] ?? '';

        if ($sessionToken === '' || $formToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $formToken);
    }
}

if (!function_exists('csrf_require')) {
    function csrf_require(): void
    {
        if (!csrf_validate()) {
            http_response_code(403);
            die('Ошибка безопасности: недействительный CSRF-токен.');
        }
    }
}