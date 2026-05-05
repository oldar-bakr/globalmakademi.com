<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';

start_admin_session();
if (current_admin()) {
    redirect('index.php');
}

$err = null;
// Strict allowlist: ignore anything in `next` that isn't one of the
// known admin pages. Defends against open-redirect (`//evil.com`,
// `\\evil.com`, `javascript:`, scheme-relative URLs, etc.).
$NEXT_ALLOWED = ['index.php', 'programs.php', 'gallery.php'];
$next = 'index.php';
$nextRaw = $_GET['next'] ?? '';
if (is_string($nextRaw) && $nextRaw !== '') {
    $path = parse_url($nextRaw, PHP_URL_PATH);
    if (is_string($path)) {
        $base = basename($path);
        if (in_array($base, $NEXT_ALLOWED, true)) {
            $next = $base;
        }
    }
}

// Basic per-session brute-force throttle: lock for 60s after 5 failed attempts.
$_SESSION['login_fails']     = $_SESSION['login_fails']     ?? 0;
$_SESSION['login_locked_at'] = $_SESSION['login_locked_at'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $now = time();
    if ($_SESSION['login_fails'] >= 5 && ($now - $_SESSION['login_locked_at']) < 60) {
        $err = 'Too many failed attempts. Wait a minute and try again.';
    } else {
        $u = trim((string)($_POST['username'] ?? ''));
        $p = (string)($_POST['password'] ?? '');
        if (admin_login($u, $p)) {
            $_SESSION['login_fails'] = 0;
            $_SESSION['login_locked_at'] = 0;
            redirect($next);
        } else {
            $_SESSION['login_fails']++;
            $_SESSION['login_locked_at'] = $now;
            $err = 'Invalid username or password.';
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign in — Makademi Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin">
  <div class="login-shell">
    <div class="login-card">
      <div class="login-brand">
        <span class="logo">M</span>
        <div>
          <h1>Makademi Admin</h1>
          <p class="sub">Sign in to manage programs and gallery</p>
        </div>
      </div>
<?php if ($err): ?>
      <div class="admin-flash error"><?= e($err) ?></div>
<?php endif; ?>
      <form method="post" class="admin-form" autocomplete="on">
        <?= csrf_field() ?>
        <div>
          <label for="u">Username</label>
          <input type="text" id="u" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
        </div>
        <div>
          <label for="p">Password</label>
          <input type="password" id="p" name="password" required>
        </div>
        <div class="actions">
          <button type="submit" class="btn-admin primary">Sign in</button>
        </div>
      </form>
      <div class="login-foot">Makademi Training &amp; Consultancy Ltd</div>
    </div>
  </div>
</body>
</html>
