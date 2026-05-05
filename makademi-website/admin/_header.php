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
function _adm_nav(string $key, string $a): string { return $key === $a ? 'active' : ''; }
function _adm_crumb(string $a): string {
    return match ($a) {
        'dashboard' => 'Dashboard',
        'programs'  => 'Programs',
        'gallery'   => 'Gallery',
        default     => 'Admin',
    };
}
$me = current_admin();
$initial = strtoupper(substr($me['username'] ?? 'A', 0, 1));
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($admin_page_title ?? 'Makademi Admin') ?></title>
  <link rel="icon" href="../favicon.ico" sizes="48x48">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin">
  <div class="admin-shell">

    <aside class="admin-sidebar">
      <div class="brand-block">
        <a href="index.php" class="brand-mark">
          <span class="logo">M</span>
          <span class="name">Makademi<small>Content Manager</small></span>
        </a>
      </div>

      <div class="nav-label">Manage</div>
      <nav>
        <a href="index.php" class="<?= _adm_nav('dashboard', $active) ?>">
          <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
          Dashboard
        </a>
        <a href="programs.php" class="<?= _adm_nav('programs', $active) ?>">
          <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
          Programs
        </a>
        <a href="gallery.php" class="<?= _adm_nav('gallery', $active) ?>">
          <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.5-3.5L9 20"/></svg>
          Gallery
        </a>
      </nav>

      <div class="side-foot">
        Edits go live instantly on the public site.<br>
        <a href="../index.html" target="_blank" rel="noopener">Open public site →</a>
      </div>
    </aside>

    <header class="admin-topbar">
      <div class="crumbs">
        <span>Makademi Admin</span>
        <span>›</span>
        <strong><?= e(_adm_crumb($active)) ?></strong>
      </div>
      <div class="meta">
        <a class="view-site" href="../index.html" target="_blank" rel="noopener">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          <span>View site</span>
        </a>
        <span class="who">
          <span class="avatar"><?= e($initial) ?></span>
          <span>Hi, <?= e($me['username']) ?></span>
        </span>
        <form method="post" action="logout.php" class="logout-form">
          <?= csrf_field() ?>
          <button type="submit" class="link-button">Sign out</button>
        </form>
      </div>
    </header>

    <main class="admin-main">
<?php
foreach (flash_take() as $f) {
    $kind = in_array($f['kind'], ['success','error','info'], true) ? $f['kind'] : 'info';
    echo '<div class="admin-flash ' . $kind . '">' . e($f['msg']) . '</div>';
}
?>
