<?php
declare(strict_types=1);

$active_admin_nav = 'gallery';
$admin_page_title = 'Gallery — Makademi Admin';
require __DIR__ . '/_header.php';

$pdo  = db();
$cfg  = app_config();
$dir  = rtrim($cfg['gallery_upload_dir'], '/');
$urlPrefix = rtrim($cfg['gallery_url_prefix'], '/');

if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
    flash_set('error', "Could not create upload directory: {$dir}");
}

/** MIME → extension we trust. PHP/.htaccess uploads are blocked entirely. */
const ALLOWED_IMAGE_MIMES = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];
const MAX_UPLOAD_BYTES = 10 * 1024 * 1024; // 10 MB

function safe_unlink_image(string $dir, string $filename): void {
    // Avoid path traversal.
    $base = basename($filename);
    if ($base !== $filename || $base === '' || $base[0] === '.') return;
    $full = $dir . '/' . $base;
    if (is_file($full)) @unlink($full);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $do = $_POST['do'] ?? '';

    if ($do === 'add_category') {
        $name        = trim((string)($_POST['name'] ?? ''));
        $slug        = trim((string)($_POST['slug'] ?? ''));
        $badgeClass  = trim((string)($_POST['badge_class'] ?? 'engineering'));
        $badgeText   = trim((string)($_POST['badge_text'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        if ($name === '' || strlen($name) > 128) {
            flash_set('error', 'Category name is required (max 128 chars).');
        } else {
            if ($slug === '') {
                $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $name));
                $slug = trim($slug, '-');
            }
            if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
                flash_set('error', 'Slug must be lowercase letters, numbers, and dashes only.');
            } else {
                $sortOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM gallery_categories')->fetchColumn();
                try {
                    $pdo->prepare('INSERT INTO gallery_categories (name, slug, badge_class, badge_text, description, sort_order) VALUES (?,?,?,?,?,?)')
                        ->execute([$name, $slug, $badgeClass, $badgeText, $description, $sortOrder]);
                    flash_set('success', 'Gallery category added.');
                } catch (PDOException $e) {
                    flash_set('error', 'Could not add category. The name or slug may already exist.');
                }
            }
        }
        redirect('gallery.php');
    }

    if ($do === 'delete_category') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Remove all images in this category from disk first.
            $imgs = $pdo->prepare('SELECT filename FROM gallery_images WHERE gallery_category_id = ?');
            $imgs->execute([$id]);
            foreach ($imgs->fetchAll() as $r) {
                safe_unlink_image($dir, $r['filename']);
            }
            $pdo->prepare('DELETE FROM gallery_images WHERE gallery_category_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM gallery_categories WHERE id = ?')->execute([$id]);
            flash_set('success', 'Gallery category removed.');
        }
        redirect('gallery.php');
    }

    if ($do === 'upload_photo') {
        $catId   = (int)($_POST['gallery_category_id'] ?? 0);
        $caption = trim((string)($_POST['caption'] ?? ''));
        if ($catId <= 0) {
            flash_set('error', 'Pick a category first.');
            redirect('gallery.php');
        }
        if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $code = $_FILES['photo']['error'] ?? -1;
            $msg  = match ($code) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is larger than the server allows.',
                UPLOAD_ERR_NO_FILE                         => 'Choose a photo to upload.',
                UPLOAD_ERR_PARTIAL                         => 'Upload was interrupted. Please try again.',
                default                                    => 'Upload failed (code ' . (int)$code . ').',
            };
            flash_set('error', $msg);
            redirect('gallery.php');
        }
        $tmp  = $_FILES['photo']['tmp_name'];
        $size = (int)$_FILES['photo']['size'];
        if ($size > MAX_UPLOAD_BYTES) {
            flash_set('error', 'Image must be 10 MB or smaller.');
            redirect('gallery.php');
        }
        // Inspect the actual bytes — never trust the client's content-type.
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($tmp) ?: '';
        if (!isset(ALLOWED_IMAGE_MIMES[$mime])) {
            flash_set('error', 'Only JPG, PNG, WebP, or GIF images are accepted.');
            redirect('gallery.php');
        }
        $ext  = ALLOWED_IMAGE_MIMES[$mime];
        // Random unique filename — never use the user-provided name.
        $name = bin2hex(random_bytes(12)) . '.' . $ext;
        $dest = $dir . '/' . $name;
        if (!@move_uploaded_file($tmp, $dest)) {
            flash_set('error', 'Could not save the uploaded file.');
            redirect('gallery.php');
        }
        @chmod($dest, 0644);

        $stmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM gallery_images WHERE gallery_category_id = ?');
        $stmt->execute([$catId]);
        $sortOrder = (int)$stmt->fetchColumn();

        $ins = $pdo->prepare('INSERT INTO gallery_images (gallery_category_id, filename, caption, sort_order) VALUES (?,?,?,?)');
        $ins->execute([$catId, $name, $caption, $sortOrder]);
        flash_set('success', 'Photo uploaded.');
        redirect('gallery.php');
    }

    if ($do === 'delete_photo') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('SELECT filename FROM gallery_images WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) {
                safe_unlink_image($dir, $row['filename']);
                $pdo->prepare('DELETE FROM gallery_images WHERE id = ?')->execute([$id]);
                flash_set('success', 'Photo removed.');
            }
        }
        redirect('gallery.php');
    }

    if ($do === 'reorder') {
        $kind = (string)($_POST['scope_id'] ?? '');
        $ids  = $_POST['order'] ?? [];
        if (!is_array($ids)) $ids = [];
        $clean = [];
        foreach ($ids as $id) {
            $n = (int)$id;
            if ($n > 0) $clean[] = $n;
        }
        $wantsJson = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== '')
            || str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');

        $ok = true;
        if ($clean) {
            try {
                if (str_starts_with($kind, 'cat:')) {
                    $catId = (int)substr($kind, 4);
                    // Only update images that actually belong to this category — guards against tampering.
                    $pdo->beginTransaction();
                    $u = $pdo->prepare('UPDATE gallery_images SET sort_order = ? WHERE id = ? AND gallery_category_id = ?');
                    foreach ($clean as $i => $imgId) {
                        $u->execute([$i, $imgId, $catId]);
                    }
                    $pdo->commit();
                } else {
                    $ok = false;
                }
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $ok = false;
            }
        }

        if ($wantsJson) {
            header('Content-Type: application/json');
            if (!$ok) http_response_code(400);
            echo json_encode(['ok' => $ok, 'count' => count($clean)]);
            exit;
        }
        flash_set($ok ? 'success' : 'error', $ok ? 'Order updated.' : 'Could not save the new order.');
        redirect('gallery.php');
    }

    if ($do === 'update_caption') {
        $id      = (int)($_POST['id'] ?? 0);
        $caption = trim((string)($_POST['caption'] ?? ''));
        if ($id > 0) {
            $pdo->prepare('UPDATE gallery_images SET caption = ? WHERE id = ?')->execute([$caption, $id]);
            flash_set('success', 'Caption updated.');
        }
        redirect('gallery.php');
    }
}

