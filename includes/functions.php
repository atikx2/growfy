<?php
/** Shared helpers: escaping, settings, flash, csrf, icons, misc. */

function h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

/* ------------------------------ settings ----------------------------- */

function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (db()->query('SELECT k, v FROM ' . DB_PREFIX . 'settings') as $row) {
                $cache[$row['k']] = (string) $row['v'];
            }
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    return array_key_exists($key, $cache) ? $cache[$key] : $default;
}

function settings_set(array $pairs): void
{
    $st = db()->prepare('INSERT INTO ' . DB_PREFIX . 'settings (k, v) VALUES (?, ?)');
    $up = db()->prepare('UPDATE ' . DB_PREFIX . 'settings SET v = ? WHERE k = ?');
    $exists = db()->prepare('SELECT COUNT(*) FROM ' . DB_PREFIX . 'settings WHERE k = ?');
    foreach ($pairs as $k => $v) {
        $exists->execute([$k]);
        ((int) $exists->fetchColumn()) ? $up->execute([(string) $v, $k]) : $st->execute([$k, (string) $v]);
    }
}

/** Content helper with default from seed (so site never looks empty). */
function c(string $key): string
{
    static $seed = null;
    if ($seed === null) {
        $seed = seed_settings();
    }
    return setting($key, $seed[$key] ?? '');
}

/* ------------------------------- flash ------------------------------- */

function flash_set(string $type, string $msg): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* -------------------------------- csrf ------------------------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . h(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $ok = isset($_POST['_csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string) $_POST['_csrf']);
    if (!$ok) {
        http_response_code(419);
        exit('Security token mismatch. Please go back and try again.');
    }
}

/* ------------------------------- helpers ------------------------------ */

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 60);
}

