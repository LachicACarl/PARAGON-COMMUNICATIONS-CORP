<?php
session_start();
require_once 'database.php';
require_once 'helpers.php';

// Check if user is logged in
if(!isLoggedIn()) {
    die("Unauthorized access");
}

try {
    // Create status_input_channel table
    $query = "CREATE TABLE IF NOT EXISTS status_input_channel (
        id INT PRIMARY KEY AUTO_INCREMENT,
        channel_name VARCHAR(255) NOT NULL UNIQUE,
        description TEXT,
        is_active TINYINT DEFAULT 1,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )";

    $pdo->exec($query);
    echo "Status input channel table created successfully!";

} catch(Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}
