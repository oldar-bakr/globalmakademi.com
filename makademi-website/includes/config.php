<?php
/**
 * Local development config (SQLite).
 *
 * This file is shipped in the Replit dev environment so the site
 * runs out of the box. On Hostinger, replace the contents of this
 * file with the values from config.example.php (keeping the same
 * filename includes/config.php).
 */

return [
    'db_driver'  => 'sqlite',
    'sqlite_path' => __DIR__ . '/../db/makademi.sqlite',

    // MySQL fields are unused in dev but kept for shape parity.
    'db_host'    => 'localhost',
    'db_name'    => '',
    'db_user'    => '',
    'db_pass'    => '',
    'db_port'    => 3306,
    'db_charset' => 'utf8mb4',

    // Dev-only secret. NOT a placeholder (so /admin/setup-account.php works
    // locally for testing). Production must set its own value derived from
    // config.example.php — never reuse this string on a public server.
    'app_secret' => 'replit-local-dev-secret-d4e7c0a3-not-for-public-deploy',

    'gallery_upload_dir' => __DIR__ . '/../assets/images/gallery',
    'gallery_url_prefix' => 'assets/images/gallery',

    'setup_complete' => false,
];
