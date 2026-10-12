<?php // upload.php — Upload clothing images + tag colors
require_once __DIR__ . '/includes/db.php';
pushRecent('Upload');
$db = getDB();
$edit = null;
if (isset($_GET['edit'])) { // query string + hidden form control demo
  $s = $db->prepare("SELECT * FROM items WHERE id=?"); $s->execute([(int)$_GET['edit']]);
  $edit = $s->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? '';
    $color = trim($_POST['color'] ?? '');
    $occasion = $_POST['occasion'] ?? '';
    $forGender = $_POST['for_gender'] ?? 'unisex';
    if (!preg_match('/^[A-Za-z0-9 ]{3,40}$/', $name)) throw new Exception("Name: 3-40 letters/numbers.");
    if (!preg_match('/^[A-Za-z#0-9 ]{3,20}$/', $color)) throw new Exception("Color invalid.");
    if (!in_array($type, ['top','bottom','dress','shoes','outerwear','accessory'], true)) throw new Exception("Bad type.");
    if (!in_array($occasion, ['casual','formal','party','sports','ethnic'], true)) throw new Exception("Bad occasion.");
    if (!in_array($forGender, ['male','female','unisex'], true)) throw new Exception("Bad gender choice.");

    $imgPath = $edit['image_path'] ?? '';
    if (!empty($_FILES['photo']['name'])) {
      if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) throw new Exception("Upload error.");
      $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, ALLOWED_IMG, true)) throw new Exception("Only jpg/png/webp allowed.");
      if ($_FILES['photo']['size'] > 2*1024*1024) throw new Exception("Max 2MB.");
      $fname = 'uploads/item_' . time() . '_' . rand(100,999) . '.' . $ext;
      if (!move_uploaded_file($_FILES['photo']['tmp_name'], BASE_PATH.'/'.$fname)) throw new Exception("Move failed.");
      $imgPath = $fname;
    }
    $now = date('Y-m-d H:i:s');
    if ($id !== '') { // UPDATE (CRUD)
      $stmt = $db->prepare("UPDATE items SET name=?,type=?,color=?,occasion=?,for_gender=?,image_path=? WHERE id=?");
      $stmt->execute([$name,$type,strtolower($color),$occasion,$forGender,$imgPath,(int)$id]);
      flash("Item updated.");
    } else { // CREATE (CRUD + prepared)
      $stmt = $db->prepare("INSERT INTO items(name,type,color,occasion,for_gender,image_path,created_at) VALUES(?,?,?,?,?,?,?)");
      $stmt->execute([$name,$type,strtolower($color),$occasion,$forGender,$imgPath,$now]);
      flash("Item added to closet.");
    }
    header("Location: closet.php"); exit;
  } catch (Exception $ex) { flash($ex->getMessage(), 'error'); }
}
include __DIR__ . '/includes/header.php';
?>
<h2 class="mt-3"><?= $edit ? 'Edit' : 'Add' ?> Clothing Item</h2>
<form id="uploadForm" method="post" enctype="multipart/form-data" class="card p-3">
  <input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? '')) ?>">
  <div class="mb-2"><label>Item name*</label>
    <input name="name" class="form-control" required minlength="3" maxlength="40" pattern="[A-Za-z0-9 ]{3,40}" value="<?= e($edit['name'] ?? '') ?>" placeholder="e.g. Blue Jeans"></div>
  <div class="row">
    <div class="col-md-3 mb-2"><label>Type*</label><select name="type" class="form-select" required>
      <?php foreach (['top','bottom','dress','shoes','outerwear','accessory'] as $t): ?>
      <option <?= ($edit['type']??'')===$t?'selected':'' ?> value="<?= $t ?>"><?= ucfirst($t) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3 mb-2"><label>Color tag* (text or picker)</label>
      <div class="d-flex gap-2"><input name="color" class="form-control" required value="<?= e($edit['color'] ?? '') ?>" placeholder="blue">
      <input type="color" class="form-control form-control-color" value="#3366ff" oninput="this.previousElementSibling.value=this.value"></div></div>
    <div class="col-md-3 mb-2"><label>Occasion*</label><select name="occasion" class="form-select" required>
      <?php foreach (['casual','formal','party','sports','ethnic'] as $o): ?>
      <option <?= ($edit['occasion']??'')===$o?'selected':'' ?> value="<?= $o ?>"><?= ucfirst($o) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3 mb-2"><label>Made for*</label><select name="for_gender" class="form-select" required>
      <?php foreach (['unisex','male','female'] as $g): ?>
      <option <?= ($edit['for_gender']??'unisex')===$g?'selected':'' ?> value="<?= $g ?>"><?= ucfirst($g) ?></option><?php endforeach; ?></select></div>
  </div>
  <div class="mb-2"><label>Photo (jpg/png/webp, max 2MB)</label>
    <input id="imageInput" type="file" name="photo" accept="image/*" class="form-control">
    <img id="imgPreview" class="mt-2 style-images" style="max-width:220px;height:auto" alt="preview"></div>
  <button class="btn btn-success"><?= $edit ? 'Update' : 'Save to Closet' ?></button>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
