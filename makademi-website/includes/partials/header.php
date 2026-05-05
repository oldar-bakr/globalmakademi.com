<?php
/**
 * Site header partial.
 *
 * Variables expected (set before include):
 *   $page_title       — string  e.g. 'Training Programs - Global Makademi'
 *   $page_description — string  meta description
 *   $active_nav       — string  one of: home|about|programs|gallery|contact (optional)
 */
$active_nav = $active_nav ?? '';
function _nav_cls(string $key, string $active): string {
    return 'nav-link' . ($key === $active ? ' active' : '');
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_title ?? 'Global Makademi') ?></title>
  <meta name="description" content="<?= e($page_description ?? '') ?>">
  <link rel="icon" href="favicon.ico" sizes="48x48">
  <link rel="icon" href="favicon-32.png" type="image/png" sizes="32x32">
  <link rel="icon" href="favicon-16.png" type="image/png" sizes="16x16">
  <link rel="apple-touch-icon" href="apple-touch-icon.png">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

  <!-- HEADER -->
  <header class="site-header">
    <div class="container">
      <div class="header-inner">
        <a href="index.html" class="brand">
          <img src="assets/images/makademi-logo.jpeg" alt="Global Makademi Logo">
          <div class="brand-text">
            <span class="brand-name">GLOBAL MAKADEMI</span>
            <span class="brand-sub">MAKADEMI TRAINING &amp; CONSULTANCY LTD</span>
          </div>
        </a>
        <nav class="desktop-nav">
          <div class="nav-links">
            <a href="index.html" class="<?= _nav_cls('home', $active_nav) ?>">Home</a>
            <a href="about.html" class="<?= _nav_cls('about', $active_nav) ?>">About</a>
            <a href="courses.php" class="<?= _nav_cls('programs', $active_nav) ?>">Programs</a>
            <a href="gallery.php" class="<?= _nav_cls('gallery', $active_nav) ?>">Gallery</a>
            <a href="contact.html" class="<?= _nav_cls('contact', $active_nav) ?>">Contact</a>
          </div>
          <a href="contact.html" class="btn btn-gold">Inquire Now</a>
        </nav>
        <button class="mobile-toggle" id="mobile-menu-btn" aria-label="Toggle menu" aria-expanded="false">
          <svg id="menu-icon-open" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
          <svg id="menu-icon-close" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
      </div>
    </div>
    <div class="mobile-nav" id="mobile-nav">
      <a href="index.html" class="<?= _nav_cls('home', $active_nav) ?>">Home</a>
      <a href="about.html" class="<?= _nav_cls('about', $active_nav) ?>">About</a>
      <a href="courses.php" class="<?= _nav_cls('programs', $active_nav) ?>">Programs</a>
      <a href="gallery.php" class="<?= _nav_cls('gallery', $active_nav) ?>">Gallery</a>
      <a href="contact.html" class="<?= _nav_cls('contact', $active_nav) ?>">Contact</a>
      <div class="mobile-cta">
        <a href="contact.html" class="btn btn-gold btn-full" style="font-size:1.125rem;height:3rem">Inquire Now</a>
      </div>
    </div>
  </header>