function post(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

/** Validate + clamp a string field. */
function field(string $key, int $max = 200, bool $required = true): string
{
    $v = trim(mb_substr((string) ($_POST[$key] ?? ''), 0, $max));
    if ($required && $v === '') {
        return '';
    }
    return $v;
}

function time_ago(string $ts): string
{
    $t = strtotime($ts);
    if (!$t) return $ts;
    $d = time() - $t;
    if ($d < 60) return 'just now';
    if ($d < 3600) return floor($d / 60) . 'm ago';
    if ($d < 86400) return floor($d / 3600) . 'h ago';
    if ($d < 604800) return floor($d / 86400) . 'd ago';
    return date('M j, Y', $t);
}

/* ----------------------------- icon library -------------------------- */

function icon_names(): array
{
    return ['instagram','youtube','facebook','tiktok','spotify','apple','twitter','linkedin','telegram','whatsapp',
            'chart','trend','rocket','shield','bolt','target','users','star','heart','check','award','brush',
            'palette','megaphone','headset','globe','code','search','spark','mail','phone','pin','clock','message',
            'trash','edit','logout','eye','download','close'];
}

/** Inline SVG icon (stroke style, Lucide-like). */
function icon(string $name, string $cls = ''): string
{
    $paths = [
        'instagram' => '<rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4.25"/><circle cx="17.6" cy="6.4" r="1.15" fill="currentColor" stroke="none"/>',
        'youtube'   => '<path d="M2.7 7.2a3 3 0 0 1 2.1-2.2C6.6 4.5 9.3 4.4 12 4.4s5.4.1 7.2.6a3 3 0 0 1 2.1 2.2c.4 1.5.4 3 .4 4.8s0 3.3-.4 4.8a3 3 0 0 1-2.1 2.2c-1.8.5-4.5.6-7.2.6s-5.4-.1-7.2-.6a3 3 0 0 1-2.1-2.2c-.4-1.5-.4-3-.4-4.8s0-3.3.4-4.8Z"/><path d="m10 9.2 5.2 2.8L10 14.8z" fill="currentColor" stroke="none"/>',
        'facebook'  => '<path d="M15.5 3.5h-2.8a3.7 3.7 0 0 0-3.7 3.7v2.6H6.5v3.6H9v7.1h3.7v-7.1h2.7l.6-3.6h-3.3V7.6c0-.6.4-1 1-1h2.8z"/>',
        'tiktok'    => '<path d="M14.5 3v11.4a4.4 4.4 0 1 1-4.4-4.4"/><path d="M14.5 5.6a6.6 6.6 0 0 0 5.5 3.4"/>',
        'spotify'   => '<circle cx="12" cy="12" r="9.5"/><path d="M8 9.6c2.9-.9 5.8-.7 8.2.8M8.3 12.5c2.4-.7 4.7-.5 6.7.7M8.7 15.2c1.9-.5 3.6-.4 5.2.6"/>',
        'apple'     => '<path d="M16.2 12.8c0-2.4 2-3.6 2-3.7-1-1.5-2.7-1.7-3.3-1.7-1.4-.1-2.7.8-3.4.8-.7 0-1.8-.8-3-.8-1.5 0-3 .9-3.8 2.3-1.6 2.8-.4 7 1.2 9.3.8 1.1 1.7 2.4 2.9 2.3 1.2 0 1.6-.8 3-.8s1.8.8 3 .8c1.3 0 2.1-1.2 2.9-2.3a9 9 0 0 0 1.3-2.7 4 4 0 0 1-2.8-3.5Z"/><path d="M14 5.4c.6-.8 1.1-1.9 1-3-1 0-2.2.7-2.9 1.5-.6.7-1.2 1.9-1 3 1.1 0 2.2-.8 2.9-1.5Z"/>',
        'twitter'   => '<path d="M4 4l7.2 9.3L4.4 20h2.5l5.3-5.6 3.9 5.6H20l-7.5-9.8L18.9 4h-2.5l-4.4 4.9L8.5 4z"/>',
        'linkedin'  => '<rect x="3" y="3" width="18" height="18" rx="3.5"/><path d="M8 10.5V17M8 7.2v.1"/><path d="M12 17v-4a2.4 2.4 0 0 1 4.8 0v4M12 10.5V17"/>',
        'telegram'  => '<path d="m21 4.5-3.1 15.1c-.2 1-0.8 1.2-1.7.8l-4.7-3.5-2.2 2.2c-.3.3-.5.5-.9.5l.3-4.7 8.5-7.7c.4-.3-.1-.5-.6-.2L6.3 13.6l-4.6-1.4c-1-.3-1-1 .2-1.5L20.4 3c.8-.3 1.5.2.6 1.5Z"/>',
        'whatsapp'  => '<path d="M12 3.2a8.8 8.8 0 0 0-7.6 13.2L3.2 20.8l4.5-1.2A8.8 8.8 0 1 0 12 3.2Z"/><path d="M8.9 8.2c.3-.6.7-.6 1-.6h.7c.2 0 .5 0 .7.5l.9 2.1c.1.2 0 .5-.1.7l-.6.8c-.2.2-.2.5 0 .7.9 1.4 2 2.3 3.5 3 .3.1.5.1.7-.1l.9-1c.2-.3.5-.4.8-.2l2 1c.3.2.5.4.5.7-.1.7-.5 1.6-1.1 2-.6.5-1.5.8-2.6.5-2.6-.6-5.5-2.6-7-5.1-.9-1.6-1.1-2.9-.3-4.3Z" fill="currentColor" stroke="none"/>',
        'chart'     => '<path d="M4 20h16"/><path d="M7 16v-5M12 16V8M17 16v-8"/>',
        'trend'     => '<path d="m3 17 6-6 4 4 8-8.5"/><path d="M15 6.5h6v6"/>',
        'rocket'    => '<path d="M12 15.5c-1.5-1-3-2.5-4-4C9.4 5.6 14.5 3.5 20.5 3.5c0 6-2.1 11.1-8 12Z"/><path d="M8 11.5c-1.7.5-3.4 2.3-4 5 2.7-.6 4.5-2.3 5-4M15 20.5c-.5-1.5-.6-3 .1-4.4"/><circle cx="14.5" cy="9.5" r="1.8"/>',
        'shield'    => '<path d="M12 3 5 5.8v5.4c0 4.4 3 8 7 9.8 4-1.8 7-5.4 7-9.8V5.8z"/><path d="m9 12 2.2 2.2L15.4 10"/>',
        'bolt'      => '<path d="M13 2.5 4.5 13.5h5.7L9.8 21.5l8.7-11.2h-5.7z"/>',
        'target'    => '<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="4.8"/><circle cx="12" cy="12" r="1.2" fill="currentColor" stroke="none"/>',
        'users'     => '<circle cx="9" cy="8.5" r="3.5"/><path d="M3.5 20c.5-3.4 2.7-5.5 5.5-5.5S14 16.6 14.5 20"/><path d="M15.5 5.3a3.5 3.5 0 0 1 0 6.4M17.5 14.8c1.6.8 2.7 2.5 3 5.2"/>',
        'star'      => '<path d="m12 3 2.7 5.6 6.1.8-4.5 4.2 1.1 6L12 16.7l-5.4 2.9 1.1-6L3.2 9.4l6.1-.8z"/>',
        'heart'     => '<path d="M12 20.5S3.5 15.5 3.5 9.4C3.5 6.4 5.8 4.5 8.2 4.5c1.6 0 3 .9 3.8 2.1.8-1.2 2.2-2.1 3.8-2.1 2.4 0 4.7 1.9 4.7 4.9 0 6.1-8.5 11.1-8.5 11.1Z"/>',
        'check'     => '<circle cx="12" cy="12" r="9"/><path d="m8 12.3 2.7 2.7L16 9.5"/>',
        'award'     => '<circle cx="12" cy="9" r="5.5"/><path d="m8.8 13.5-1.3 7 4.5-2.4 4.5 2.4-1.3-7"/>',
        'brush'     => '<path d="M4 20c1.5 0 5 .2 6.5-1.5l8.2-8.2a2.3 2.3 0 0 0-3.2-3.2L7.3 15.3C5.5 17 4 18.5 4 20Z"/><path d="m14 6.8 3.2 3.2"/>',
        'palette'   => '<path d="M12 3.5a8.5 8.5 0 1 0 0 17c1.5 0 2-1 1.4-2.1-.6-1.3.2-2.4 1.6-2.4h1.5a4.1 4.1 0 0 0 4-4.3C20.4 7 16.6 3.5 12 3.5Z"/><circle cx="7.8" cy="10.5" r="1.1" fill="currentColor" stroke="none"/><circle cx="12" cy="7.6" r="1.1" fill="currentColor" stroke="none"/><circle cx="16.2" cy="10.5" r="1.1" fill="currentColor" stroke="none"/>',
        'megaphone' => '<path d="M3 11v3l4 .6V10.4z"/><path d="m7 10.4 12.5-5.2v14.6L7 14.6"/><path d="M9 15.4a2.5 2.5 0 0 0 5 .4"/>',
        'headset'   => '<path d="M4.5 17v-4a7.5 7.5 0 0 1 15 0v4"/><rect x="3.5" y="14.5" width="4" height="6" rx="1.6"/><rect x="16.5" y="14.5" width="4" height="6" rx="1.6"/>',
        'globe'     => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.6 2.4 3.9 5.6 3.9 9s-1.3 6.6-3.9 9c-2.6-2.4-3.9-5.6-3.9-9S9.4 5.4 12 3Z"/>',
        'code'      => '<path d="m8 7-5 5 5 5M16 7l5 5-5 5M13.5 4l-3 16"/>',
        'search'    => '<circle cx="11" cy="11" r="7"/><path d="m20.5 20.5-4.8-4.8"/>',
        'spark'     => '<path d="M12 3.5 14 9l5.5 2-5.5 2-2 5.5-2-5.5L4.5 11 10 9z"/><path d="M19 3v3M17.5 4.5h3M5 18v2.5M3.8 19.3h2.4"/>',
        'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m3.5 7 8.5 6 8.5-6"/>',
        'phone'     => '<path d="M6.8 3.5 9.2 3l1.7 4-1.9 1.6c.9 1.9 2.4 3.5 4.4 4.5L15.2 11l4 1.7-.4 2.5c-.2 1.4-1.3 2.4-2.7 2.4C9.6 17.6 6.4 14.4 6.4 8c0-1.4 1-2.5.4-4.5Z"/>',
        'pin'       => '<path d="M12 21.5S5 14.9 5 9.9A7 7 0 0 1 19 9.9c0 5-7 11.6-7 11.6Z"/><circle cx="12" cy="9.8" r="2.6"/>',
        'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'message'   => '<path d="M21 12a8.5 8.5 0 0 1-8.5 8.5c-1.5 0-3-.4-4.2-1L3.5 20.5 4.9 16a8.5 8.5 0 1 1 16.1-4Z"/><path d="M8.5 11h7M8.5 14h4"/>',
        'trash'     => '<path d="M4 6.5h16M9.5 6V4.4A1.4 1.4 0 0 1 10.9 3h2.2a1.4 1.4 0 0 1 1.4 1.4V6M6.5 6.5l.8 12.2A1.9 1.9 0 0 0 9.2 20.5h5.6a1.9 1.9 0 0 0 1.9-1.8l.8-12.2"/><path d="M10 10.5v6M14 10.5v6"/>',
        'edit'      => '<path d="M14.5 5.5a2.1 2.1 0 0 1 3 3L8.3 17.7 4 19l1.3-4.3z"/>',
        'logout'    => '<path d="M9 21H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h3"/><path d="m16 17 5-5-5-5M21 12H9"/>',
        'eye'       => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/>',
        'download'  => '<path d="M12 3v12M7 10l5 5 5-5"/><path d="M4 20h16"/>',
        'close'     => '<path d="M6 6l12 12M18 6 6 18"/>',
    ];
    $p = $paths[$name] ?? $paths['spark'];
    return '<svg class="ic ' . h($cls) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}

/** Star rating row. */
function stars(int $n): string
{
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $fill = $i <= $n ? 'currentColor' : 'none';
        $out .= '<svg viewBox="0 0 24 24" class="star" fill="' . $fill . '" stroke="currentColor" stroke-width="1.5"><path d="m12 3 2.7 5.6 6.1.8-4.5 4.2 1.1 6L12 16.7l-5.4 2.9 1.1-6L3.2 9.4l6.1-.8z"/></svg>';
    }
    return '<span class="stars" aria-label="' . $n . ' out of 5 stars">' . $out . '</span>';
}

/** Initials avatar for testimonials without a photo. */
function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $a = mb_substr($parts[0] ?? 'G', 0, 1);
    $b = mb_substr($parts[1] ?? '', 0, 1);
    return mb_strtoupper($a . $b);
}

/* ----------------------------- upload helper ------------------------- */

function handle_image_upload(string $field): ?string
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $f   = $_FILES[$field];
    $max = UPLOAD_MAX_MB * 1024 * 1024;
    if ($f['size'] > $max) {
        throw new RuntimeException('Image is too large (max ' . UPLOAD_MAX_MB . 'MB).');
    }
    $info = @getimagesize($f['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!$info || !isset($allowed[$info['mime']])) {
        throw new RuntimeException('Only JPG, PNG, WEBP or GIF images are allowed.');
    }
    $dir = GROWFY_ROOT . '/uploads';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $name = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$info['mime']];
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
        // WASM preview / other SAPIs fallback
        if (!@rename($f['tmp_name'], $dir . '/' . $name)) {
            throw new RuntimeException('Upload failed — check uploads/ folder permissions.');
        }
    }
    return 'uploads/' . $name;
}