$cats = gallery_categories_all();
$badgeChoices = ['engineering','maintenance','finance','telecom','fire','hse','corrosion','management','high-value'];

?>
<h1>Gallery</h1>
<p>Manage the photos that appear on the public Gallery page. Photos are saved to <code>assets/images/gallery/</code>.</p>

<div class="admin-card">
  <h2 style="margin-top:0">Add a new gallery category</h2>
  <form method="post" class="admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="do" value="add_category">
    <div class="row">
      <div>
        <label for="cat-name">Heading</label>
        <input type="text" id="cat-name" name="name" maxlength="128" required placeholder="e.g. Firefighter Training">
      </div>
      <div>
        <label for="cat-slug">Slug (URL-safe, optional)</label>
        <input type="text" id="cat-slug" name="slug" maxlength="128" placeholder="auto from heading">
      </div>
    </div>
    <div class="row">
      <div>
        <label for="cat-badge-class">Badge color</label>
        <select id="cat-badge-class" name="badge_class">
<?php foreach ($badgeChoices as $b): ?>
          <option value="<?= e($b) ?>"><?= e($b) ?></option>
<?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="cat-badge-text">Badge text (optional)</label>
        <input type="text" id="cat-badge-text" name="badge_text" maxlength="128" placeholder="e.g. Fire Safety & Emergency">
      </div>
    </div>
    <div>
      <label for="cat-desc">Description (shown under heading)</label>
      <textarea id="cat-desc" name="description"></textarea>
    </div>
    <div class="actions">
      <button type="submit" class="btn-admin primary">Add category</button>
    </div>
  </form>
</div>

<?php if (!$cats): ?>
<div class="admin-card">No gallery categories yet. Add one above to get started.</div>
<?php else: foreach ($cats as $cat):
  $stmt = $pdo->prepare('SELECT * FROM gallery_images WHERE gallery_category_id = ? ORDER BY sort_order, id');
  $stmt->execute([$cat['id']]);
  $images = $stmt->fetchAll();
?>
<div class="admin-card">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap">
    <div>
      <h2 style="margin:0"><?= e($cat['name']) ?> <span class="pill"><?= e($cat['slug']) ?></span></h2>
