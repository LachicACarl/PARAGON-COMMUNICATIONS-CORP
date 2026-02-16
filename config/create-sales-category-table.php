<?php
session_start();

require_once 'config/authenticate.php';
require_once 'config/database.php';
require_once 'config/helpers.php';

if (!isHeadAdmin()) {
  die('Unauthorized access');
}

$sql = "CREATE TABLE IF NOT EXISTS sales_category (
  id INT PRIMARY KEY AUTO_INCREMENT,
  category_name VARCHAR(255) UNIQUE NOT NULL,
  description TEXT,
  is_active TINYINT DEFAULT 1,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
)";

try {
  $pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
    DB_USER,
    DB_PASS
  );
  
  $pdo->exec($sql);
  echo "Sales category table created successfully!";
} catch (PDOException $e) {
  echo "Error creating table: " . $e->getMessage();
}
