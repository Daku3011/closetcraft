<?php
if (!defined('APP_NAME')) require_once __DIR__ . '/config.php';
$theme = $_COOKIE['cc_theme'] ?? 'light';
$active = basename($_SERVER['PHP_SELF'] ?? 'index.php');
function navCls(string $page, string $active): string { return $active === $page ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e($theme) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ClosetCraft - Digital Wardrobe Planner. Catalogue clothes, filter by type color occasion, random outfit generator, calendar.">
  <meta name="keywords" content="wardrobe, outfit, planner, PHP, HTML, CSS, JavaScript">
  <meta name="author" content="ClosetCraft Student Project">
  <title><?= e(APP_NAME) ?> - Digital Wardrobe Planner</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect x='8' y='8' width='84' height='84' rx='22' fill='%23d76a9b' stroke='black' stroke-width='6'/><text x='50' y='68' font-size='52' text-anchor='middle' fill='white' font-family='Arial' font-weight='bold'>C</text></svg>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="assets/css/style.css?v=8">
</head>
<body>
<div class="page-frame"><div class="canvas">
<header class="site-header">
  <nav class="navbar custom-nav">
    <div class="container">
      <a class="navbar-brand brand-logo" href="index.php">closetcraft</a>
      <div id="navMenu">
        <ul class="nav-list">
          <li><a class="nav-link <?= navCls('closet.php',$active) ?>" href="closet.php">Closet</a></li>
          <li><a class="nav-link <?= navCls('generator.php',$active) ?>" href="generator.php">Generator</a></li>
          <li><a class="nav-link <?= navCls('outfits.php',$active) ?>" href="outfits.php">Outfits</a></li>
          <li><a class="nav-link <?= navCls('calendar.php',$active) ?>" href="calendar.php">Calendar</a></li>
        </ul>
        <a href="upload.php" class="btn btn-sm btn-primary">Get started</a>
        <button id="themeBtn" class="btn btn-sm" aria-label="Toggle dark and light theme"><?= $theme==='dark' ? 'Light' : 'Dark' ?></button>
      </div>
    </div>
  </nav>
</header>
<main class="main-wrap">
<?php $f = getFlash(); if ($f): ?>
  <div class="alert alert-<?= $f['type']==='error'?'danger':'success' ?> mt-3"><?= e($f['msg']) ?></div>
<?php endif; ?>
