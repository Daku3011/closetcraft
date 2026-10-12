<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    try {
        if (!is_dir(dirname(DB_FILE))) mkdir(dirname(DB_FILE), 0777, true);
        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE TABLE IF NOT EXISTS items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            type TEXT NOT NULL CHECK(type IN ('top','bottom','dress','shoes','outerwear','accessory')),
            color TEXT NOT NULL,
            occasion TEXT NOT NULL CHECK(occasion IN ('casual','formal','party','sports','ethnic')),
            image_path TEXT,
            created_at TEXT NOT NULL
        )");
        $pdo->exec("CREATE TABLE IF NOT EXISTS outfits (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            top_id INTEGER, bottom_id INTEGER, shoes_id INTEGER, accessory_id INTEGER,
            occasion TEXT NOT NULL,
            fav_date TEXT,
            created_at TEXT NOT NULL,
            FOREIGN KEY(top_id) REFERENCES items(id),
            FOREIGN KEY(bottom_id) REFERENCES items(id)
        )");
        // Migration: newer columns for dress/outerwear slots + gender tagging + fit style
        $cols = $pdo->query("PRAGMA table_info(outfits)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('dress_id', $cols, true)) $pdo->exec("ALTER TABLE outfits ADD COLUMN dress_id INTEGER");
        if (!in_array('outerwear_id', $cols, true)) $pdo->exec("ALTER TABLE outfits ADD COLUMN outerwear_id INTEGER");
        $ocols = $pdo->query("PRAGMA table_info(outfits)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('style', $ocols, true)) $pdo->exec("ALTER TABLE outfits ADD COLUMN style TEXT DEFAULT 'unisex'");
        $icols = $pdo->query("PRAGMA table_info(items)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('for_gender', $icols, true)) $pdo->exec("ALTER TABLE items ADD COLUMN for_gender TEXT DEFAULT 'unisex'");
        $pdo->exec("UPDATE items SET for_gender='unisex' WHERE for_gender IS NULL");
        // Seed demo data on first run
        $count = (int)$pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
        if ($count === 0) seedDemo($pdo);
    } catch (PDOException $ex) {
        die("Database error: " . e($ex->getMessage()));
    }
    return $pdo;
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
 * SQLite has no stored procedures, so for MySQL deployment use database/closetcraft_mysql.sql
 * which defines:  GetItemsByOccasion(p_occasion), CountItemsByType()
 * Example call:
 *   $mysqli = new mysqli("localhost","root","","closetcraft");
 *   $res = $mysqli->query("CALL GetItemsByOccasion('casual')");
 *   // or PDO: $pdo->query("CALL GetItemsByOccasion('casual')");
 * The api.php?action=stats simulates the stored-procedure result for the SQLite demo.
 */
