#!/usr/bin/env node
import fs from 'node:fs';
import path from 'node:path';

const ROOT = 'makademi-website';
const OUT_DIR = path.join(ROOT, 'data');
fs.mkdirSync(OUT_DIR, { recursive: true });

function decodeHtml(s) {
  return String(s)
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#39;/g, "'")
    .replace(/&nbsp;/g, ' ');
}

function categorySlug(name) {
  const map = {
    'Engineering & Technical': 'engineering-technical',
    'Maintenance & Production': 'maintenance-production',
    'Banking & Finance': 'banking-finance',
    'Telecom & Digital': 'telecom-digital',
    'Fire Safety & Emergency': 'fire-safety-emergency',
    'Health, Safety & Environment': 'health-safety-environment',
    'Corrosion & Integrity': 'corrosion-integrity',
    'Management & Leadership': 'management-leadership',
    'Finance & Accounting': 'finance-accounting',
    'High-Value Programs': 'high-value-programs',
  };
  return map[name] || name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

function badgeClass(name) {
  const map = {
    'Engineering & Technical': 'engineering',
    'Maintenance & Production': 'maintenance',
    'Banking & Finance': 'finance',
    'Telecom & Digital': 'telecom',
    'Fire Safety & Emergency': 'fire',
    'Health, Safety & Environment': 'hse',
    'Corrosion & Integrity': 'corrosion',
    'Management & Leadership': 'management',
    'Finance & Accounting': 'finance',
    'High-Value Programs': 'high-value',
  };
  return map[name] || 'engineering';
}

// --- Programs ---
const coursesHtml = fs.readFileSync(path.join(ROOT, 'courses.html'), 'utf8');

// Categories from sidebar buttons
const categories = [];
const seenCat = new Set();
const catRe = /<button class="cat-btn[^"]*" data-category="([^"]+)"(?:\s+data-slug="([^"]+)")?[^>]*>([^<]+)<\/button>/g;
let cm;
let catSort = 0;
while ((cm = catRe.exec(coursesHtml))) {
  const name = decodeHtml(cm[1]);
  if (name === 'All') continue;
  if (seenCat.has(name)) continue;
  seenCat.add(name);
  const slug = cm[2] || categorySlug(name);
  categories.push({
    sort_order: catSort++,
    name,
    slug,
    badge_class: badgeClass(name),
  });
}

// Programs: parse each .course-item card.
const programs = [];
let progSort = 0;
const cardRe = /<div class="card course-card card-hover course-item"\s+data-title="([^"]+)"\s+data-desc="([^"]+)"\s+data-category="([^"]+)"[^>]*>([\s\S]*?)<\/div>\s*<\/div>\s*<\/div>/g;
let cardMatch;
while ((cardMatch = cardRe.exec(coursesHtml))) {
  const title = decodeHtml(cardMatch[1]);
  const description = decodeHtml(cardMatch[2]);
  const category = decodeHtml(cardMatch[3]);
  const inner = cardMatch[4];

  // Duration: text inside first .item span (after the clock svg)
  const metaItems = [...inner.matchAll(/<span class="item">[\s\S]*?<\/svg>\s*([^<]+)<\/span>/g)].map((m) => decodeHtml(m[1]).trim());
  const duration = metaItems[0] || '';
  const location = metaItems[1] || '';

  // Pre-built detail pages we want to keep linking to.
  const detailUrlMap = {
    'Firefighting in Oil Facilities (JOIFF Accredited)': 'courses/firefighting-joiff.html',
    'Electric and Hybrid Vehicle Technology: 800V Architecture & BMS Diagnostics': 'courses/electric-hybrid-vehicle.html',
  };

  programs.push({
    sort_order: progSort++,
    title,
    description,
    category,
    duration,
    location,
    detail_url: detailUrlMap[title] || '',
    is_published: 1,
  });
}

// --- Gallery ---
const galleryHtml = fs.readFileSync(path.join(ROOT, 'gallery.html'), 'utf8');

// Categories: <h2 class="gallery-category-title">Title</h2>
const galleryCategories = [];
const galleryImages = [];

