<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function start_admin_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('makademi_admin');
    session_start();
}

function current_admin(): ?array
{
    start_admin_session();
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    static $cached = null;
    if ($cached !== null && $cached['id'] === $_SESSION['admin_id']) {
        return $cached;
    }
    $stmt = db()->prepare('SELECT id, username, last_login_at FROM admin_users WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $row = $stmt->fetch();
    $cached = $row ?: null;
    return $cached;
}

function require_admin(): array
{
    $admin = current_admin();
    if (!$admin) {
        $next = $_SERVER['REQUEST_URI'] ?? '';
        header('Location: login.php?next=' . urlencode($next));
        exit;
    }
    return $admin;
}

function admin_login(string $username, string $password): bool
{
    start_admin_session();
    $stmt = db()->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = ?');
    $stmt->execute([$username]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($password, $row['password_hash'])) {
        // Constant-ish delay to slow brute force attempts.
        usleep(random_int(150000, 350000));
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$row['id'];
    db()->prepare('UPDATE admin_users SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?')
        ->execute([$row['id']]);
    return true;
}

function admin_logout(): void
{
    start_admin_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
