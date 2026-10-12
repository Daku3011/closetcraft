<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Kolkata');

define('APP_NAME', 'ClosetCraft');
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_DIR', BASE_PATH . '/uploads/');
define('DB_FILE', BASE_PATH . '/data/wardrobe.db');
define('ALLOWED_IMG', ['jpg', 'jpeg', 'png', 'webp']);

// .env loader (local dev convenience): KEY=value lines, # comments.
// Real environment variables always win — .env never overrides them.
// Get the template from .env.example; the real .env is never committed.
$__envFile = BASE_PATH . '/.env';
if (is_file($__envFile)) {
    foreach (file($__envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $__line) {
        $__line = trim($__line);
        if ($__line === '' || $__line[0] === '#') continue;
        $__parts = explode('=', $__line, 2);
        if (count($__parts) !== 2) continue;
        $__k = trim($__parts[0]);
        $__v = trim(trim($__parts[1]), "\"'");
        if ($__k !== '' && getenv($__k) === false) putenv($__k . '=' . $__v);
    }
    unset($__line, $__parts, $__k, $__v);
}
unset($__envFile);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_COOKIE['cc_theme'])) {
    setcookie('cc_theme', 'light', time() + (30 * 24 * 60 * 60), '/');
}

// Auth helpers
function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}
function requireLogin(): void {
    if (!currentUser()) { header('Location: login.php'); exit; }
}
function flash(string $msg, string $type = 'success'): void {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}
function getFlash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
function e(string $s): string {
    return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8');
}
// Offline-safe placeholder image (data-URI SVG): renders in light AND dark theme,
// no external network needed. Gradient pair per clothing type.
function placeholderImg(?string $type, ?string $label = null): string {
    $t = strtolower(trim($type ?? 'item')) ?: 'item';
    $pairs = [
        'top' => ['#f3c6d3', '#df9dbe'], 'bottom' => ['#bcd3f5', '#8fb0ea'],
        'dress' => ['#d9c2f0', '#b394e0'], 'shoes' => ['#f6d9b8', '#ecb877'],
        'outerwear' => ['#bfe3d8', '#87c9b6'], 'accessory' => ['#f5e6a8', '#e9cc72'],
    ];
    [$a, $b] = $pairs[$t] ?? ['#ddd6f0', '#b8aede'];
    $letter = strtoupper(substr($t, 0, 1));
    $word = $label ?? $t;
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='400' height='300'>"
        . "<defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'>"
        . "<stop offset='0' stop-color='$a'/><stop offset='1' stop-color='$b'/></linearGradient></defs>"
        . "<rect width='400' height='300' fill='url(#g)'/>"
        . "<text x='200' y='168' font-size='100' text-anchor='middle' fill='rgba(255,255,255,.92)' font-family='Arial' font-weight='bold'>$letter</text>"
        . "<text x='200' y='218' font-size='26' text-anchor='middle' fill='rgba(255,255,255,.9)' font-family='Arial'>$word</text></svg>";
    return 'data:image/svg+xml,' . rawurlencode($svg);
}
// Track recent views using session (state management demo)
function pushRecent(string $label): void {
    $_SESSION['recent'] = $_SESSION['recent'] ?? [];
    array_unshift($_SESSION['recent'], $label . ' @ ' . date('d-m-Y H:i'));
    $_SESSION['recent'] = array_slice($_SESSION['recent'], 0, 5);
}

// Photo storage helpers (local folder or Supabase — see includes/storage.php)
require_once __DIR__ . '/storage.php';
