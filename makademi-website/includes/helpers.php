<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url, int $code = 303): void
{
    header('Location: ' . $url, true, $code);
    exit;
}

/** Append/replace a flash message that survives one redirect. */
function flash_set(string $kind, string $msg): void
{
    require_once __DIR__ . '/auth.php';
    start_admin_session();
    $_SESSION['flash'][] = ['kind' => $kind, 'msg' => $msg];
}

function flash_take(): array
{
    require_once __DIR__ . '/auth.php';
    start_admin_session();
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function categories_all(): array
{
    $stmt = db()->query('SELECT * FROM categories ORDER BY sort_order, name');
    return $stmt->fetchAll();
}

function gallery_categories_all(): array
{
    $stmt = db()->query('SELECT * FROM gallery_categories ORDER BY sort_order, name');
    return $stmt->fetchAll();
}

/**
 * Returns programs grouped for the public courses.php grid.
 * Each row joins the category to expose name + badge_class.
 */
function programs_for_public(): array
{
    $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.badge_class
            FROM programs p
            JOIN categories c ON c.id = p.category_id
            WHERE p.is_published = 1
            ORDER BY p.sort_order, p.id';
    return db()->query($sql)->fetchAll();
}

function programs_all_admin(): array
{
    $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.badge_class
            FROM programs p
            JOIN categories c ON c.id = p.category_id
            ORDER BY p.sort_order, p.id';
    return db()->query($sql)->fetchAll();
}

function gallery_for_public(): array
{
    $cats = gallery_categories_all();
    if (!$cats) return [];
    $stmt = db()->query('SELECT * FROM gallery_images ORDER BY gallery_category_id, sort_order, id');
    $images = $stmt->fetchAll();
    $byCat = [];
    foreach ($images as $img) {
        $byCat[(int)$img['gallery_category_id']][] = $img;
    }
    $out = [];
    foreach ($cats as $c) {
        $out[] = [
            'category' => $c,
            'images'   => $byCat[(int)$c['id']] ?? [],
        ];
    }
    return $out;
}

/** Inquiry-link helper — keeps the existing query-string convention. */
function inquiry_url(string $title): string
{
    return 'contact.php?subject=' . rawurlencode('Inquiry: ' . $title);
}
