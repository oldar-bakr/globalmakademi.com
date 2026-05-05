<?php
declare(strict_types=1);

$active_admin_nav = 'programs';
$admin_page_title = 'Programs — Makademi Admin';
require __DIR__ . '/_header.php';

$pdo = db();
$action = $_GET['action'] ?? 'list';

/** Look up an editable program record. */
function load_program(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare('SELECT * FROM programs WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// ---------- POST handlers ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $do = $_POST['do'] ?? '';

    if ($do === 'save') {
        $id          = (int)($_POST['id'] ?? 0);
        $title       = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $catId       = (int)($_POST['category_id'] ?? 0);
        $duration    = trim((string)($_POST['duration'] ?? ''));
        $location    = trim((string)($_POST['location'] ?? ''));
        $detailUrl   = trim((string)($_POST['detail_url'] ?? ''));
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);
        $published   = isset($_POST['is_published']) ? 1 : 0;

        $errs = [];
        if ($title === '' || strlen($title) > 255)        $errs[] = 'Title is required (max 255 chars).';
        if ($description === '')                          $errs[] = 'Description is required.';
        if ($catId <= 0)                                  $errs[] = 'Pick a category.';
        if (strlen($duration) > 64)                       $errs[] = 'Duration is too long.';
        if (strlen($location) > 128)                      $errs[] = 'Location is too long.';
        if ($detailUrl !== '' && strlen($detailUrl) > 255) $errs[] = 'Detail URL is too long.';

        // Validate category exists.
        $catStmt = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
        $catStmt->execute([$catId]);
        if (!$catStmt->fetch()) $errs[] = 'Invalid category selection.';

        if ($errs) {
            flash_set('error', implode(' ', $errs));
            redirect('programs.php?action=' . ($id ? 'edit&id=' . $id : 'new'));
        }

        if ($id > 0) {
            $u = $pdo->prepare('UPDATE programs SET title=?, description=?, category_id=?, duration=?, location=?, detail_url=?, sort_order=?, is_published=?, updated_at=CURRENT_TIMESTAMP WHERE id=?');
            $u->execute([$title, $description, $catId, $duration, $location, $detailUrl, $sortOrder, $published, $id]);
            flash_set('success', 'Program updated.');
        } else {
            // New program: append at end of sort order if unspecified.
            if ($sortOrder === 0) {
                $sortOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM programs')->fetchColumn();
            }
            $i = $pdo->prepare('INSERT INTO programs (title, description, category_id, duration, location, detail_url, sort_order, is_published) VALUES (?,?,?,?,?,?,?,?)');
            $i->execute([$title, $description, $catId, $duration, $location, $detailUrl, $sortOrder, $published]);
            flash_set('success', 'Program created.');
        }
        redirect('programs.php');
    }

    if ($do === 'reorder') {
        $ids = $_POST['order'] ?? [];
        if (!is_array($ids)) $ids = [];
        $clean = [];
        foreach ($ids as $id) {
            $n = (int)$id;
            if ($n > 0) $clean[] = $n;
        }
        $wantsJson = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== '')
            || str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
        if ($clean) {
            try {
                $pdo->beginTransaction();
                $u = $pdo->prepare('UPDATE programs SET sort_order = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
                foreach ($clean as $i => $pid) {
                    $u->execute([$i + 1, $pid]);
                }
                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                if ($wantsJson) {
                    http_response_code(500);
                    header('Content-Type: application/json');
                    echo json_encode(['ok' => false, 'error' => 'save_failed']);
                    exit;
                }
                flash_set('error', 'Could not save the new order.');
                redirect('programs.php');
            }
        }
        if ($wantsJson) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'count' => count($clean)]);
            exit;
        }
        flash_set('success', 'Order updated.');
        redirect('programs.php');
    }

    if ($do === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM programs WHERE id = ?')->execute([$id]);
            flash_set('success', 'Program deleted.');
        }
        redirect('programs.php');
    }
}

$categories = categories_all();

