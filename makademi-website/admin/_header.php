<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';

// Pages that include this header expect to be admin-only — except setup/login.
$basename = basename($_SERVER['SCRIPT_NAME'] ?? '');
$publicAdminPages = ['login.php', 'setup-account.php'];
if (!in_array($basename, $publicAdminPages, true)) {
    require_admin();
}

$active = $active_admin_nav ?? '';
function _adm_nav(string $key, string $a): string { return $key === $a ? 'is-active' : ''; }
$me = current_admin();

$nav_items = [
    ['key' => 'dashboard', 'href' => 'index.php',    'label' => 'Dashboard', 'icon' => 'M3 12h6V3H3v9Zm0 9h6v-7H3v7Zm8 0h10v-9H11v9Zm0-18v7h10V3H11Z'],
    ['key' => 'programs',  'href' => 'programs.php', 'label' => 'Programs',  'icon' => 'M4 4h16v4H4V4Zm0 6h16v4H4v-4Zm0 6h16v4H4v-4Z'],
    ['key' => 'gallery',   'href' => 'gallery.php',  'label' => 'Gallery',   'icon' => 'M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Zm2 12 4-5 3 4 2-2 3 4H6Zm9-8a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z'],
];
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($admin_page_title ?? 'Makademi Admin') ?></title>
  <link rel="icon" href="../favicon.ico" sizes="48x48">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin">
  <div class="admin-shell">
    <aside class="admin-sidebar" aria-label="Primary navigation">
      <a href="index.php" class="brand">
        <span class="brand-mark" aria-hidden="true">M</span>
        <span class="brand-text">
          <strong>Makademi</strong>
          <small>Content Manager</small>
        </span>
      </a>
      <nav class="admin-nav">
<?php foreach ($nav_items as $item): ?>
        <a href="<?= e($item['href']) ?>" class="nav-link <?= _adm_nav($item['key'], $active) ?>">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= e($item['icon']) ?>"/></svg>
          <span><?= e($item['label']) ?></span>
        </a>
<?php endforeach; ?>
      </nav>
      <div class="sidebar-foot">
        <a href="../index.html" target="_blank" rel="noopener" class="ghost-link">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3h7v7h-2V6.4L10.7 14.7l-1.4-1.4L17.6 5H14V3ZM5 5h6v2H7v10h10v-4h2v6H5V5Z"/></svg>
          View public site
        </a>
      </div>
    </aside>

    <div class="admin-content">
      <header class="admin-topbar">
        <button type="button" class="menu-toggle" aria-label="Toggle navigation" onclick="document.body.classList.toggle('nav-open')">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18v2H3V6Zm0 5h18v2H3v-2Zm0 5h18v2H3v-2Z"/></svg>
        </button>
        <div class="topbar-title"><?= e($admin_page_heading ?? ucfirst($active ?: 'Admin')) ?></div>
        <div class="topbar-meta">
<?php if ($me): ?>
          <span class="who">Signed in as <strong><?= e($me['username']) ?></strong></span>
          <form method="post" action="logout.php" class="logout-form">
            <?= csrf_field() ?>
            <button type="submit" class="btn-admin ghost small">Sign out</button>
          </form>
<?php endif; ?>
        </div>
      </header>
      <main class="admin-main">
<?php
foreach (flash_take() as $f) {
    $kind = in_array($f['kind'], ['success','error','info'], true) ? $f['kind'] : 'info';
    echo '<div class="admin-flash ' . $kind . '">' . e($f['msg']) . '</div>';
}
?>
