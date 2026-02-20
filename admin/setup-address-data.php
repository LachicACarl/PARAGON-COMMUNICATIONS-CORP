<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

session_start();

// Check authentication
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Check admin access
if (getCurrentRole() !== 'admin' && getCurrentRole() !== 'head_admin') {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Clear existing data
        $pdo->exec("DELETE FROM municipalities");
        $pdo->exec("DELETE FROM provinces");
        $pdo->exec("DELETE FROM regions");
        
        // Sample Philippines regions, provinces, and municipalities
        $sampleData = [
            ['region' => 'NCR (National Capital Region)', 'code' => 'NCR', 'provinces' => [
                ['name' => 'Metro Manila', 'code' => 'MM', 'municipalities' => [
                    'Quezon City', 'Manila', 'Caloocan', 'Las Piñas', 'Makati', 'Marikina', 'Muntinlupa', 'Pateros', 'Pasay', 'Pasig', 'San Juan', 'Taguig'
                ]]
            ]],
            ['region' => 'Region I (Ilocos Region)', 'code' => 'I', 'provinces' => [
                ['name' => 'Ilocos Norte', 'code' => 'IN', 'municipalities' => ['Laoag', 'Batac', 'Paoay', 'Pagudpud', 'San Nicolas']],
                ['name' => 'Ilocos Sur', 'code' => 'IS', 'municipalities' => ['Vigan', 'Candon', 'Tagudin', 'Santa Cruz', 'Caoayan']],
                ['name' => 'La Union', 'code' => 'LU', 'municipalities' => ['San Fernando', 'Dagupan', 'Bauang', 'Santol', 'Aringay']]
            ]],
            ['region' => 'Region II (Cagayan Valley)', 'code' => 'II', 'provinces' => [
                ['name' => 'Cagayan', 'code' => 'CG', 'municipalities' => ['Tuguegarao City', 'Cabanatuan', 'Gattaran', 'Amumuran', 'Baggao']],
                ['name' => 'Isabela', 'code' => 'IB', 'municipalities' => ['Santiago', 'Echague', 'San Mateo', 'Alicia', 'Bislig']],
                ['name' => 'Nueva Vizcaya', 'code' => 'NV', 'municipalities' => ['Bayombong', 'Cabarroguis', 'Guimba', 'Bambang', 'Solano']]
            ]],
            ['region' => 'Region III (Central Luzon)', 'code' => 'III', 'provinces' => [
                ['name' => 'Nueva Ecija', 'code' => 'NE', 'municipalities' => ['Cabanatuan', 'San Fernando', 'Gapan', 'Talugtug', 'Aliaga']],
                ['name' => 'Bulacan', 'code' => 'BL', 'municipalities' => ['Malolos', 'Meycauayan', 'Valenzuela', 'Sta. Maria', 'Pandi']],
                ['name' => 'Pampanga', 'code' => 'PM', 'municipalities' => ['San Fernando', 'Angeles', 'Apalit', 'Guagua', 'Bacolor']]
            ]]
        ];

        foreach ($sampleData as $regionData) {
            // Insert region
            $stmt = $pdo->prepare("INSERT INTO regions (name, code) VALUES (?, ?)");
            $stmt->execute([$regionData['region'], $regionData['code']]);
            $regionId = $pdo->lastInsertId();

            // Insert provinces
            foreach ($regionData['provinces'] as $provinceData) {
                $stmt = $pdo->prepare("INSERT INTO provinces (region_id, name, code) VALUES (?, ?, ?)");
                $stmt->execute([$regionId, $provinceData['name'], $provinceData['code']]);
                $provinceId = $pdo->lastInsertId();

                // Insert municipalities
                foreach ($provinceData['municipalities'] as $municipalityName) {
                    $stmt = $pdo->prepare("INSERT INTO municipalities (region_id, province_id, name) VALUES (?, ?, ?)");
                    $stmt->execute([$regionId, $provinceId, $municipalityName]);
                }
            }
        }

        $success = true;
        $message = 'Sample address data loaded successfully! ' . count($sampleData) . ' regions with provinces and municipalities have been added.';
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Address Data - Paragon</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .material-icons {
            font-family: 'Material Icons';
            font-size: 24px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">

<div class="bg-white rounded-lg shadow-2xl p-8 w-full max-w-md">
    <div class="text-center mb-8">
        <span class="material-icons text-5xl text-blue-600 block mb-4">location_on</span>
        <h1 class="text-2xl font-bold text-gray-800">Address Data Setup</h1>
        <p class="text-gray-600 text-sm mt-2">Initialize sample region, province, and municipality data</p>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?>">
            <p class="<?php echo $success ? 'text-green-700' : 'text-red-700'; ?> text-sm">
                <span class="material-icons text-lg inline mr-2"><?php echo $success ? 'check_circle' : 'error'; ?></span>
                <?php echo $message; ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="font-semibold text-blue-900 mb-2">What will be loaded:</h3>
        <ul class="text-sm text-blue-800 space-y-1">
            <li>✓ 4 Regions (NCR, Region I, II, III)</li>
            <li>✓ 11 Provinces across all regions</li>
            <li>✓ 50+ Municipalities and cities</li>
        </ul>
    </div>

    <form method="POST" class="space-y-4">
        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all flex items-center justify-center gap-2">
            <span class="material-icons">download</span>
            Load Sample Address Data
        </button>

        <a href="address-management.php" class="w-full block text-center bg-gray-200 text-gray-800 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-all">
            View Address Management
        </a>
    </form>

    <p class="text-xs text-gray-500 text-center mt-6">
        This will create sample data for demonstration purposes. You can add more regions and municipalities later through the management panel.
    </p>
</div>

</body>
</html>