const sectionRe = /<div class="gallery-category">([\s\S]*?)<\/div>\s*<\/div>/g;
let secMatch;
let gcSort = 0;
let imgSort = 0;
while ((secMatch = sectionRe.exec(galleryHtml))) {
  const sec = secMatch[1];
  const titleMatch = sec.match(/<h2[^>]*>([^<]+)<\/h2>/);
  if (!titleMatch) continue;
  const catName = decodeHtml(titleMatch[1]).trim();
  const groupMatch = sec.match(/data-lightbox-group="([^"]+)"/);
  const catSlug = (groupMatch ? groupMatch[1] : catName.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''));
  const badgeMatch = sec.match(/<span class="badge badge-cat-([a-z-]+)">([^<]+)<\/span>/);
  const descMatch = sec.match(/<p>([^<]+)<\/p>/);
  galleryCategories.push({
    sort_order: gcSort++,
    name: catName,
    slug: catSlug,
    badge_class: badgeMatch ? badgeMatch[1] : 'engineering',
    badge_text: badgeMatch ? decodeHtml(badgeMatch[2]) : '',
    description: descMatch ? decodeHtml(descMatch[1]).trim() : '',
  });

  const tileRe = /<button[^>]*class="photo-tile"[^>]*data-lightbox-src="([^"]+)"[^>]*data-lightbox-caption="([^"]+)"[^>]*>/g;
  let tm;
  let perCatSort = 0;
  while ((tm = tileRe.exec(sec))) {
    galleryImages.push({
      sort_order: perCatSort++,
      gallery_category_slug: catSlug,
      filename: path.basename(decodeHtml(tm[1])),
      caption: decodeHtml(tm[2]),
    });
    imgSort++;
  }
}

// Write JSON for inspection
fs.writeFileSync(path.join(OUT_DIR, 'extracted.json'), JSON.stringify({
  categories,
  programs,
  galleryCategories,
  galleryImages,
}, null, 2));

console.log(`Categories: ${categories.length}`);
console.log(`Programs: ${programs.length}`);
console.log(`Gallery categories: ${galleryCategories.length}`);
console.log(`Gallery images: ${galleryImages.length}`);

// --- Generate SQL ---
function escMy(v) {
  if (v === null || v === undefined) return 'NULL';
  if (typeof v === 'number') return String(v);
  return "'" + String(v).replace(/\\/g, '\\\\').replace(/'/g, "''") + "'";
}
const escSqlite = escMy; // identical for our values (no backslashes in source)

function buildSql(dialect) {
  const isMy = dialect === 'mysql';
  const ai = isMy ? 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
  const txt = isMy ? 'TEXT' : 'TEXT';
  const vc = (n) => (isMy ? `VARCHAR(${n})` : 'TEXT');
  const ts = isMy ? 'DATETIME DEFAULT CURRENT_TIMESTAMP' : "DATETIME DEFAULT CURRENT_TIMESTAMP";
  const charset = isMy ? ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';
  const engine = isMy ? ' ENGINE=InnoDB' : '';

  const lines = [];
  lines.push('-- Makademi schema + seed data (auto-generated, do not hand-edit).');
  lines.push('-- Dialect: ' + dialect);
  if (isMy) {
    lines.push('SET NAMES utf8mb4;');
    lines.push('SET FOREIGN_KEY_CHECKS = 0;');
  } else {
    lines.push('PRAGMA foreign_keys = OFF;');
  }
  lines.push('');

  // Drop in dependency order
  for (const t of ['gallery_images', 'gallery_categories', 'programs', 'categories', 'admin_users']) {
    lines.push(`DROP TABLE IF EXISTS ${t};`);
  }
  lines.push('');

  // admin_users
  lines.push(`CREATE TABLE admin_users (
  id ${ai},
  username ${vc(64)} NOT NULL UNIQUE,
  password_hash ${vc(255)} NOT NULL,
  created_at ${ts},
  last_login_at DATETIME NULL
)${engine}${charset};`);
  lines.push('');

  // categories
  lines.push(`CREATE TABLE categories (
  id ${ai},
  name ${vc(128)} NOT NULL UNIQUE,
  slug ${vc(128)} NOT NULL UNIQUE,
  badge_class ${vc(64)} NOT NULL DEFAULT 'engineering',
  sort_order INT NOT NULL DEFAULT 0
)${engine}${charset};`);
  lines.push('');

  // programs
  lines.push(`CREATE TABLE programs (
  id ${ai},
  title ${vc(255)} NOT NULL,
  description ${txt} NOT NULL,
  category_id INT NOT NULL,
  duration ${vc(64)} NOT NULL DEFAULT '',
  location ${vc(64)} NOT NULL DEFAULT '',
  detail_url ${vc(255)} NOT NULL DEFAULT '',
  is_published TINYINT NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at ${ts},
  updated_at ${ts}
)${engine}${charset};`);
  lines.push('');

  // gallery_categories
  lines.push(`CREATE TABLE gallery_categories (
  id ${ai},
  name ${vc(128)} NOT NULL UNIQUE,
  slug ${vc(128)} NOT NULL UNIQUE,
  badge_class ${vc(64)} NOT NULL DEFAULT 'engineering',
  badge_text ${vc(128)} NOT NULL DEFAULT '',
  description ${txt} NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
)${engine}${charset};`);
  lines.push('');

  // gallery_images
  lines.push(`CREATE TABLE gallery_images (
  id ${ai},
  gallery_category_id INT NOT NULL,
  filename ${vc(255)} NOT NULL,
  caption ${vc(500)} NOT NULL DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0,
  created_at ${ts}
)${engine}${charset};`);
  lines.push('');

  // Seed: categories
  lines.push('-- Categories');
  for (const c of categories) {
    lines.push(`INSERT INTO categories (name, slug, badge_class, sort_order) VALUES (${escMy(c.name)}, ${escMy(c.slug)}, ${escMy(c.badge_class)}, ${c.sort_order});`);
  }
  lines.push('');

  // Seed: programs (resolve category_id by name lookup)
  lines.push('-- Programs');
  // Build a lookup using subquery so we don't depend on auto-increment ordering
  for (const p of programs) {
    lines.push(`INSERT INTO programs (title, description, category_id, duration, location, detail_url, is_published, sort_order) VALUES (${escMy(p.title)}, ${escMy(p.description)}, (SELECT id FROM categories WHERE name = ${escMy(p.category)}), ${escMy(p.duration)}, ${escMy(p.location)}, ${escMy(p.detail_url)}, ${p.is_published}, ${p.sort_order});`);
  }
  lines.push('');

  // Seed: gallery categories
  lines.push('-- Gallery categories');
  for (const c of galleryCategories) {
    lines.push(`INSERT INTO gallery_categories (name, slug, badge_class, badge_text, description, sort_order) VALUES (${escMy(c.name)}, ${escMy(c.slug)}, ${escMy(c.badge_class)}, ${escMy(c.badge_text)}, ${escMy(c.description)}, ${c.sort_order});`);
  }
  lines.push('');

  // Seed: gallery images
  lines.push('-- Gallery images');
  for (const img of galleryImages) {
    lines.push(`INSERT INTO gallery_images (gallery_category_id, filename, caption, sort_order) VALUES ((SELECT id FROM gallery_categories WHERE slug = ${escMy(img.gallery_category_slug)}), ${escMy(img.filename)}, ${escMy(img.caption)}, ${img.sort_order});`);
  }
  lines.push('');

  if (isMy) {
    lines.push('SET FOREIGN_KEY_CHECKS = 1;');
  } else {
    lines.push('PRAGMA foreign_keys = ON;');
  }

  return lines.join('\n') + '\n';
}

const dbDir = path.join(ROOT, 'db');
fs.mkdirSync(dbDir, { recursive: true });
fs.writeFileSync(path.join(dbDir, 'setup.sql'), buildSql('mysql'));
fs.writeFileSync(path.join(dbDir, 'setup.sqlite.sql'), buildSql('sqlite'));
console.log('Wrote db/setup.sql and db/setup.sqlite.sql');
