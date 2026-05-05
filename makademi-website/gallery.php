<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$cfg          = app_config();
$gallery      = gallery_for_public();
$urlPrefix    = rtrim($cfg['gallery_url_prefix'], '/');

$page_title       = 'Gallery - Global Makademi';
$page_description = 'A look inside Global Makademi training: live-fire firefighter drills, classroom sessions, and on-site industrial programs.';
$active_nav       = 'gallery';

include __DIR__ . '/includes/partials/header.php';
?>

  <main style="padding-top:5rem">

    <section class="page-header centered">
      <div class="container">
        <h1>Training Gallery</h1>
        <p>A look inside our programs — live-fire drills, classroom sessions, and on-site industrial training around the world.</p>
      </div>
    </section>

    <section style="padding:5rem 0;background:var(--white)">
      <div class="container">
<?php if (empty($gallery)): ?>
        <p style="text-align:center;color:var(--slate-500)">Gallery is being updated. Please check back soon.</p>
<?php else: foreach ($gallery as $group): $cat = $group['category']; $images = $group['images']; ?>
        <div class="gallery-category">
          <div class="gallery-category-header">
<?php if (!empty($cat['badge_text'])): ?>
            <span class="badge badge-cat-<?= e($cat['badge_class']) ?>"><?= e($cat['badge_text']) ?></span>
<?php endif; ?>
            <h2><?= e($cat['name']) ?></h2>
<?php if (!empty($cat['description'])): ?>
            <p><?= e($cat['description']) ?></p>
<?php endif; ?>
          </div>
<?php if (!empty($images)): ?>
          <div class="photo-grid" data-lightbox-group="<?= e($cat['slug']) ?>">
<?php foreach ($images as $img):
            $src = $urlPrefix . '/' . $img['filename'];
?>
            <button type="button" class="photo-tile" data-lightbox-src="<?= e($src) ?>" data-lightbox-caption="<?= e($img['caption']) ?>">
              <img src="<?= e($src) ?>" alt="<?= e($img['caption']) ?>" loading="lazy">
            </button>
<?php endforeach; ?>
          </div>
<?php endif; ?>
        </div>
<?php endforeach; endif; ?>
      </div>
    </section>

    <!-- CTA -->
    <section class="cta-banner">
      <div class="dot-pattern"></div>
      <div class="container inner">
        <h2>Want to see one of our programs in person?</h2>
        <p>We host on-site visits and tailor corporate programs at facilities across Türkiye, Libya, and the wider region.</p>
        <a href="contact.html" class="btn btn-gold btn-lg">Get in Touch</a>
      </div>
    </section>

  </main>

<?php include __DIR__ . '/includes/partials/lightbox.php'; ?>
<?php include __DIR__ . '/includes/partials/footer.php'; ?>
