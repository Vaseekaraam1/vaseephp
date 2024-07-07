<?php
session_start();
include('db.php'); // Adjust the path based on your file structure

// Check session and role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'superadmin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Advance ID is required");
}

$advance_id = $_GET['id'];

// Query to fetch advance payment details from customer_advance table
$stmt_advance = $conn->prepare("SELECT ca.advance_id, ca.advance_date, c.name, ca.advance_amount, ca.used_amount, ca.balance_amount, ca.customer_id
                        FROM customer_advance ca
                        JOIN customer c ON ca.customer_id = c.customer_id
                        WHERE ca.advance_id = ?");
$stmt_advance->bind_param("i", $advance_id);

if ($stmt_advance->execute()) {
    $result_advance = $stmt_advance->get_result();
    if ($result_advance->num_rows > 0) {
        $row = $result_advance->fetch_assoc();
    } else {
        die("No advance payment found for ID: " . htmlspecialchars($advance_id));
    }
} else {
    die("Error fetching advance payment details: " . $stmt_advance->error);
}

$stmt_advance->close();

// Query to fetch usage history from invoices table
$stmt_usage = $conn->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m-%d') AS usage_date, advance_used AS used_amount
                        FROM invoices
                        WHERE customer_id = ? AND advance_used > 0");
$stmt_usage->bind_param("i", $row['customer_id']);

if ($stmt_usage->execute()) {
    $result_usage = $stmt_usage->get_result();
    $usage_history = [];
    while ($usage_row = $result_usage->fetch_assoc()) {
        $usage_history[] = $usage_row;
    }
} else {
    die("Error fetching usage history: " . $stmt_usage->error);
}

$stmt_usage->close();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Advance Payment Details</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .content {
            margin-left: 200px; /* Sidebar width */
            padding: 20px;
        }
        .details-box {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .details-box h2 {
            margin-top: 0;
        }
        .details-box p {
            margin: 5px 0;
        }
        .details-box .info {
            display: flex;
            justify-content: space-between;
        }
        .table-container {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        /* Alternating row colors */
        table tbody tr:nth-child(even) {
            background-color: #f2f2f2; /* Light color for even rows */
        }
        table tbody tr:nth-child(odd) {
            background-color: #e9e9e9; /* Very light color for odd rows */
        }
          
     body {
    font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; 
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
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
        <a href="lease_advances.php">Lease Advance</a>
        <a href="settings.php">Settings</a>
    <?php } ?>
    <a href="invoice_form.php">Generate Invoice</a>
    <a href="transaction_report.php">Transaction Report</a>
    <a href="logout.php">Logout</a>
</div>
<div class="content">
    <div class="details-box">
        <h2>Advance Payment Details - <?= htmlspecialchars($row['name']) ?></h2>
        <div class="info">
            <div>
                <p><strong>Advance Amount:</strong> <?= htmlspecialchars($row['advance_amount']) ?></p>
                <p><strong>Used Amount:</strong> <?= htmlspecialchars($row['used_amount']) ?></p>
                <p><strong>Balance Amount:</strong> <?= htmlspecialchars($row['balance_amount']) ?></p>
            </div>
            <div>
                <p><strong>Advance Date:</strong> <?= htmlspecialchars($row['advance_date']) ?></p>
            </div>
        </div>
    </div>

    <div class="table-container">
        <h2>Usage History</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Used Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usage_history as $index => $usage): ?>
                    <tr>
                        <td><?= htmlspecialchars($usage['usage_date']) ?></td>
                        <td><?= htmlspecialchars($usage['used_amount']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
