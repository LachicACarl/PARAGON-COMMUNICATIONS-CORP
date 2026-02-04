<?php
/**
 * PARAGON COMMUNICATIONS - Database Configuration with PDO
 * Handles all database connections securely using PDO
 */

// Load configuration
require_once __DIR__ . '/config.php';

// PDO Connection Options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => false,
];

try {
    // Create PDO connection
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Set timezone to UTC for consistency
    $pdo->exec("SET time_zone='+00:00'");
    
} catch(PDOException $e) {
    // Log error securely (don't show in production)
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Show user-friendly message
    if(defined('ENV') && ENV === 'development') {
        die("Database Connection Failed: " . $e->getMessage());
    } else {
        die("Database connection error. Please try again later.");
    }
}

/**
 * Helper function to execute prepared statements safely
 */
function executeQuery($pdo, $query, $params = []) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Helper function to get single row
 */
function getRow($pdo, $query, $params = []) {
    $stmt = executeQuery($pdo, $query, $params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Helper function to get all rows
 */
function getAll($pdo, $query, $params = []) {
    $stmt = executeQuery($pdo, $query, $params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Helper function to insert data
 */
function insert($pdo, $table, $data) {
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');
    
    $query = "INSERT INTO " . $table . " (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
    
    $stmt = executeQuery($pdo, $query, array_values($data));
    return $pdo->lastInsertId();
}

/**
 * Helper function to update data
 */
function update($pdo, $table, $data, $where) {
    $set = [];
    $values = [];
    
    foreach($data as $key => $value) {
        $set[] = $key . " = ?";
        $values[] = $value;
    }
    
    foreach($where as $key => $value) {
        $values[] = $value;
    }
    
    $whereClause = "";
    $i = 0;
    foreach($where as $key => $value) {
        if($i > 0) $whereClause .= " AND ";
        $whereClause .= $key . " = ?";
        $i++;
    }
    
    $query = "UPDATE " . $table . " SET " . implode(", ", $set) . " WHERE " . $whereClause;
    
    $stmt = executeQuery($pdo, $query, $values);
    return $stmt->rowCount();
}