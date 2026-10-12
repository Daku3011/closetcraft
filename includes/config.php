<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Kolkata');

define('APP_NAME', 'ClosetCraft');
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_DIR', BASE_PATH . '/uploads/');
define('DB_FILE', BASE_PATH . '/data/wardrobe.db');
define('ALLOWED_IMG', ['jpg', 'jpeg', 'png', 'webp']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_COOKIE['cc_theme'])) {
    setcookie('cc_theme', 'light', time() + (30 * 24 * 60 * 60), '/');
}

// Flash message helpers (Session variables)
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
