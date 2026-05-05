<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$categories = categories_all();
$programs   = programs_for_public();
$total      = count($programs);

$page_title       = 'Training Programs - Global Makademi';
$page_description = 'Explore our comprehensive catalog of 100+ specialized industrial training courses designed for professionals.';
$active_nav       = 'programs';

include __DIR__ . '/includes/partials/header.php';
?>

  <main style="padding-top:5rem">
    <div class="page-header">
      <div class="container">
        <h1>Training Programs</h1>
        <p>Explore our comprehensive catalog of <?= (int)$total ?>+ specialized industrial training courses designed for professionals.</p>
      </div>
    </div>

    <div class="container" style="margin-top:-2rem;position:relative;z-index:10">
      <div class="card card-no-border card-shadow-lg" style="padding:1.5rem">
        <div style="display:flex;flex-direction:column;gap:1rem;justify-content:space-between;align-items:center" class="search-row">
          <div class="search-bar" style="width:100%;max-width:24rem">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" id="course-search" class="input input-lg" placeholder="Search programs by title or keyword..." style="padding-left:2.5rem">
          </div>
          <div style="font-size:0.875rem;font-weight:600;color:var(--navy)">
            Showing <strong id="course-count"><?= (int)$total ?></strong> of <?= (int)$total ?> programs
          </div>
        </div>
      </div>
    </div>

    <div class="container" style="margin-top:2rem;padding-bottom:6rem">
      <div class="courses-layout">
        <div class="courses-sidebar">
          <h3>Categories</h3>
          <button class="cat-btn active" data-category="All">All</button>
<?php foreach ($categories as $cat): ?>
          <button class="cat-btn" data-category="<?= e($cat['name']) ?>" data-slug="<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></button>
<?php endforeach; ?>
        </div>
        <div class="courses-grid">
          <div class="grid grid-2" id="courses-container">
<?php foreach ($programs as $p): ?>
            <div class="card course-card card-hover course-item"
                 data-title="<?= e($p['title']) ?>"
                 data-desc="<?= e($p['description']) ?>"
                 data-category="<?= e($p['category_name']) ?>"
                 style="display:flex;flex-direction:column">
              <div class="card-body">
                <div class="mb-3"><span class="badge badge-cat-<?= e($p['badge_class']) ?>"><?= e($p['category_name']) ?></span></div>
                <h3><?= e($p['title']) ?></h3>
                <p class="desc"><?= e($p['description']) ?></p>
                <div class="meta" style="padding-top:1rem;border-top:1px solid var(--slate-100);margin-top:auto">
<?php if (!empty($p['duration'])): ?>
                  <span class="item"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> <?= e($p['duration']) ?></span>
<?php endif; ?>
<?php if (!empty($p['location'])): ?>
                  <span class="item"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg> <?= e($p['location']) ?></span>
<?php endif; ?>
                </div>
                <div class="course-actions">
<?php if (!empty($p['detail_url'])): ?>
                  <a href="<?= e($p['detail_url']) ?>" class="view-link">View Details <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>
                  <a href="<?= e($p['detail_url']) ?>" class="btn btn-gold btn-sm">View Details</a>
<?php else: ?>
                  <div></div>
                  <a href="<?= e(inquiry_url($p['title'])) ?>" class="btn btn-gold btn-sm">Inquire Now</a>
<?php endif; ?>
                </div>
              </div>
            </div>
<?php endforeach; ?>
          </div>
          <div id="no-results" class="no-results" style="display:none;margin-top:1.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <h3 style="font-size:1.25rem;font-weight:700;color:var(--navy);margin-bottom:0.5rem">No programs found</h3>
            <p style="color:var(--slate-500)">Try adjusting your search terms or selecting a different category.</p>
            <button class="btn btn-outline-navy" style="margin-top:1.5rem" id="clear-filters">Clear Filters</button>
          </div>
        </div>
      </div>
    </div>
  </main>

<?php include __DIR__ . '/includes/partials/footer.php'; ?>
<style>
  @media(min-width:768px){.search-row{flex-direction:row!important}}
</style>
