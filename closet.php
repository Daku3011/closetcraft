<?php // closet.php — Catalogue + Filter
require_once __DIR__ . '/includes/db.php';
pushRecent('Closet');
$type = $_GET['type'] ?? '';
$color = $_GET['color'] ?? '';
$occasion = $_GET['occasion'] ?? '';
$search = $_GET['search'] ?? '';
$for = $_GET['for'] ?? '';
setcookie('cc_last_filter', json_encode(compact('type','color','occasion')), time()+86400*7, '/');

$db = getDB();
$sql = "SELECT * FROM items WHERE 1=1"; $params = [];
if ($type !== '') { $sql .= " AND type = ?"; $params[] = $type; }
if ($occasion !== '') { $sql .= " AND occasion = ?"; $params[] = $occasion; }
if ($for !== '') { $sql .= " AND for_gender = ?"; $params[] = $for; }
if ($color !== '') { $sql .= " AND color LIKE ?"; $params[] = "%$color%"; }
if ($search !== '') { $sql .= " AND name LIKE ?"; $params[] = "%$search%"; }
$sql .= " ORDER BY id DESC";
$stmt = $db->prepare($sql); $stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Delete via query string ?delete=id (CRUD)
if (isset($_GET['delete'])) {
  try {
    $del = $db->prepare("DELETE FROM items WHERE id = ?");
    $del->execute([(int)$_GET['delete']]);
    flash("Item deleted.");
    header("Location: closet.php"); exit;
  } catch (Exception $ex) { flash("Delete failed: ".$ex->getMessage(), 'error'); }
}
include __DIR__ . '/includes/header.php';
?>
<div class="page-head">
  <div>
    <h2>My Closet</h2>
    <p class="page-sub">Filter, search and manage everything you own.</p>
  </div>
  <span class="count-pill"><?= count($items) ?> items</span>
</div>
<form method="get" class="row g-2 mb-3">
  <div class="col-md-3"><select name="type" class="form-select">
    <option value="">All types</option>
    <?php foreach (['top','bottom','dress','shoes','outerwear','accessory'] as $t): ?>
      <option <?= $type===$t?'selected':'' ?> value="<?= $t ?>"><?= ucfirst($t) ?></option>
    <?php endforeach; ?></select></div>
  <div class="col-md-2"><select name="occasion" class="form-select">
    <option value="">All occasions</option>
    <?php foreach (['casual','formal','party','sports','ethnic'] as $o): ?>
      <option <?= $occasion===$o?'selected':'' ?> value="<?= $o ?>"><?= ucfirst($o) ?></option>
    <?php endforeach; ?></select></div>
  <div class="col-md-2"><select name="for" class="form-select">
    <option value="">All genders</option>
    <?php foreach (['male','female','unisex'] as $g): ?>
      <option <?= $for===$g?'selected':'' ?> value="<?= $g ?>"><?= ucfirst($g) ?></option>
    <?php endforeach; ?></select></div>
  <div class="col-md-2"><input name="color" class="form-control" placeholder="Color" value="<?= e($color) ?>" pattern="[A-Za-z#0-9 ]{0,20}"></div>
  <div class="col-md-2"><input name="search" id="liveSearch" class="form-control" placeholder="Search…" value="<?= e($search) ?>"></div>
  <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>

<div class="row">
<?php foreach ($items as $it): ?>
  <div class="col-md-3 item-card">
    <?php if ($it['image_path'] && file_exists(__DIR__.'/'.$it['image_path'])): ?>
      <img src="<?= e($it['image_path']) ?>" alt="<?= e($it['name']) ?>">
    <?php else: ?>
      <img src="<?= placeholderImg($it['type'], $it['type']) ?>" alt="no photo">
    <?php endif; ?>
    <h5 class="mt-2"><?= e($it['name']) ?></h5>
    <p><span class="badge bg-secondary"><?= e($it['type']) ?></span>
       <span class="badge bg-info"><?= e($it['occasion']) ?></span>
       <span class="badge bg-secondary"><?= e($it['for_gender'] ?? 'unisex') ?></span>
       <span class="color-dot" style="background:<?= e($it['color']) ?>"></span> <?= e($it['color']) ?></p>
    <small class="text-muted"><?= e($it['created_at']) ?></small><br>
    <a href="closet.php?delete=<?= $it['id'] ?>&type=<?= e($type) ?>" onclick="return confirm('Delete?')" class="btn btn-sm btn-danger">Delete</a>
    <a href="upload.php?edit=<?= $it['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
  </div>
<?php endforeach; ?>
</div>

<h3 class="mt-4">All items</h3>
<table class="styled-table">
  <tr><th>ID</th><th>Name</th><th>Type</th><th>Color</th><th>Occasion</th></tr>
  <?php foreach ($items as $it): ?><tr><td><?= $it['id'] ?></td><td><?= e($it['name']) ?></td><td><?= e($it['type']) ?></td><td><?= e($it['color']) ?></td><td><?= e($it['occasion']) ?></td></tr><?php endforeach; ?>
</table>
<?php include __DIR__ . '/includes/footer.php'; ?>
