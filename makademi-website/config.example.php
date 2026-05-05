<?php
/**
 * Makademi site config — TEMPLATE.
 *
 * On Hostinger:
 *   1. Copy this file to: includes/config.php
 *   2. Fill in the MySQL credentials from hPanel → Databases → MySQL.
 *   3. Set a long, random APP_SECRET (used for session security).
 *   4. Save. Do NOT commit includes/config.php anywhere public.
 *
 * For local development on Replit, includes/config.php already
 * exists and points at a SQLite file under db/ — leave that alone.
 */

return [
    // 'mysql' for Hostinger production, 'sqlite' for local dev.
    'db_driver' => 'mysql',

    // MySQL settings (used when db_driver = 'mysql').
    'db_host'     => 'localhost',
    'db_name'     => 'u123456789_makademi',
    'db_user'     => 'u123456789_admin',
    'db_pass'     => 'CHANGE_ME_BEFORE_DEPLOYING',
    'db_port'     => 3306,
    'db_charset'  => 'utf8mb4',

    // SQLite settings (used when db_driver = 'sqlite' — local dev only).
    'sqlite_path' => __DIR__ . '/../db/makademi.sqlite',

    // 32+ characters of random bytes. Generate with `openssl rand -hex 32`.
    'app_secret'  => 'CHANGE_ME_TO_64_RANDOM_HEX_CHARACTERS',

    // Where uploaded gallery images are stored on disk (relative to site root).
    'gallery_upload_dir' => __DIR__ . '/../assets/images/gallery',

    // Public URL prefix for those images (relative to site root).
    'gallery_url_prefix' => 'assets/images/gallery',

    // Set to true once /admin/setup-account.php has created the admin
    // user. The setup page refuses to run again when this is true.
    // (You can also delete the setup file from the server entirely.)
    'setup_complete' => false,
];
