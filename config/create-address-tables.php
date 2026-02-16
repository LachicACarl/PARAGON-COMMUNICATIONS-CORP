<?php
/**
 * Create Address Management Tables
 * This file creates the necessary tables for region, province, and municipality management
 */

session_start();
require_once __DIR__ . '/config/database.php';

try {
    // Create regions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS regions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL UNIQUE,
            code VARCHAR(10),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (name)
        )
    ");

    // Create provinces table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS provinces (
            id INT PRIMARY KEY AUTO_INCREMENT,
            region_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(10),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE CASCADE,
            UNIQUE KEY unique_province (region_id, name),
            INDEX (name),
            INDEX (region_id)
        )
    ");

    // Create municipalities table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS municipalities (
            id INT PRIMARY KEY AUTO_INCREMENT,
            province_id INT NOT NULL,
            region_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(10),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE CASCADE,
            FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE CASCADE,
            UNIQUE KEY unique_municipality (province_id, name),
            INDEX (name),
            INDEX (province_id),
            INDEX (region_id)
        )
    ");

    echo "Tables created successfully!";

} catch(PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>
