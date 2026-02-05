<!DOCTYPE html>
<html>
<head>
    <title>Upload Excel</title>
</head>
<body>

<h2>Upload Converge Masterlist</h2>

<form action="process_excel.php" method="POST" enctype="multipart/form-data">
    <input type="file" name="excel_file" accept=".xlsx" required>
    <br><br>
    <button type="submit">Upload Excel</button>
</form>

</body>
</html>

