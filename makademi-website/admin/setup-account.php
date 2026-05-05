<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';

// Ensure the session cookie is issued before ANY output, so the
// CSRF token printed in the form below matches the one verified on POST.
start_admin_session();
csrf_token();

$installedMarker = __DIR__ . '/.installed';

/**
 * Known placeholder values for app_secret. If the operator never replaced
 * the placeholder in includes/config.php, we refuse to run setup at all,
 * because the bootstrap-takeover protection below relies on the operator
 * knowing a value that an internet attacker cannot guess.
 */
const SETUP_PLACEHOLDER_SECRETS = [
    'CHANGE_ME_TO_64_RANDOM_HEX_CHARACTERS',
    'dev-only-secret-do-not-use-in-production',
    '',
];

/**
 * Refuse to run when:
 *  - admin/.installed marker exists, OR
 *  - admin_users table already has any row, OR
 *  - the operator never set a real app_secret in includes/config.php.
 */
function setup_already_done(string $marker): ?string {
    if (file_exists($marker)) {
        return 'Setup has already been completed. Delete admin/.installed (and ideally this file) if you need to re-run it.';
    }
    try {
        $count = (int)db()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
        if ($count > 0) {
            // Backfill the marker so future visits short-circuit fast.
            @file_put_contents($marker, "Completed at " . date('c') . "\n");
            return 'An admin account already exists. Delete admin/.installed and remove the matching row from admin_users to re-run.';
        }
    } catch (Throwable $e) {
        return 'Database not initialized yet. Run db/setup.sql in phpMyAdmin first, then reload this page.';
    }
    return null;
}

$cfg          = app_config();
$expectedSecret = (string)($cfg['app_secret'] ?? '');
$err = setup_already_done($installedMarker);
if (!$err && in_array($expectedSecret, SETUP_PLACEHOLDER_SECRETS, true)) {
    $err = 'Setup is locked: the file includes/config.php still has the default app_secret placeholder. '
         . 'Edit includes/config.php and change app_secret to a long, random value (e.g. 64 random hex chars), '
         . 'save, then reload this page.';
}

$done = false;
$createdUser = '';

if (!$err && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u  = trim((string)($_POST['username'] ?? ''));
    $p  = (string)($_POST['password'] ?? '');
    $p2 = (string)($_POST['password2'] ?? '');
    $providedSecret = (string)($_POST['setup_secret'] ?? '');

    if (!hash_equals($expectedSecret, $providedSecret)) {
        // Constant-time compare; surface a clear but non-leaky error.
        $err = 'The setup secret does not match the app_secret in includes/config.php. '
             . 'Open that file on the server, copy the app_secret value exactly, and paste it below.';
    } elseif ($u === '' || strlen($u) < 3 || strlen($u) > 64) {
        $err = 'Username must be 3-64 characters.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]+$/', $u)) {
        $err = 'Username may only contain letters, numbers, dot, dash, underscore.';
    } elseif (strlen($p) < 12) {
        $err = 'Password must be at least 12 characters.';
    } elseif ($p !== $p2) {
        $err = 'Passwords do not match.';
    } else {
        $hash = password_hash($p, PASSWORD_BCRYPT);
        $pdo  = db();
        $driver = (string)($cfg['db_driver'] ?? 'mysql');
        try {
            // Atomic single-admin enforcement. SQLite needs BEGIN IMMEDIATE
            // to grab the write lock up front; MySQL InnoDB transactions
            // serialize the COUNT + INSERT well enough for our 1-writer scale.
            if ($driver === 'sqlite') {
                $pdo->exec('BEGIN IMMEDIATE');
            } else {
                $pdo->beginTransaction();
            }
            $count = (int)$pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
            if ($count > 0) {
                $pdo->exec('ROLLBACK');
                @file_put_contents($installedMarker, "Completed at " . date('c') . "\n");
                $err = 'An admin account already exists. Delete admin/.installed and the row in admin_users to re-run.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
                $stmt->execute([$u, $hash]);
                $pdo->exec('COMMIT');
                file_put_contents($installedMarker, "Completed at " . date('c') . "\n");
                $done = true;
                $createdUser = $u;
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $err = 'Could not create admin user. The database may not be initialized.';
        }
    }
}

$admin_page_title = 'First-time admin setup';
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>First-time setup — Makademi Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin">
  <div class="login-shell">
    <div class="login-card">
      <div class="login-brand">
        <span class="mark" aria-hidden="true">M</span>
        <span class="text">
          <strong>Makademi</strong>
          <small>Content Manager</small>
        </span>
      </div>
      <h1>First-time admin setup</h1>
      <p class="sub">Create the single admin account that manages your Makademi site. This page disables itself after the account is created.</p>

<?php if ($done): ?>
      <div class="admin-flash success">
        Account created for <strong><?= e($createdUser) ?></strong>. For best security, delete this file from the server: <code>admin/setup-account.php</code>
      </div>
      <p><a href="login.php" class="btn-admin primary">Go to login</a></p>
<?php elseif ($err): ?>
      <div class="admin-flash error"><?= e($err) ?></div>
      <p><a href="login.php" class="btn-admin outline">Go to login</a></p>
<?php else: ?>
      <form method="post" class="admin-form" autocomplete="off">
        <?= csrf_field() ?>
        <div>
          <label for="setup_secret">Setup secret</label>
          <input type="password" id="setup_secret" name="setup_secret" required autofocus
                 placeholder="Paste the app_secret value from includes/config.php">
          <small style="display:block;margin-top:0.25rem;color:var(--admin-muted)">
            This blocks anyone on the internet from creating your admin account before you do.
            Open <code>includes/config.php</code> on your server and copy the value of <code>'app_secret'</code> exactly.
          </small>
        </div>
        <div>
          <label for="u">Username</label>
          <input type="text" id="u" name="username" required minlength="3" maxlength="64" pattern="[A-Za-z0-9_.\-]+">
        </div>
        <div>
          <label for="p">Password (min 12 chars)</label>
          <input type="password" id="p" name="password" required minlength="12">
        </div>
        <div>
          <label for="p2">Confirm password</label>
          <input type="password" id="p2" name="password2" required minlength="12">
        </div>
        <div class="actions">
          <button type="submit" class="btn-admin primary">Create admin account</button>
        </div>
      </form>
<?php endif; ?>
    </div>
  </div>
</body>
</html>
