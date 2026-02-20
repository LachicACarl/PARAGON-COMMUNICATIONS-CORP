<?php
/**
 * Database Connection Test
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    echo "=== PARAGON DATABASE CONNECTION TEST ===\n\n";
    
    // Test basic connection
    $result = $pdo->query('SELECT 1');
    echo "✓ Database connection: SUCCESS\n";
    
    // Check database name
    echo "✓ Database name: " . DB_NAME . "\n";
    
    // Check host
    echo "✓ Host: " . DB_HOST . "\n";
    
    // Check user
    echo "✓ User: " . DB_USER . "\n";
    
    // Check tables
    $tablesQuery = "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?";
    $result = getRow($pdo, $tablesQuery, [DB_NAME]);
    echo "✓ Tables in database: " . $result['count'] . "\n";
    
    // List all tables
    $listQuery = "SELECT table_name FROM information_schema.tables WHERE table_schema = ? ORDER BY table_name";
    $stmt = $pdo->prepare($listQuery);
    $stmt->execute([DB_NAME]);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($tables)) {
        echo "\nTables found:\n";
        foreach ($tables as $table) {
            echo "  • " . $table . "\n";
        }
    }
    
    echo "\n=== DATABASE IS CONNECTED AND WORKING ===\n";
    
} catch(Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "  1. MySQL is running\n";
    echo "  2. Database credentials in .env file\n";
    echo "  3. Database 'paragon_db' exists\n";
}
?>
