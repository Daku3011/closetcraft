<?php // calendar.php — Outfit calendar
require_once __DIR__ . '/includes/db.php';
requireLogin();
$uid = currentUser()['id'];
pushRecent('Calendar');
$db = getDB();
$month = $_GET['m'] ?? date('Y-m'); // query-string state: ?m=2026-10
if (!preg_match('/^\d{4}-\d{2}$/', $month)) $month = date('Y-m');
$start = $month . '-01';
$days = (int)date('t', strtotime($start));
$firstDow = (int)date('w', strtotime($start)); // 0=Sun
$stmt = $db->prepare("SELECT * FROM outfits WHERE user_id=? AND substr(fav_date,1,7)=? ORDER BY fav_date");
$stmt->execute([$uid, $month]);
$byDay = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $o) { $byDay[substr($o['fav_date'],8,2)][] = $o; }
$prev = date('Y-m', strtotime($start.' -1 month')); $next = date('Y-m', strtotime($start.' +1 month'));
include __DIR__ . '/includes/header.php';
?>
<h2 class="mt-3">Outfit Calendar — <?= e(date('F Y', strtotime($start))) ?></h2>
<a href="?m=<?= $prev ?>" class="btn btn-sm btn-outline-primary">Prev</a>
<a href="?m=<?= date('Y-m') ?>" class="btn btn-sm btn-outline-secondary">Today</a>
<a href="?m=<?= $next ?>" class="btn btn-sm btn-outline-primary">Next</a>
<div class="cal-grid mt-3">
  <?php for ($i=0;$i<$firstDow;$i++) echo "<div></div>";
  for ($d=1;$d<=$days;$d++):
    $key = sprintf('%02d',$d); $isToday = ($month.'-'.$key)===date('Y-m-d'); ?>
    <div class="cal-day <?= $isToday?'today':'' ?>"><b><?= $d ?></b>
      <?php foreach ($byDay[$key] ?? [] as $o): ?>
        <span class="outfit-tag"><?= e($o['name']) ?> (<?= e($o['occasion']) ?>)</span>
      <?php endforeach; ?>
    </div>
  <?php endfor; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
