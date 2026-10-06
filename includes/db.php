<?php
/**
 * PDO factory + zero-config auto-installer.
 * MySQL (cPanel) or SQLite (fallback / preview) — same code.
 */

require_once __DIR__ . '/schema.php';

function db_driver(): string
{
    if (DB_DRIVER === 'mysql' || DB_DRIVER === 'sqlite') {
        return DB_DRIVER;
    }
    return trim((string) DB_NAME) !== '' ? 'mysql' : 'sqlite';
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $driver = db_driver();
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    if ($driver === 'mysql') {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
    } else {
        $dir = GROWFY_ROOT . '/data';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $pdo = new PDO('sqlite:' . $dir . '/growfy.db', null, null, $opts);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');
    }

    db_install($pdo, $driver);
    return $pdo;
}

/** Create tables + seed on first run. Safe to call on every request. */
function db_install(PDO $pdo, string $driver): void
{
    $p = DB_PREFIX;
    $exists = false;
    try {
        if ($driver === 'mysql') {
            $st = $pdo->query("SHOW TABLES LIKE '{$p}settings'");
            $exists = (bool) $st->fetchColumn();
        } else {
            $st = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$p}settings'");
            $exists = (bool) $st->fetchColumn();
        }
    } catch (Throwable $e) {
        $exists = false;
    }

    foreach (schema_statements($driver) as $sql) {
        $pdo->exec($sql);
    }

    if ($exists) {
        return; // already installed
    }

    // ---- seed settings ----
    $st = $pdo->prepare("INSERT INTO {$p}settings (k, v) VALUES (?, ?)");
    foreach (seed_settings() as $k => $v) {
        $st->execute([$k, $v]);
    }

    // ---- seed admin (change on first login) ----
    $pdo->prepare("INSERT INTO {$p}admins (username, pass_hash, display_name, force_change) VALUES (?, ?, ?, 1)")
        ->execute(['admin', password_hash('Growfy@2026', PASSWORD_DEFAULT), 'Administrator']);

    // ---- seed rows ----
    $rows = seed_rows();

    $ins = function (string $table, array $cols, array $sets) use ($pdo, $p) {
        $sql = "INSERT INTO {$p}{$table} (" . implode(',', $cols) . ") VALUES (" .
            rtrim(str_repeat('?,', count($cols)), ',') . ")";
        $st = $pdo->prepare($sql);
        foreach ($sets as $row) {
            $st->execute($row);
        }
    };

    $ins('services',     ['title','tag','platform','icon','description','price_from','sort_order'], $rows['services']);
    $ins('stats',        ['value','suffix','label','sublabel','icon','sort_order'],                 $rows['stats']);
    $ins('steps',        ['title','description','icon','sort_order'],                               $rows['steps']);
    $ins('features',     ['title','description','icon','sort_order'],                               $rows['features']);
    $ins('testimonials', ['name','role','avatar','quote','stars','sort_order'],                     $rows['testimonials']);
    $ins('faqs',         ['question','answer','sort_order'],                                        $rows['faqs']);
}

/* ---------- tiny query helpers ---------- */

function q_all(string $table, string $where = '1=1', string $order = 'sort_order ASC, id ASC'): array
{
    $st = db()->query('SELECT * FROM ' . DB_PREFIX . $table . " WHERE $where ORDER BY $order");
    return $st->fetchAll() ?: [];
}

function q_one(string $table, string $where, array $args = []): ?array
{
    $st = db()->prepare('SELECT * FROM ' . DB_PREFIX . $table . " WHERE $where LIMIT 1");
    $st->execute($args);
    $row = $st->fetch();
    return $row ?: null;
}

function q_count(string $table, string $where = '1=1', array $args = []): int
{
    $st = db()->prepare('SELECT COUNT(*) FROM ' . DB_PREFIX . $table . " WHERE $where");
    $st->execute($args);
    return (int) $st->fetchColumn();
}
