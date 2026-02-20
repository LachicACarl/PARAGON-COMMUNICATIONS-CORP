<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/config.php';

try {
    // Drop existing tables if they have wrong structure
    $pdo->exec("DROP TABLE IF EXISTS call_out_status");
    $pdo->exec("DROP TABLE IF EXISTS pull_out_remarks");
    $pdo->exec("DROP TABLE IF EXISTS status_input_channel");
    $pdo->exec("DROP TABLE IF EXISTS sales_category");
    $pdo->exec("DROP TABLE IF EXISTS main_remarks");

    // Create call_out_status table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS call_out_status (
            id INT PRIMARY KEY AUTO_INCREMENT,
            status_name VARCHAR(100) NOT NULL,
            description LONGTEXT,
            color_code VARCHAR(7),
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
            UNIQUE KEY unique_status_name (status_name),
            INDEX idx_status_name (status_name)
        )
    ");

    // Create pull_out_remarks table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pull_out_remarks (
            id INT PRIMARY KEY AUTO_INCREMENT,
            remark_text VARCHAR(255) NOT NULL,
            description LONGTEXT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
            UNIQUE KEY unique_remark_text (remark_text),
            INDEX idx_remark_text (remark_text)
        )
    ");

    // Create status_input_channel table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS status_input_channel (
            id INT PRIMARY KEY AUTO_INCREMENT,
            channel_name VARCHAR(100) NOT NULL,
            description LONGTEXT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
            UNIQUE KEY unique_channel_name (channel_name),
            INDEX idx_channel_name (channel_name)
        )
    ");

    // Create sales_category table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sales_category (
            id INT PRIMARY KEY AUTO_INCREMENT,
            category_name VARCHAR(100) NOT NULL,
            description LONGTEXT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
            UNIQUE KEY unique_category_name (category_name),
            INDEX idx_category_name (category_name)
        )
    ");

    // Create main_remarks table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS main_remarks (
            id INT PRIMARY KEY AUTO_INCREMENT,
            remark_title VARCHAR(255) NOT NULL,
            remark_description LONGTEXT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
            UNIQUE KEY unique_remark_title (remark_title),
            INDEX idx_remark_title (remark_title)
        )
    ");

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'All configuration tables created successfully!']);

} catch(Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error creating tables: ' . $e->getMessage()]);
    error_log('Config tables creation error: ' . $e->getMessage());
}
?>
