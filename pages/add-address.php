<?php
/**
 * PARAGON COMMUNICATIONS - Add Address API
 * Handles adding regions, provinces, and municipalities to database
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['type'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }

    $type = $input['type'];
    $response = ['success' => false];

    if ($type === 'region') {
        $name = trim($input['name'] ?? '');
        if (empty($name)) {
            $response['error'] = 'Region name is required';
        } else {
            try {
                insert($pdo, 'regions', ['name' => $name]);
                $response['success'] = true;
                logAction($pdo, 'CREATE', 'regions', null, null, ['name' => $name]);
            } catch (Exception $e) {
                $response['error'] = 'Region already exists or database error';
            }
        }

    } elseif ($type === 'province') {
        $regionId = (int)($input['regionId'] ?? 0);
        $name = trim($input['name'] ?? '');
        
        if (empty($regionId) || empty($name)) {
            $response['error'] = 'Region and province name are required';
        } else {
            try {
                insert($pdo, 'provinces', ['region_id' => $regionId, 'name' => $name]);
                $response['success'] = true;
                logAction($pdo, 'CREATE', 'provinces', null, null, ['region_id' => $regionId, 'name' => $name]);
            } catch (Exception $e) {
                $response['error'] = 'Province already exists for this region or database error';
            }
        }

    } elseif ($type === 'municipality') {
        $regionId = (int)($input['regionId'] ?? 0);
        $provinceId = (int)($input['provinceId'] ?? 0);
        $name = trim($input['name'] ?? '');
        
        if (empty($regionId) || empty($provinceId) || empty($name)) {
            $response['error'] = 'Region, province, and municipality name are required';
        } else {
            try {
                insert($pdo, 'municipalities', [
                    'region_id' => $regionId,
                    'province_id' => $provinceId,
                    'name' => $name
                ]);
                $response['success'] = true;
                logAction($pdo, 'CREATE', 'municipalities', null, null, [
                    'region_id' => $regionId,
                    'province_id' => $provinceId,
                    'name' => $name
                ]);
            } catch (Exception $e) {
                $response['error'] = 'Municipality already exists for this province or database error';
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    error_log("Add address error: " . $e->getMessage());
}
?>
