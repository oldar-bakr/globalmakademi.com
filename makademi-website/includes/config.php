<?php
/**
 * Makademi site config — LOCAL DEV (Replit).
 *
 * This file is gitignored and is the LOCAL DEVELOPMENT copy only.
 * It points at the SQLite file under db/makademi.sqlite so the
 * Replit preview works without a MySQL server.
 *
 * The PRODUCTION config.php lives only on Hostinger under
 * public_html/includes/config.php and is never committed.
 *
 * If you need the production template, see config.example.php in
 * the website root.
 */

return [
    // 'sqlite' for local dev on Replit, 'mysql' for Hostinger.
    'db_driver' => 'sqlite',

    // MySQL settings (unused while db_driver = 'sqlite').
    'db_host'     => 'localhost',
    'db_name'     => 'u123456789_makademi',
    'db_user'     => 'u123456789_admin',
    'db_pass'     => 'placeholder-not-used-locally',
    'db_port'     => 3306,
    'db_charset'  => 'utf8mb4',

    // SQLite path (used while db_driver = 'sqlite').
    'sqlite_path' => __DIR__ . '/../db/makademi.sqlite',

    // Local dev secret — never used in production.
    'app_secret'  => 'replit-local-dev-secret-d4e7c0a3-not-for-public-deploy',

    // Where uploaded gallery images live on disk (relative to site root).
    'gallery_upload_dir' => __DIR__ . '/../assets/images/gallery',

    // Public URL prefix for those images (relative to site root).
    'gallery_url_prefix' => 'assets/images/gallery',

    // Local admin user already created during initial setup.
    'setup_complete' => true,
];
