<?php
session_start();
require_once 'database.php';
require_once 'helpers.php';

// Check if user is logged in and is head admin
if(!isLoggedIn() || !isHeadAdmin()) {
    die("Unauthorized access");
}

try {
    // Create call_out_status table
    $query = "CREATE TABLE IF NOT EXISTS call_out_status (
        id INT PRIMARY KEY AUTO_INCREMENT,
        status_name VARCHAR(255) NOT NULL UNIQUE,
        description TEXT,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )";

    $pdo->exec($query);
    echo "Call out status table created successfully!";

} catch(Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}