<?php if (!empty($cat['badge_text'])): ?>
      <div style="margin-top:0.25rem;color:var(--admin-muted);font-size:0.875rem">Badge: <code><?= e($cat['badge_class']) ?></code> — “<?= e($cat['badge_text']) ?>”</div>
<?php endif; ?>
<?php if (!empty($cat['description'])): ?>
      <p style="margin:0.5rem 0 0;color:var(--admin-muted);font-size:0.875rem;max-width:60ch"><?= e($cat['description']) ?></p>
<?php endif; ?>
    </div>
    <form method="post" onsubmit="return confirm('Delete this category and ALL <?= count($images) ?> photo(s) inside it? This cannot be undone.')">
      <?= csrf_field() ?>
      <input type="hidden" name="do" value="delete_category">
      <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
      <button type="submit" class="btn-admin danger small">Delete category</button>
    </form>
  </div>

  <h3 style="margin:1.25rem 0 0.5rem;font-size:0.9375rem;color:var(--admin-muted);text-transform:uppercase;letter-spacing:0.5px">Photos (<?= count($images) ?>)</h3>
<?php if (!$images): ?>
  <p style="color:var(--admin-muted)">No photos yet — upload one below.</p>
<?php else: ?>
  <div class="gallery-grid reorder-horizontal" data-reorder data-reorder-url="gallery.php" data-reorder-item=".gallery-thumb[data-id]" data-reorder-scope="cat:<?= (int)$cat['id'] ?>">
<?php foreach ($images as $img): $src = '../' . $urlPrefix . '/' . rawurlencode($img['filename']); ?>
    <div class="gallery-thumb" data-id="<?= (int)$img['id'] ?>">
      <span class="drag-handle gallery-handle" role="button" aria-label="Drag to reorder" title="Drag to reorder">
        <svg viewBox="0 0 24 24" aria-hidden="true" width="16" height="16"><path fill="currentColor" d="M9 5h2v2H9V5Zm4 0h2v2h-2V5ZM9 11h2v2H9v-2Zm4 0h2v2h-2v-2ZM9 17h2v2H9v-2Zm4 0h2v2h-2v-2Z"/></svg>
      </span>
      <img src="<?= e($src) ?>" alt="<?= e($img['caption']) ?>" loading="lazy">
      <div class="meta">
        <div class="cap"><?= e($img['caption'] !== '' ? $img['caption'] : '(no caption)') ?></div>
        <details>
          <summary>Edit caption</summary>
          <form method="post" style="display:flex;gap:0.3rem">
            <?= csrf_field() ?>
            <input type="hidden" name="do" value="update_caption">
            <input type="hidden" name="id" value="<?= (int)$img['id'] ?>">
            <input type="text" name="caption" value="<?= e($img['caption']) ?>" maxlength="255" style="flex:1">
            <button type="submit" class="btn-admin small primary">Save</button>
          </form>
        </details>
        <div class="row-actions">
          <span data-sort-label style="font-size:0.7rem;color:var(--admin-subtle);text-transform:uppercase;letter-spacing:0.05em">#<?= (int)$img['sort_order'] + 1 ?></span>
          <form method="post" onsubmit="return confirm('Delete this photo?')">
            <?= csrf_field() ?>
            <input type="hidden" name="do" value="delete_photo">
            <input type="hidden" name="id" value="<?= (int)$img['id'] ?>">
            <button type="submit" class="btn-admin small danger">Delete</button>
          </form>
        </div>
      </div>
    </div>
<?php endforeach; ?>
  </div>
<?php endif; ?>

  <h3 style="margin:1.5rem 0 0.5rem;font-size:0.8125rem;color:var(--admin-muted);text-transform:uppercase;letter-spacing:0.06em;font-weight:600">Upload a new photo</h3>
  <form method="post" enctype="multipart/form-data" class="admin-form gallery-upload">
    <?= csrf_field() ?>
    <input type="hidden" name="do" value="upload_photo">
    <input type="hidden" name="gallery_category_id" value="<?= (int)$cat['id'] ?>">
    <div class="row">
      <div>
        <label>Photo (JPG / PNG / WebP / GIF, max 10 MB)</label>
        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif" required>
      </div>
      <div>
        <label>Caption</label>
        <input type="text" name="caption" maxlength="255" placeholder="e.g. Live-fire drill at PETKİM Center, İzmir">
      </div>
    </div>
    <div class="actions">
      <button type="submit" class="btn-admin primary">Upload photo</button>
    </div>
  </form>
</div>
<?php endforeach; endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
