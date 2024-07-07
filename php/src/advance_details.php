<?php
session_start();
include('db.php');

// Check session and role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'superadmin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['customer_id'])) {
    header("Location: advance_payment.php");
    exit();
}

$customer_id = $_GET['customer_id'];

// Fetch customer details
$customer_query = "SELECT customer.name, customer_advance.advance_amount, customer_advance.used_amount, 
                   customer_advance.balance_amount, DATE_FORMAT(customer_advance.advance_date, '%d-%m-%Y') AS advance_date
                   FROM customer 
                   LEFT JOIN customer_advance ON customer.customer_id = customer_advance.customer_id 
                   WHERE customer.customer_id = ?";
$stmt = $conn->prepare($customer_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer_result = $stmt->get_result();
$customer = $customer_result->fetch_assoc();
$stmt->close();

// Fetch usage details
$usage_query = "SELECT DATE_FORMAT(usage_date, '%d-%m-%Y') AS usage_date, amount_used 
                FROM customer_usage 
                WHERE customer_id = ? ORDER BY usage_date ASC";
$stmt = $conn->prepare($usage_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$usage_result = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Advance Payment Details</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        body {
    font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; 
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
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

.content {
    margin-left: 220px; /* Ensuring the content starts after the sidebar */
    padding: 20px;
    box-sizing: border-box;
    width: calc(100% - 220px); /* Making sure the content takes up the remaining width */
}

    </style>
</head>

<body>
<div class="sidebar">
    <h2>Bloom Software</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="flowers.php">Flowers</a>
    <a href="billing.php">Billing</a>
    <a href="customer.php">Customers</a>
    <?php if ($_SESSION['role'] == 'superadmin') { ?>
        <a href="advance_payment.php">Advance Payment</a>
        <a href="lease_advance_payment.php">Lease Advance</a>
        <a href="settings.php">Settings</a>
    <?php } ?>
    <a href="invoice_form.php">Generate Invoice</a>
    <a href="transaction_report.php">Transaction Report</a>
    <a href="logout.php">Logout</a>
</div>
<div class="content">
    <header>
        <h1>Advance Payment Details</h1>
    </header>
    <div class="advance-details">
        <h2><?php echo $customer['name']; ?></h2>
        <p>Advance Amount: <?php echo $customer['advance_amount']; ?></p>
        <p>Used Amount: <?php echo $customer['used_amount']; ?></p>
        <p>Balance Amount: <?php echo $customer['balance_amount']; ?></p>
        <p>Advance Date: <?php echo $customer['advance_date']; ?></p>
        
        <h3>Usage Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount Used</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = $usage_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['usage_date']; ?></td>
                        <td><?php echo $row['amount_used']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            
        </table>
        <button type="button" onclick="window.location.href='advance_payment.php'" class="button center-button">BACK</button>
    </div>
</div>
</body>
</html>
