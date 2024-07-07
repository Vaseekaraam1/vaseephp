<?php
session_start();
include('db.php'); // Adjust the path as necessary

// Check session
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch total quantity sold today for all locations
$today = date('Y-m-d');
$sql = "SELECT SUM(quantity) AS total_quantity FROM billing WHERE DATE(created_at) = '$today'";
$result = $conn->query($sql);
$totalQuantity = 0;
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalQuantity = $row['total_quantity'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Total Quantity Sold Today</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .content {
            margin-left: 220px;
            padding: 20px;
        }
        header h1 {
            color: #2980b9;
        }
        .total-quantity {
            font-size: 24px;
            margin-top: 20px;
        }
        
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 200px;
    height: 100%;
    background-color: #012652; /* Sidebar color */
    color: #fff;
    padding: 20px;
    box-sizing: border-box; /* Consistent padding behavior */
}

.sidebar h2 {
    text-align: center;
}

.sidebar a {
    display: block;
    color: #fff;
    text-decoration: none;
    margin: 10px 0;
    padding: 10px;
    background-color: #1f497d; /* Link background color */
}

.sidebar a:hover {
    background-color: #133b68; /* Link hover color */
    text-decoration: none;
}
    </style>
</head>
<body>
    <div class="content">
        <header>
            <h1>Total Quantity Sold Today</h1>
        </header>
        <div class="sidebar">
            <h2>Bloom Software</h2>
            <a href="dashboard.php">Dashboard</a>
            <a href="flowers.php">Flowers</a>
            <a href="billing.php">Billing</a>
            <a href="customer.php">Customers</a>
            <?php if ($_SESSION['role'] == 'superadmin') { ?>
                <a href="advance_payment.php">Advance Payment</a>
                <a href="lease_advances.php">Lease Advance</a>
                <a href="settings.php">Settings</a>
            <?php } ?>
            <a href="invoice_form.php">Generate Invoice</a>
            <a href="transaction_report.php">Transaction Report</a>
            <a href="logout.php">Logout</a>
        </div>
        <div class="total-quantity">
            <p>Total Quantity Sold Today: <?php echo $totalQuantity; ?></p>
        </div>
        <button type="button" onclick="window.location.href='dashboard.php'" class="back-button">BACK</button>
    </div>
</body>
</html>
