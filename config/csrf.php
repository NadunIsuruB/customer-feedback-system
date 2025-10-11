<?php
if (!function_exists('csrf_token')) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    function csrf_token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
    }

    function csrf_check(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = isset($_POST['_csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['_csrf']);
            if (!$ok) {
                http_response_code(419);
                exit('CSRF check failed');
            }
        }
    }
}
