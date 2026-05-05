<?php
declare(strict_types=1);

$active_admin_nav  = 'dashboard';
$admin_page_title  = 'Dashboard — Makademi Admin';
require __DIR__ . '/_header.php';

$pdo = db();
$counts = [
    'programs'  => (int)$pdo->query('SELECT COUNT(*) FROM programs')->fetchColumn(),
    'published' => (int)$pdo->query('SELECT COUNT(*) FROM programs WHERE is_published = 1')->fetchColumn(),
    'categories'=> (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
    'gallery_categories' => (int)$pdo->query('SELECT COUNT(*) FROM gallery_categories')->fetchColumn(),
    'gallery_images'     => (int)$pdo->query('SELECT COUNT(*) FROM gallery_images')->fetchColumn(),
];
?>
<h1>Dashboard</h1>
<p>Welcome back. Use the sections below to manage what visitors see on your Makademi site.</p>

<div class="admin-stats">
  <div class="admin-stat"><div class="num"><?= $counts['programs'] ?></div><div class="label">Programs total</div></div>
  <div class="admin-stat"><div class="num"><?= $counts['published'] ?></div><div class="label">Published programs</div></div>
  <div class="admin-stat"><div class="num"><?= $counts['categories'] ?></div><div class="label">Program categories</div></div>
  <div class="admin-stat"><div class="num"><?= $counts['gallery_categories'] ?></div><div class="label">Gallery categories</div></div>
  <div class="admin-stat"><div class="num"><?= $counts['gallery_images'] ?></div><div class="label">Gallery images</div></div>
</div>

<div class="admin-card">
  <h2 style="margin-top:0">Quick actions</h2>
  <div style="display:flex;gap:0.5rem;flex-wrap:wrap">
    <a class="btn-admin primary" href="programs.php?action=new">Add a new program</a>
    <a class="btn-admin outline" href="programs.php">Manage programs</a>
    <a class="btn-admin gold" href="gallery.php">Upload gallery photos</a>
    <a class="btn-admin outline" href="../courses.php" target="_blank" rel="noopener">View public Programs page</a>
    <a class="btn-admin outline" href="../gallery.php" target="_blank" rel="noopener">View public Gallery page</a>
  </div>
</div>

<div class="admin-card">
  <h2 style="margin-top:0">How this works</h2>
  <ul style="line-height:1.7;color:var(--admin-muted);margin:0;padding-left:1.25rem">
    <li>Edits you make here go live on the public site immediately — no zip re-upload.</li>
    <li>Photos you upload are saved on Hostinger under <code>assets/images/gallery/</code>.</li>
    <li>If you ever need to add a new login, ask your developer — only one admin account exists by design.</li>
  </ul>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
