<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/config.php';

try {
    // Drop existing table if it has wrong structure
    $pdo->exec("DROP TABLE IF EXISTS installation_fees");

    // Create installation_fees table with proper structure
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS installation_fees (
            id INT PRIMARY KEY AUTO_INCREMENT,
            fee_name VARCHAR(255) NOT NULL,
            amount DECIMAL(15, 2) NOT NULL,
            description LONGTEXT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_fee_name (fee_name),
            UNIQUE KEY unique_fee_name (fee_name)
        )
    ");

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Installation fees table created successfully!']);

} catch(Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error creating table: ' . $e->getMessage()]);
    error_log('Installation fees table creation error: ' . $e->getMessage());
}
