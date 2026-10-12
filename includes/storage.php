<?php
// includes/storage.php — photo storage that survives restarts.
// Without env config: plain local uploads/ folder (Mac / local dev).
// With SUPABASE_URL + SUPABASE_KEY set: Supabase Storage bucket (Render/live).
// Bucket must exist and be PUBLIC (Storage -> New bucket -> Public ON).
declare(strict_types=1);

function storageEnabled(): bool {
    return (bool)getenv('SUPABASE_URL') && (bool)getenv('SUPABASE_KEY');
}
function storageBucket(): string {
    $b = getenv('SUPABASE_BUCKET');
    return $b ? (string)$b : 'closet-images';
}
// Public URL for a stored path
function storagePublicUrl(string $path): string {
    return rtrim((string)getenv('SUPABASE_URL'), '/') . '/storage/v1/object/public/' . storageBucket() . '/' . ltrim($path, '/');
}
// True when image_path is usable as-is (remote URL or existing local file)
function imageSrc(?string $path, string $fallback): string {
    if ($path && str_starts_with($path, 'http')) return $path;
    if ($path && file_exists(BASE_PATH . '/' . $path)) return $path;
    return $fallback;
}
// Minimal HTTP helper: cURL when available, streams fallback otherwise.
// Returns [httpCode, body|false].
function storageHttp(string $method, string $url, array $headers, ?string $body = null): array {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 25,
        ]);
        if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $res = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$code, $res];
    }
    $ctx = stream_context_create(['http' => [
        'method' => $method, 'header' => implode("\r\n", $headers) . "\r\n",
        'content' => $body, 'ignore_errors' => true, 'timeout' => 25,
    ]]);
    $res = @file_get_contents($url, false, $ctx);
    return [$res === false ? 0 : 200, $res];
}
// Upload $tmpFile to Supabase, returns public URL or throws
function storageUpload(string $tmpFile, string $destName, string $mime): string {
    $url = rtrim((string)getenv('SUPABASE_URL'), '/') . '/storage/v1/object/' . storageBucket() . '/' . $destName;
    $key = (string)getenv('SUPABASE_KEY');
    [$code, $res] = storageHttp('POST', $url,
        ["apikey: $key", "Authorization: Bearer $key", "Content-Type: $mime", 'x-upsert: true'],
        (string)file_get_contents($tmpFile));
    if ($res === false || $code < 200 || $code >= 300) throw new Exception("Photo storage upload failed (HTTP $code).");
    return storagePublicUrl($destName);
}
// Best-effort delete (remote URL or local file — never fatal)
function storageDelete(?string $path): void {
    if (!$path) return;
    try {
        if (str_starts_with($path, 'http') && storageEnabled()) {
            $prefix = storagePublicUrl('');
            if (str_starts_with($path, $prefix)) {
                $obj = substr($path, strlen($prefix));
                $url = rtrim((string)getenv('SUPABASE_URL'), '/') . '/storage/v1/object/' . storageBucket() . '/' . $obj;
                $key = (string)getenv('SUPABASE_KEY');
                storageHttp('DELETE', $url, ["apikey: $key", "Authorization: Bearer $key"]);
                return;
            }
        }
        if (file_exists(BASE_PATH . '/' . $path)) @unlink(BASE_PATH . '/' . $path);
    } catch (Throwable $t) { /* best effort */ }
}
