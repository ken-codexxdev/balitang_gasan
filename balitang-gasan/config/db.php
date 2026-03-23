<?php
// =============================================================
//  Gasan News — Database Configuration
//  Edit these values to match your hosting environment.
// =============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'balitang_gasan_db');
define('DB_USER', 'root');       // ← change for production
define('DB_PASS', '');           // ← change for production
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'Balitang Gaseño');
define('SITE_URL', 'http://localhost/balitang-gasan');   // ← no trailing slash
define('UPLOAD_DIR', __DIR__ . '/../uploads/articles/');
define('UPLOAD_URL', SITE_URL . '/uploads/articles/');

// ---------------------------------------------------------------
//  PDO Connection
// ---------------------------------------------------------------
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('<div style="font-family:sans-serif;padding:20px;color:#c00">
         <strong>Database Connection Error:</strong> ' . htmlspecialchars($e->getMessage()) . '
         <br><small>Check your credentials in config/db.php</small></div>');
}

// ---------------------------------------------------------------
//  Helper: generate URL-friendly slug
// ---------------------------------------------------------------
function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// ---------------------------------------------------------------
//  Helper: truncate text
// ---------------------------------------------------------------
function excerpt(string $html, int $words = 25): string {
    $text = wp_strip_tags($html);
    $arr = explode(' ', $text);
    if (count($arr) <= $words) return $text;
    return implode(' ', array_slice($arr, 0, $words)) . '…';
}

function wp_strip_tags(string $html): string {
    return strip_tags($html);
}

// ---------------------------------------------------------------
//  Helper: time-ago string
// ---------------------------------------------------------------
function time_ago(string $datetime): string {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . ' year'  . ($diff->y  > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m  > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day'   . ($diff->d  > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour'  . ($diff->h  > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min'   . ($diff->i  > 1 ? 's' : '') . ' ago';
    return 'just now';
}

// ---------------------------------------------------------------
//  Session & auth helpers
// ---------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function current_user(): array {
    return $_SESSION['user'] ?? [];
}