// ---------- New / Edit form ----------
if ($action === 'new' || $action === 'edit') {
    $editing = null;
    if ($action === 'edit') {
        $editing = load_program($pdo, (int)($_GET['id'] ?? 0));
        if (!$editing) { flash_set('error', 'Program not found.'); redirect('programs.php'); }
    }
    $f = $editing ?? ['id'=>0,'title'=>'','description'=>'','category_id'=>($categories[0]['id']??0),'duration'=>'','location'=>'','detail_url'=>'','sort_order'=>0,'is_published'=>1];
    ?>
    <h1><?= $editing ? 'Edit program' : 'Add new program' ?></h1>
    <div class="admin-card">
      <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="save">
        <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">

        <div>
          <label for="title">Title</label>
          <input type="text" id="title" name="title" maxlength="255" required value="<?= e($f['title']) ?>">
        </div>
        <div>
          <label for="description">Description</label>
          <textarea id="description" name="description" required><?= e($f['description']) ?></textarea>
        </div>
        <div class="row">
          <div>
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
<?php foreach ($categories as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= ((int)$c['id']===(int)$f['category_id'])?'selected':'' ?>><?= e($c['name']) ?></option>
<?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="duration">Duration (e.g. "2 Weeks")</label>
            <input type="text" id="duration" name="duration" maxlength="64" value="<?= e($f['duration']) ?>">
          </div>
        </div>
        <div class="row">
          <div>
            <label for="location">Location (e.g. "Istanbul")</label>
            <input type="text" id="location" name="location" maxlength="128" value="<?= e($f['location']) ?>">
          </div>
          <div>
            <label for="sort_order">Sort order (lower shows first; leave 0 to auto-append)</label>
            <input type="number" id="sort_order" name="sort_order" value="<?= (int)$f['sort_order'] ?>" min="0" max="9999">
          </div>
        </div>
        <div>
          <label for="detail_url">Detail page URL (optional, e.g. <code>courses/firefighting-joiff.html</code>)</label>
          <input type="text" id="detail_url" name="detail_url" maxlength="255" value="<?= e($f['detail_url']) ?>">
        </div>
        <div class="check">
          <input type="checkbox" id="is_published" name="is_published" value="1" <?= !empty($f['is_published'])?'checked':'' ?>>
          <label for="is_published" style="margin:0">Visible on public site</label>
        </div>
        <div class="actions">
          <button type="submit" class="btn-admin primary"><?= $editing ? 'Save changes' : 'Create program' ?></button>
          <a href="programs.php" class="btn-admin outline">Cancel</a>
        </div>
      </form>
    </div>
    <?php
    require __DIR__ . '/_footer.php';
    return;
}

// ---------- List view (with search + category filter) ----------
$search   = trim((string)($_GET['q'] ?? ''));
$catSlug  = trim((string)($_GET['category'] ?? ''));

$where = []; $params = [];
if ($search !== '') {
    $where[] = '(p.title LIKE ? OR p.description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($catSlug !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $catSlug;
}
$sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.badge_class
        FROM programs p JOIN categories c ON c.id = p.category_id';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY p.sort_order, p.id';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<h1>Programs <span style="color:var(--admin-muted);font-size:1rem;font-weight:400">(<?= count($rows) ?>)</span></h1>

<form method="get" class="admin-toolbar">
  <div class="grow">
    <label for="q">Search</label>
    <input type="text" id="q" name="q" placeholder="Title or description…" value="<?= e($search) ?>">
  </div>
  <div>
    <label for="category">Category</label>
    <select id="category" name="category">
      <option value="">All categories</option>
<?php foreach ($categories as $c): ?>
      <option value="<?= e($c['slug']) ?>" <?= $c['slug']===$catSlug?'selected':'' ?>><?= e($c['name']) ?></option>
<?php endforeach; ?>
    </select>
  </div>
  <div>
    <button type="submit" class="btn-admin primary">Filter</button>
    <a href="programs.php" class="btn-admin outline">Reset</a>
  </div>
  <div style="margin-left:auto">
    <a href="programs.php?action=new" class="btn-admin gold">+ Add new program</a>
  </div>
</form>

<?php
$canReorder = ($search === '' && $catSlug === '');
?>
<?php if (!$canReorder && $rows): ?>
<div class="admin-flash info" style="margin-bottom:0.75rem">Drag-to-reorder is disabled while a search or category filter is active. <a href="programs.php" style="color:inherit;text-decoration:underline">Clear the filter</a> to rearrange programs.</div>
<?php endif; ?>
<div class="admin-card" style="padding:0;overflow:auto">
  <table class="admin-table">
    <thead>
      <tr>
<?php if ($canReorder): ?>
        <th style="width:36px" aria-label="Drag to reorder"></th>
<?php endif; ?>
        <th style="width:60px">Sort</th>
        <th>Title</th>
        <th>Category</th>
        <th>Duration</th>
        <th>Location</th>
        <th>Status</th>
        <th class="col-actions">Actions</th>
      </tr>
    </thead>
    <tbody<?= $canReorder ? ' data-reorder data-reorder-url="programs.php" data-reorder-item="tr[data-id]"' : '' ?>>
<?php if (!$rows): ?>
      <tr><td colspan="<?= $canReorder ? 8 : 7 ?>" style="text-align:center;color:var(--admin-muted);padding:2rem">No programs match.</td></tr>
<?php else: foreach ($rows as $r): ?>
      <tr<?= $canReorder ? ' data-id="' . (int)$r['id'] . '"' : '' ?>>
<?php if ($canReorder): ?>
        <td class="col-handle"><span class="drag-handle" role="button" aria-label="Drag to reorder" title="Drag to reorder"><svg viewBox="0 0 24 24" aria-hidden="true" width="16" height="16"><path fill="currentColor" d="M9 5h2v2H9V5Zm4 0h2v2h-2V5ZM9 11h2v2H9v-2Zm4 0h2v2h-2v-2ZM9 17h2v2H9v-2Zm4 0h2v2h-2v-2Z"/></svg></span></td>
<?php endif; ?>
        <td<?= $canReorder ? ' data-sort-label' : '' ?>><?= (int)$r['sort_order'] ?></td>
        <td>
          <strong><?= e($r['title']) ?></strong>
          <div style="color:var(--admin-muted);font-size:0.8125rem;margin-top:0.125rem"><?= e(mb_strimwidth($r['description'], 0, 110, '…')) ?></div>
        </td>
        <td><span class="pill"><?= e($r['category_name']) ?></span></td>
        <td><?= e($r['duration']) ?></td>
        <td><?= e($r['location']) ?></td>
        <td>
<?php if ((int)$r['is_published'] === 1): ?>
          <span class="pill on">Published</span>
<?php else: ?>
          <span class="pill off">Hidden</span>
<?php endif; ?>
        </td>
        <td class="col-actions">
          <a class="btn-admin small outline" href="programs.php?action=edit&id=<?= (int)$r['id'] ?>">Edit</a>
          <form method="post" onsubmit="return confirm('Delete this program? This cannot be undone.')" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="do" value="delete">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button type="submit" class="btn-admin small danger">Delete</button>
          </form>
        </td>
      </tr>
<?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
