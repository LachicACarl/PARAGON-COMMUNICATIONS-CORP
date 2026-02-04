<?php
session_start();

/* TEMPORARY USER NAME (You can replace later with database) */
$username = "Head Admin";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PARAGON Dashboard</title>
<link rel="stylesheet" href="dashboard.css">
</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->
    <div class="sidebar">

        <h2 class="logo">PARAGON</h2>

        <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">User Management</a></li>
            <li><a href="#">Masterlist Monitoring</a></li>
            <li><a href="#">Dormant Call Outs</a></li>
            <li><a href="#">Reports</a></li>
            <li><a href="#">Logout</a></li>
        </ul>

    </div>

    <!-- MAIN CONTENT -->
    <div class="main">

        <!-- HEADER -->
        <div class="header">
            <h1>Welcome, <?php echo $username; ?></h1>
        </div>

        <!-- STAT CARDS -->
        <div class="cards">

            <div class="card">
                <h3>Total Customers</h3>
                <p>120</p>
            </div>

            <div class="card">
                <h3>Dormant Accounts</h3>
                <p>25</p>
            </div>

            <div class="card">
                <h3>Total Amount Paid</h3>
                <p>₱350,000</p>
            </div>

            <div class="card">
                <h3>Installations</h3>
                <p>90</p>
            </div>

        </div>

        <!-- MONITORING TABLE -->
        <div class="table-section">

            <h2>Customer Monitoring</h2>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Address</th>
                    <th>Amount Paid</th>
                    <th>Call Out Status</th>
                    <th>Main Remarks</th>
                </tr>

                <tr>
                    <td>1</td>
                    <td>Quezon City</td>
                    <td>₱10,000</td>
                    <td>Active</td>
                    <td>Completed</td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>Manila</td>
                    <td>₱8,000</td>
                    <td>Dormant</td>
                    <td>Follow Up</td>
                </tr>

            </table>

        </div>

    </div>

</div>

</body>
</html>
