<?php

require '../config/database.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if(isset($_FILES['excel_file'])){

    // Validate file type
    $fileType = pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION);

    if($fileType != "xlsx"){
        die("Only XLSX files allowed!");
    }

    $file = $_FILES['excel_file']['tmp_name'];

    try {

        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Remove header row
        array_shift($rows);

        foreach($rows as $row){

            // Skip empty rows
            if(empty($row[0])) continue;

            $address = $row[0];
            $amount_paid = floatval($row[1]);
            $installation_fee = floatval($row[2]);
            $callout_status = $row[3];
            $pullout_remarks = $row[4];
            $status_channel = $row[5];
            $sales_category = $row[6];
            $main_remarks = $row[7];

            $stmt = $pdo->prepare("
                INSERT INTO customers 
                (address, amount_paid, installation_fee, callout_status, pullout_remarks, status_channel, sales_category, main_remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $address,
                $amount_paid,
                $installation_fee,
                $callout_status,
                $pullout_remarks,
                $status_channel,
                $sales_category,
                $main_remarks
            ]);
        }

        echo "<h3>Excel Imported Successfully!</h3>";

    } catch(Exception $e){
        echo "Error: " . $e->getMessage();
    }
}
