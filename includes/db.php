<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

// DATABASE_URL (e.g. postgresql://user:pass@host:5432/db) selects Postgres.
// Unset => local SQLite file. Same code runs on both.
function dbIsPostgres(): bool {
    return (bool)getenv('DATABASE_URL');
}

function getDB(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    try {
        $pg = dbIsPostgres();
        if ($pg) {
            $p = parse_url((string)getenv('DATABASE_URL'));
            $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s',
                $p['host'] ?? 'localhost', $p['port'] ?? 5432, ltrim($p['path'] ?? '/closetcraft', '/'));
            // Keep query flags like ?sslmode=require (needed by Supabase)
            if (!empty($p['query'])) {
                parse_str($p['query'], $q);
                if (!empty($q['sslmode'])) $dsn .= ';sslmode=' . $q['sslmode'];
            }
            $user = isset($p['user']) ? rawurldecode($p['user']) : null;
            $pass = isset($p['pass']) ? rawurldecode($p['pass']) : null;
            $pdo = new PDO($dsn, $user, $pass);
        } else {
            if (!is_dir(dirname(DB_FILE))) mkdir(dirname(DB_FILE), 0777, true);
            $pdo = new PDO('sqlite:' . DB_FILE);
        }
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Full final schema (all columns incl. later additions), so fresh
        // databases on either dialect need no step-by-step migrations.
        $id = $pg ? 'SERIAL PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
        $pdo->exec("CREATE TABLE IF NOT EXISTS items (
            id $id,
            name TEXT NOT NULL,
            type TEXT NOT NULL CHECK(type IN ('top','bottom','dress','shoes','outerwear','accessory')),
            color TEXT NOT NULL,
            occasion TEXT NOT NULL CHECK(occasion IN ('casual','formal','party','sports','ethnic')),
            for_gender TEXT DEFAULT 'unisex',
            user_id INTEGER,
            image_path TEXT,
            created_at TEXT NOT NULL
        )");
        $pdo->exec("CREATE TABLE IF NOT EXISTS outfits (
            id $id,
            name TEXT NOT NULL,
            top_id INTEGER, bottom_id INTEGER, dress_id INTEGER,
            shoes_id INTEGER, outerwear_id INTEGER, accessory_id INTEGER,
            occasion TEXT NOT NULL,
            style TEXT DEFAULT 'unisex',
            user_id INTEGER,
            fav_date TEXT,
            created_at TEXT NOT NULL
        )");
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id $id,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at TEXT NOT NULL
        )");
        // Bring pre-existing SQLite databases up to the same schema (portable statements only)
        $extra = [
            'outfits' => ['dress_id' => 'INTEGER', 'outerwear_id' => 'INTEGER', 'style' => "TEXT DEFAULT 'unisex'", 'user_id' => 'INTEGER'],
            'items' => ['for_gender' => "TEXT DEFAULT 'unisex'", 'user_id' => 'INTEGER'],
        ];
        foreach ($extra as $table => $cols) {
            foreach ($cols as $col => $typedef) {
                if (!columnExists($pdo, $pg, $table, $col)) {
                    $pdo->exec("ALTER TABLE $table ADD COLUMN $col $typedef");
                }
            }
        }
        $pdo->exec("UPDATE items SET for_gender='unisex' WHERE for_gender IS NULL");
        // Seed demo data on first run
        $count = (int)$pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
        if ($count === 0) seedDemo($pdo);
    } catch (PDOException $ex) {
        die("Database error: " . e($ex->getMessage()));
    }
    return $pdo;
}

// Portable column check (PRAGMA is SQLite-only)
function columnExists(PDO $pdo, bool $pg, string $table, string $col): bool {
    if ($pg) {
        $s = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_name=? AND column_name=?");
        $s->execute([$table, $col]);
        return (bool)$s->fetchColumn();
    }
    $cols = $pdo->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_COLUMN, 1);
    return in_array($col, $cols, true);
}

// Portable last-insert id (Postgres needs the sequence name)
function dbLastId(PDO $pdo, string $table): int {
    if (dbIsPostgres()) return (int)$pdo->lastInsertId($table . '_id_seq');
    return (int)$pdo->lastInsertId();
}

function seedDemo(PDO $pdo): void {
    $demo = [
        ['White Shirt', 'top', 'white', 'formal', 'unisex'],
        ['Blue Jeans', 'bottom', 'blue', 'casual', 'unisex'],
        ['Red Dress', 'dress', 'red', 'party', 'female'],
        ['Running Shoes', 'shoes', 'white', 'sports', 'unisex'],
        ['Denim Jacket', 'outerwear', 'blue', 'casual', 'unisex'],
        ['Kurta Set', 'dress', 'green', 'ethnic', 'male'],
        ['Black Heels', 'shoes', 'black', 'party', 'female'],
        ['Gold Watch', 'accessory', 'gold', 'formal', 'unisex'],
    ];
    $stmt = $pdo->prepare("INSERT INTO items(name,type,color,occasion,for_gender,image_path,created_at) VALUES(?,?,?,?,?,?,?)");
    foreach ($demo as $d) {
        $stmt->execute([$d[0], $d[1], $d[2], $d[3], $d[4], '', date('Y-m-d H:i:s')]);
    }
}

/*
 * Stored procedure execution in PHP (MySQL version).
 * SQLite/Postgres demos don't use stored procedures; for MySQL deployment use
 * database/closetcraft_mysql.sql which defines GetItemsByOccasion(p_occasion),
 * CountItemsByType(). Example: $pdo->query("CALL GetItemsByOccasion('casual')");
 * The api.php?action=stats simulates the stored-procedure result for the demos.
 */
