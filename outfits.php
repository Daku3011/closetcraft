<?php
require_once __DIR__ . '/includes/db.php';
pushRecent('Outfits');
$db = getDB();
if (isset($_GET['delete'])) {
  $db->prepare("DELETE FROM outfits WHERE id=?")->execute([(int)$_GET['delete']]);
  flash("Outfit deleted."); header("Location: outfits.php"); exit;
}
function itemOf(PDO $db, $id): ?array {
  if (!$id) return null;
  $s = $db->prepare("SELECT * FROM items WHERE id=?"); $s->execute([(int)$id]);
  return $s->fetch(PDO::FETCH_ASSOC) ?: null;
}
function thumb(?array $it, string $label): string {
  if ($it && !empty($it['image_path']) && file_exists(__DIR__.'/'.$it['image_path']))
    return e($it['image_path']);
  return placeholderImg($it['type'] ?? $label, $it['type'] ?? $label);
}
$rows = $db->query("SELECT * FROM outfits ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$view = null;
if (isset($_GET['view'])) {
  $s = $db->prepare("SELECT * FROM outfits WHERE id=?"); $s->execute([(int)$_GET['view']]);
  $view = $s->fetch(PDO::FETCH_ASSOC);
}
include __DIR__ . '/includes/header.php';
?>

<?php if ($view):
  $parts = ['Top'=>itemOf($db,$view['top_id']),'Bottom'=>itemOf($db,$view['bottom_id']),'Dress'=>itemOf($db,$view['dress_id'] ?? null),'Shoes'=>itemOf($db,$view['shoes_id']),'Outerwear'=>itemOf($db,$view['outerwear_id'] ?? null),'Accessory'=>itemOf($db,$view['accessory_id'])];
?>
  <a href="outfits.php" class="btn btn-sm btn-outline-secondary mt-3">Back to all outfits</a>
  <h2 class="mt-2">Your Fit — <?= e($view['name']) ?> <small>(<?= e($view['occasion']) ?> · <?= e($view['style'] ?? 'unisex') ?><?=
    $view['fav_date'] ? ', '.e($view['fav_date']) : '' ?>)</small></h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px">
    <?php foreach ($parts as $label=>$it): ?>
      <?php // Hide the excluded core piece: dress XOR (top+bottom)
            if ($it === null && ($label === 'Dress' || (($label === 'Top' || $label === 'Bottom') && $parts['Dress']))) continue; ?>
      <div class="item-card" style="display:block">
        <img src="<?= thumb($it,$label) ?>" alt="<?= e($label) ?>">
        <h5><?= e($label) ?></h5>
        <p><?= $it ? e($it['name']).' <small>('.e($it['color']).')</small>' : '— none —' ?></p>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
<h2 class="mt-3">Saved Outfits (<?= count($rows) ?>)</h2>
<?php if (!$rows): ?><p>No fits saved yet. Go to <a href="generator.php">Generator</a>, click Generate, then Save.</p><?php endif; ?>
<div class="row">
<?php foreach ($rows as $r):
  $first = itemOf($db, $r['top_id'] ?? $r['dress_id'] ?? $r['bottom_id']);
?>
  <div class="item-card" style="display:block">
    <img src="<?= thumb($first,'fit') ?>" alt="fit">
    <h5><?= e($r['name']) ?></h5>
    <p><span class="badge bg-info"><?= e($r['occasion']) ?></span> <span class="badge bg-secondary"><?= e($r['style'] ?? 'unisex') ?></span> <?= e($r['fav_date'] ?? '') ?></p>
    <a href="outfits.php?view=<?= $r['id'] ?>" class="btn btn-sm btn-primary">See the fit</a>
    <a href="outfits.php?delete=<?= $r['id'] ?>" onclick="return confirm('Delete?')" class="btn btn-sm btn-danger">Delete</a>
  </div>
<?php endforeach; ?>
</div>
<table class="styled-table mt-3">
  <tr><th>ID</th><th>Name</th><th>Occasion</th><th>Date</th><th>Action</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr><td><?= $r['id'] ?></td><td><a href="outfits.php?view=<?= $r['id'] ?>"><?= e($r['name']) ?></a></td>
      <td><?= e($r['occasion']) ?></td><td><?= e($r['fav_date'] ?? '—') ?></td>
      <td><a href="outfits.php?view=<?= $r['id'] ?>" class="btn btn-sm btn-primary">View</a></td></tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
<a href="calendar.php" class="btn btn-primary mt-3">View Calendar</a>
<?php include __DIR__ . '/includes/footer.php'; ?>
