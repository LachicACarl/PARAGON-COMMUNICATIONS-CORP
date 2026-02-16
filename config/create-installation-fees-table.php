<?php
session_start();
require_once 'database.php';
require_once 'helpers.php';

// Check if user is logged in and is head admin
if(!isLoggedIn() || !isHeadAdmin()) {
    die("Unauthorized access");
}

try {
    // Create installation_fees table
    $query = "CREATE TABLE IF NOT EXISTS installation_fees (
        id INT PRIMARY KEY AUTO_INCREMENT,
        fee_amount VARCHAR(100) NOT NULL,
        description TEXT,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )";

    $pdo->exec($query);
    echo "Installation fees table created successfully!";

} catch(Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}
