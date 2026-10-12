<?php // generator.php — Random outfit generator
require_once __DIR__ . '/includes/db.php';
pushRecent('Generator');
$db = getDB();

function candidates(PDO $db, string $type): array {
  $s = $db->prepare("SELECT * FROM items WHERE type=?");
  $s->execute([$type]);
  $all = $s->fetchAll(PDO::FETCH_ASSOC);
  shuffle($all); // every page load / click gives a fresh order
  return $all;
}
function pick(PDO $db, string $type, string $occ, string $style = 'unisex'): ?array {
  $all = candidates($db, $type);
  if ($style === 'male') $all = array_values(array_filter($all, fn($i) => ($i['for_gender'] ?? 'unisex') !== 'female'));
  if ($style === 'female') $all = array_values(array_filter($all, fn($i) => ($i['for_gender'] ?? 'unisex') !== 'male'));
  foreach ($all as $it) if ($it['occasion'] === $occ) return $it; // occasion first
  return $all[0] ?? null; // ...then anything, so a fit always assembles
}
function buildFit(PDO $db, string $occ, string $style = 'unisex'): array {
  $fit = ['top'=>pick($db,'top',$occ,$style),'bottom'=>pick($db,'bottom',$occ,$style),
          'dress'=>pick($db,'dress',$occ,$style),'shoes'=>pick($db,'shoes',$occ,$style),
          'outerwear'=>pick($db,'outerwear',$occ,$style),'accessory'=>pick($db,'accessory',$occ,$style)];
  // Sensible rule: a fit is EITHER (top + bottom) OR a dress — never both.
  // Male style never gets a dress, so party looks always include a bottom.
  if ($style !== 'male' && $fit['dress'] && ($fit['dress']['occasion'] === $occ || (!$fit['top'] && !$fit['bottom']))) {
    $fit['top'] = null; $fit['bottom'] = null; // style around the dress
  } else { $fit['dress'] = null; }
  return $fit;
}
function thumbSrc(?array $it, string $label): string {
  if ($it && !empty($it['image_path']) && file_exists(__DIR__.'/'.$it['image_path'])) return e($it['image_path']);
  return placeholderImg($it['type'] ?? $label, $it['type'] ?? $label);
}
// Classic form POST save (non-AJAX fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_outfit'])) {
  try {
    $name = trim($_POST['outfit_name'] ?? 'My Outfit');
    if (!preg_match('/^[A-Za-z0-9 ]{3,40}$/', $name)) throw new Exception("Outfit name invalid.");
    $stmt = $db->prepare("INSERT INTO outfits(name,top_id,bottom_id,dress_id,shoes_id,outerwear_id,accessory_id,occasion,style,fav_date,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
    $int = fn($k) => ($_POST[$k] ?? '') !== '' ? (int)$_POST[$k] : null;
    $style = in_array($_POST['style'] ?? 'unisex', ['male','female','unisex'], true) ? $_POST['style'] : 'unisex';
    $stmt->execute([$name,$int('top_id'),$int('bottom_id'),$int('dress_id'),$int('shoes_id'),$int('outerwear_id'),$int('accessory_id'),$_POST['occasion'],$style,$_POST['fav_date']?:null,date('Y-m-d H:i:s')]);
    flash("Outfit '$name' saved."); header("Location: outfits.php"); exit;
  } catch (Exception $ex) { flash($ex->getMessage(), 'error'); }
}
include __DIR__ . '/includes/header.php';
?>
<h2 class="mt-3">Outfit Generator</h2>
<p class="sub">Shuffled every click — top, bottom or dress, shoes, outerwear, accessory.</p>
<div class="card p-3 mb-3">
  <!-- One form drives BOTH modes, so occasion + style always apply -->
  <form method="get" id="genForm">
    <div class="d-flex gap-3 flex-wrap">
      <div><label>Occasion</label>
      <select id="genOccasion" name="occasion" class="form-select" style="max-width:220px">
      <?php $sel = $_GET['occasion'] ?? 'casual'; foreach (['casual','formal','party','sports','ethnic'] as $o): ?>
        <option <?= $sel===$o?'selected':'' ?>><?= $o ?></option>
      <?php endforeach; ?>
      </select></div>
      <div><label>Style for</label>
      <select id="genStyle" name="style" class="form-select" style="max-width:220px">
      <?php $st = $_GET['style'] ?? 'unisex'; foreach (['unisex','male','female'] as $g): ?>
        <option <?= $st===$g?'selected':'' ?>><?= $g ?></option>
      <?php endforeach; ?>
      </select></div>
    </div>
    <div class="mt-2 d-flex gap-2 flex-wrap">
      <button id="ajaxGenerate" type="button" class="btn btn-primary">Shuffle it</button>
      <button type="submit" name="go" value="1" class="btn btn-light">PHP reload version</button>
    </div>
  </form>
  <div id="ajaxResult" class="mt-3"></div>
</div>

<?php
// Server-side random (decision + looping example)
if (isset($_GET['go'])) {
  $occ = $_GET['occasion'] ?? 'casual';
  $style = in_array($_GET['style'] ?? 'unisex', ['male','female','unisex'], true) ? $_GET['style'] : 'unisex';
  $combo = buildFit($db, $occ, $style);
  // Don't show the excluded core piece (dress XOR top+bottom); other empty slots are informative
  $combo = array_filter($combo, fn($it, $k) => $it !== null || !in_array($k, ['top','bottom','dress'], true), ARRAY_FILTER_USE_BOTH);
  echo "<div class='card p-3'><h4>Your $occ fit for $style — full day, head to toe</h4>";
  echo "<div style='display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-top:8px'>";
  foreach ($combo as $k=>$it) {
    if ($it === null && !in_array($k, ['dress'], true) && false) continue;
    echo "<div class='item-card' style='display:block;margin:0'><img src='".thumbSrc($it,$k)."' alt='$k'><br><b>".e($k)."</b><br>".($it?e($it['name'])." <small>(".e($it['color']).", ".e($it['occasion']).")</small>":'— closet has none —')."</div>";
  }
  echo "</div></div>";
  $_POST_FILL = ['top_id'=>$combo['top']['id']??'','bottom_id'=>$combo['bottom']['id']??'','dress_id'=>$combo['dress']['id']??'','shoes_id'=>$combo['shoes']['id']??'','outerwear_id'=>$combo['outerwear']['id']??'','accessory_id'=>$combo['accessory']['id']??''];
}
?>
<form method="post" class="card p-3 mt-3">
  <h4>Save this fit to your calendar</h4>
  <?php foreach (['top_id','bottom_id','dress_id','shoes_id','outerwear_id','accessory_id'] as $k): ?>
    <input type="hidden" name="<?= $k ?>" id="hid_<?= $k ?>" value="<?= e((string)($_POST_FILL[$k] ?? '')) ?>">
  <?php endforeach; ?>
  <input type="hidden" name="style" id="hid_style" value="<?= e($_GET['style'] ?? 'unisex') ?>">
  <div class="row">
    <div class="col-md-4"><label>Outfit name*</label><input id="outfitName" name="outfit_name" class="form-control" required pattern="[A-Za-z0-9 ]{3,40}"></div>
    <div class="col-md-4"><label>Occasion</label><select name="occasion" class="form-select"><?php foreach (['casual','formal','party','sports','ethnic'] as $o): ?><option><?= $o ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label>Calendar date</label><input type="date" name="fav_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
  </div>
  <button name="save_outfit" class="btn btn-success mt-2">Save Outfit</button>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
