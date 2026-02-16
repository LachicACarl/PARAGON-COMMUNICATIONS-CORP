<?php
session_start();

require_once 'config/authenticate.php';
require_once 'config/database.php';
require_once 'config/helpers.php';

if (!isHeadAdmin()) {
  die('Unauthorized access');
}

$sql = "CREATE TABLE IF NOT EXISTS main_remarks (
  id INT PRIMARY KEY AUTO_INCREMENT,
  remark_text TEXT NOT NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
)";

try {
  // Use PDO from database.php
  global $pdo;
  $pdo->exec($sql);
  echo "Main remarks table created successfully!";
} catch (PDOException $e) {
  echo "Error creating table: " . $e->getMessage();
}
