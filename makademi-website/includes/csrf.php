<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function csrf_token(): string
{
    start_admin_session();
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
    start_admin_session();
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $sent)) {
        http_response_code(400);
        echo 'Invalid or missing CSRF token. Please go back and try again.';
        exit;
    }
}
