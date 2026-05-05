<?php
declare(strict_types=1);

/**
 * Returns a singleton PDO connection driven by includes/config.php.
 * Throws on misconfiguration.
 *
 * In SQLite (dev) mode, the schema is auto-applied from
 * db/setup.sqlite.sql on first boot, and a throwaway dev admin
 * account is seeded so you can log in immediately in Replit.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = require __DIR__ . '/config.php';
    $driver = $cfg['db_driver'] ?? 'mysql';

    if ($driver === 'sqlite') {
        $path = $cfg['sqlite_path'];
        $dir  = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $isFresh = !file_exists($path) || filesize($path) === 0;
        $pdo = new PDO('sqlite:' . $path);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        // First-boot schema bootstrap.
        if ($isFresh || !sqlite_has_tables($pdo)) {
            $sql = @file_get_contents(__DIR__ . '/../db/setup.sqlite.sql');
            if ($sql !== false && $sql !== '') {
                $pdo->exec($sql);
            }
        }

        // Seed a dev admin so you don't have to run setup-account.php
        // every time the workspace restarts. SQLite (dev) only.
        sqlite_seed_dev_admin($pdo);

        return $pdo;
    }

    if ($driver === 'mysql') {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['db_host'],
            (int)$cfg['db_port'],
            $cfg['db_name'],
            $cfg['db_charset']
        );
        $pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$cfg['db_charset']}",
        ]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    }

    throw new RuntimeException("Unsupported db_driver: {$driver}");
}

/** Returns the loaded config array. */
function app_config(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/config.php';
    }
    return $cfg;
}

function sqlite_has_tables(PDO $pdo): bool
{
    try {
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='admin_users'");
        return (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Dev-only convenience: if the SQLite admin_users table is empty,
 * create a known account so the workspace user can log in instantly.
 * Credentials are surfaced on the login screen in dev mode.
 */
function sqlite_seed_dev_admin(PDO $pdo): void
{
    try {
        $count = (int)$pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    } catch (Throwable $e) {
        return;
    }
    if ($count > 0) return;
    $hash = password_hash(DEV_ADMIN_PASSWORD, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
    $stmt->execute([DEV_ADMIN_USERNAME, $hash]);
}

/** Throwaway local credentials. Never used on Hostinger (MySQL driver). */
const DEV_ADMIN_USERNAME = 'admin';
const DEV_ADMIN_PASSWORD = 'makademi-dev-pass';

function is_dev_sqlite(): bool
{
    $cfg = app_config();
    return ($cfg['db_driver'] ?? '') === 'sqlite';
}
