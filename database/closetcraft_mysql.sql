-- database/closetcraft_mysql.sql
-- MySQL deployment (with stored procedures). For quick demo the app uses SQLite automatically.
CREATE DATABASE IF NOT EXISTS closetcraft CHARACTER SET utf8mb4;
USE closetcraft;
CREATE TABLE IF NOT EXISTS items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(40) NOT NULL,
  type ENUM('top','bottom','dress','shoes','outerwear','accessory') NOT NULL,
  color VARCHAR(20) NOT NULL,
  occasion ENUM('casual','formal','party','sports','ethnic') NOT NULL,
  image_path VARCHAR(255),
  created_at DATETIME NOT NULL
);
CREATE TABLE IF NOT EXISTS outfits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(40) NOT NULL,
  top_id INT, bottom_id INT, shoes_id INT, accessory_id INT,
  occasion VARCHAR(20) NOT NULL,
  fav_date DATE, created_at DATETIME NOT NULL
);
DELIMITER //
-- Stored procedure 1: filtered read
CREATE PROCEDURE GetItemsByOccasion(IN p_occasion VARCHAR(20))
BEGIN SELECT * FROM items WHERE occasion = p_occasion ORDER BY id DESC; END //
-- Stored procedure 2: aggregation
CREATE PROCEDURE CountItemsByType()
BEGIN SELECT type, COUNT(*) AS total FROM items GROUP BY type; END //
DELIMITER ;
-- PHP call example: $pdo->query("CALL GetItemsByOccasion('casual')");
