<?php // index.php — Home
require_once __DIR__ . '/includes/db.php';
pushRecent('Home');
$db = getDB();
$me = currentUser();
if ($me) {
  $uid = $me['id'];
  $q = $db->prepare("SELECT COUNT(*) FROM items WHERE user_id=?"); $q->execute([$uid]);
  $total = (int)$q->fetchColumn();
  $q = $db->prepare("SELECT COUNT(*) FROM outfits WHERE user_id=?"); $q->execute([$uid]);
  $outfits = (int)$q->fetchColumn();
  $q = $db->prepare("SELECT * FROM items WHERE user_id=? ORDER BY id DESC LIMIT 4"); $q->execute([$uid]);
  $latest = $q->fetchAll(PDO::FETCH_ASSOC);
} else { $total = 0; $outfits = 0; $latest = []; }
function gthumb(array $it): string {
  $src = imageSrc($it['image_path'] ?? null, '');
  return $src !== '' ? e($src) : placeholderImg($it['type'], $it['type']);
}
include __DIR__ . '/includes/header.php';
?>
<script>
console.log('ClosetCraft loaded. Total items (PHP): <?= $total ?>');
// Prompt bar: guess the occasion from words, jump to the generator
document.getElementById('promptGo')?.addEventListener('click', function () {
  const q = (document.getElementById('promptInput').value || '').toLowerCase();
  const occs = ['formal','party','sports','ethnic','casual'];
  let found = 'casual';
  for (const o of occs) { if (q.includes(o)) { found = o; break; } }
  location.href = 'generator.php?occasion=' + found + '&go=1';
});
</script>

<section class="hero">
  <div>
    <span class="kicker"><i></i>Digital wardrobe planner</span>
    <h1>Your best fits, from <span class="hl">a few words</span></h1>
    <p class="lede">Catalogue your clothes, filter by type, color and occasion,
      shuffle a full head-to-toe outfit and pin favorites to your calendar.</p>
    <form class="prompt-bar" onsubmit="return false">
      <input id="promptInput" placeholder="Imagine your outfit… try ‘party’">
      <button class="prompt-go" id="promptGo" aria-label="Generate">&rarr;</button>
    </form>
    <div class="hero-cta">
      <?php if ($me): ?>
      <a href="upload.php" class="btn btn-dark">Add clothing</a>
      <a href="generator.php" class="btn btn-light">Shuffle a fit</a>
      <?php else: ?>
      <a href="signup.php" class="btn btn-dark">Sign up free</a>
      <a href="login.php" class="btn btn-light">Log in</a>
      <?php endif; ?>
    </div>
    <p id="closetCount" class="hero-meta"></p>
  </div>
  <div class="hero-visual">
    <svg class="spin-badge" viewBox="0 0 100 100" aria-hidden="true">
      <defs><path id="circ" d="M50,50 m-36,0 a36,36 0 1,1 72,0 a36,36 0 1,1 -72,0"/></defs>
      <text font-size="10.5" font-weight="700" fill="currentColor"><textPath href="#circ">shuffle your fit - shuffle your fit - </textPath></text>
    </svg>
    <div class="chat">
      <div class="bubble me">Give me an option for a party fit</div>
      <div class="bubble ai"><b>Your party fit is ready</b> — top, bottom, shoes + accessory matched.</div>
      <div class="fit-strip">
        <div class="fit-cell">Top<small>styled</small></div>
        <div class="fit-cell">Bottom<small>styled</small></div>
        <div class="fit-cell">Shoes<small>styled</small></div>
        <div class="fit-cell">+Extra<small>styled</small></div>
      </div>
    </div>
  </div>
</section>

<div class="logos" aria-hidden="true">
  <span>Casual</span><span>Formal</span><span>Party</span><span>Sports</span><span>Ethnic</span>
</div>

<section class="section">
  <h2>Everything you need to plan fits</h2>
  <p class="sub"><?= $total ?> items catalogued · <?= $outfits ?> outfits saved</p>
  <div class="grid-4">
    <a class="feat-card" href="closet.php"><span class="icon-tile p">C</span><span><b>Catalogue</b><br>Photos, colors, occasions.</span></a>
    <a class="feat-card" href="generator.php"><span class="icon-tile g">S</span><span><b>Shuffle</b><br>Fresh random fit every click.</span></a>
    <a class="feat-card" href="outfits.php"><span class="icon-tile y">F</span><span><b>Favorite</b><br>Save the ones you love.</span></a>
    <a class="feat-card" href="calendar.php"><span class="icon-tile t">P</span><span><b>Plan</b><br>Pin fits to any date.</span></a>
  </div>
</section>

<?php if ($me && $latest): ?>
<section class="section">
  <h2>Fresh from your closet</h2>
  <p class="sub">Latest uploads, ready to style.</p>
  <div class="gallery">
    <?php foreach ($latest as $it): ?>
      <div class="g-card"><img src="<?= gthumb($it) ?>" alt="<?= e($it['name']) ?>">
        <p><?= e($it['name']) ?> · <?= e($it['occasion']) ?></p></div>
    <?php endforeach; ?>
  </div>
  <div class="chips">
    <?php foreach (['casual','formal','party','sports','ethnic'] as $o): ?>
      <a class="chip" href="closet.php?occasion=<?= $o ?>"><?= ucfirst($o) ?></a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <h2>How it works</h2>
  <div class="grid-3">
    <div class="step-card"><span class="step-n">1</span><span><b>Add items</b><br>Photo, type, color, occasion.</span></div>
    <div class="step-card"><span class="step-n">2</span><span><b>Shuffle a fit</b><br>Occasion-aware, never the same twice.</span></div>
    <div class="step-card"><span class="step-n">3</span><span><b>Save to calendar</b><br>Name it, date it, done.</span></div>
  </div>
</section>

<section class="cta-band">
  <div><h2>Seize the closet, start now.</h2><p>Add your first 5 items in under a minute.</p></div>
  <a href="<?= $me ? 'upload.php' : 'signup.php' ?>" class="btn btn-dark">Get started</a>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
