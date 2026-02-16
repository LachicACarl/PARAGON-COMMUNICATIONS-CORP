<?php
session_start();
require_once 'database.php';
require_once 'helpers.php';

// Check if user is logged in
if(!isLoggedIn()) {
    die("Unauthorized access");
}

try {
    // Create pull_out_remarks table
    $query = "CREATE TABLE IF NOT EXISTS pull_out_remarks (
        id INT PRIMARY KEY AUTO_INCREMENT,
        remarks TEXT NOT NULL,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )";

    $pdo->exec($query);
    echo "Pull out remarks table created successfully!";

} catch(Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}
