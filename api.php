<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
$action = $_GET['action'] ?? 'list';
$db = getDB();

// All candidates of a type, shuffled so every call differs
function candidates(PDO $db, string $type): array {
  $s = $db->prepare("SELECT * FROM items WHERE type=?");
  $s->execute([$type]);
  $all = $s->fetchAll(PDO::FETCH_ASSOC);
  shuffle($all);
  return $all;
}
// Prefer the requested occasion, fall back to anything — never empty if type exists.
// $style 'male' skips female-tagged items, 'female' skips male-tagged ones.
function pick(PDO $db, string $type, string $occ, string $style = 'unisex'): ?array {
  $all = candidates($db, $type);
  if ($style === 'male') $all = array_values(array_filter($all, fn($i) => ($i['for_gender'] ?? 'unisex') !== 'female'));
  if ($style === 'female') $all = array_values(array_filter($all, fn($i) => ($i['for_gender'] ?? 'unisex') !== 'male'));
  foreach ($all as $it) if ($it['occasion'] === $occ) return $it;
  return $all[0] ?? null;
}
try {
  if ($action === 'list') {
    $items = $db->query("SELECT * FROM items ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status'=>'ok','count'=>count($items),'items'=>$items]); exit;
  }
  if ($action === 'random') {
    $occ = $_GET['occasion'] ?? 'casual';
    $style = in_array($_GET['style'] ?? 'unisex', ['male','female','unisex'], true) ? $_GET['style'] : 'unisex';
    $top = pick($db,'top',$occ,$style); $bottom = pick($db,'bottom',$occ,$style);
    $dress = pick($db,'dress',$occ,$style); $shoes = pick($db,'shoes',$occ,$style);
    $outer = pick($db,'outerwear',$occ,$style); $acc = pick($db,'accessory',$occ,$style);
    // Sensible rule: a fit is EITHER (top + bottom) OR a dress — never both.
    // Male style never gets a dress, so party looks always include a bottom.
    $useDress = $style !== 'male' && $dress && ($dress['occasion'] === $occ || (!$top && !$bottom));
    if ($useDress) { $top = null; $bottom = null; } else { $dress = null; }
    $outfit = ['name'=>ucfirst($occ).' Look '.date('md-Hi'),'occasion'=>$occ,'style'=>$style,
      'top_id'=>$top['id']??null,'bottom_id'=>$bottom['id']??null,'dress_id'=>$useDress?($dress['id']??null):null,
      'shoes_id'=>$shoes['id']??null,'outerwear_id'=>$outer['id']??null,'accessory_id'=>$acc['id']??null];
    $chosen = array_values(array_filter([$top,$bottom,$useDress?$dress:null,$shoes,$outer,$acc]));
    echo json_encode(['status'=>'ok','message'=>"Random $occ outfit generated!",
      'outfit'=>$outfit,'items'=>$chosen]); exit;
  }
  if ($action === 'stats') {
    // Simulates MySQL stored-procedure CountItemsByType() result
    $rows = $db->query("SELECT type, COUNT(*) c FROM items GROUP BY type")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status'=>'ok','stats'=>$rows]); exit;
  }
  echo json_encode(['status'=>'error','message'=>'Unknown action']);
} catch (Exception $ex) { http_response_code(500); echo json_encode(['status'=>'error','message'=>$ex->getMessage()]); }
