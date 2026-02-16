<?php
/**
 * PARAGON COMMUNICATIONS - Address Data Fetcher
 * Fetches region, province, and municipality data from the database
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
    $action = $_GET['action'] ?? 'get';
    $type = $_GET['type'] ?? 'region'; // region, province, municipality
    $page = (int)($_GET['page'] ?? 1);
    $search = $_GET['search'] ?? '';
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    if ($action === 'get') {
        $response = [
            'success' => true,
            'data' => [],
            'pagination' => ['page' => $page, 'perPage' => $perPage, 'total' => 0, 'totalPages' => 0],
            'counts' => []
        ];

        if ($type === 'region') {
            // Get region count
            $countResult = getRow($pdo, "SELECT COUNT(*) as total FROM regions");
            $total = $countResult['total'] ?? 0;
            
            $query = "SELECT * FROM regions";
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE name LIKE ?";
                $params[] = '%' . $search . '%';
            }
            
            $query .= " ORDER BY name ASC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $regions = getAll($pdo, $query, $params);
            
            foreach ($regions as $region) {
                $response['data'][] = [
                    'id' => $region['id'],
                    'name' => $region['name'],
                    'code' => $region['code'],
                    'createdAt' => $region['created_at']
                ];
            }
            
            $response['pagination'] = [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => ceil($total / $perPage)
            ];

        } elseif ($type === 'province') {
            // Get province count
            $countResult = getRow($pdo, "SELECT COUNT(*) as total FROM provinces");
            $total = $countResult['total'] ?? 0;
            
            $query = "
                SELECT p.*, r.name as region_name
                FROM provinces p
                JOIN regions r ON p.region_id = r.id
            ";
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE p.name LIKE ? OR r.name LIKE ?";
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
            }
            
            $query .= " ORDER BY r.name, p.name ASC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $provinces = getAll($pdo, $query, $params);
            
            foreach ($provinces as $province) {
                $response['data'][] = [
                    'id' => $province['id'],
                    'regionId' => $province['region_id'],
                    'regionName' => $province['region_name'],
                    'name' => $province['name'],
                    'code' => $province['code'],
                    'createdAt' => $province['created_at']
                ];
            }
            
            $response['pagination'] = [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => ceil($total / $perPage)
            ];

        } elseif ($type === 'municipality') {
            // Get municipality count
            $countResult = getRow($pdo, "SELECT COUNT(*) as total FROM municipalities");
            $total = $countResult['total'] ?? 0;
            
            $query = "
                SELECT m.*, p.name as province_name, r.name as region_name
                FROM municipalities m
                JOIN provinces p ON m.province_id = p.id
                JOIN regions r ON m.region_id = r.id
            ";
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE m.name LIKE ? OR p.name LIKE ? OR r.name LIKE ?";
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
            }
            
            $query .= " ORDER BY r.name, p.name, m.name ASC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $municipalities = getAll($pdo, $query, $params);
            
            foreach ($municipalities as $municipality) {
                $response['data'][] = [
                    'id' => $municipality['id'],
                    'provinceId' => $municipality['province_id'],
                    'regionId' => $municipality['region_id'],
                    'provinceName' => $municipality['province_name'],
                    'regionName' => $municipality['region_name'],
                    'name' => $municipality['name'],
                    'code' => $municipality['code'],
                    'createdAt' => $municipality['created_at']
                ];
            }
            
            $response['pagination'] = [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => ceil($total / $perPage)
            ];
        }

        // Get counts for all types
        $regionCount = getRow($pdo, "SELECT COUNT(*) as total FROM regions");
        $provinceCount = getRow($pdo, "SELECT COUNT(*) as total FROM provinces");
        $municipalityCount = getRow($pdo, "SELECT COUNT(*) as total FROM municipalities");
        
        $response['counts'] = [
            'regions' => $regionCount['total'] ?? 0,
            'provinces' => $provinceCount['total'] ?? 0,
            'municipalities' => $municipalityCount['total'] ?? 0
        ];

    } elseif ($action === 'regions') {
        // Get all regions for dropdowns
        $regions = getAll($pdo, "SELECT id, name FROM regions ORDER BY name");
        $response = ['success' => true, 'data' => $regions];

    } elseif ($action === 'provinces') {
        // Get provinces for a specific region
        $regionId = $_GET['regionId'] ?? null;
        if ($regionId) {
            $provinces = getAll($pdo, "SELECT id, name FROM provinces WHERE region_id = ? ORDER BY name", [$regionId]);
            $response = ['success' => true, 'data' => $provinces];
        } else {
            $response = ['success' => false, 'data' => []];
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch data']);
    error_log("Address fetch error: " . $e->getMessage());
}
?>
