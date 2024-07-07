<?php
session_start();
include('db.php');
// Check if user is authorized (example assumes 'superadmin' role)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    echo "You are not authorized to view this page.";
    exit; // Stop further execution
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables to store invoice data
$invoice_id = $_GET['invoice_id']; // Replace with appropriate method based on your application
$invoice_data = [];

// Prepare SQL query to fetch invoice details
$sql = "SELECT i.*, c.name AS customer_name
        FROM invoices i
        INNER JOIN customer c ON i.customer_id = c.customer_id
        WHERE i.invoice_id = ?";

// Prepare and bind parameter
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id); // Assuming invoice_id is an integer
$stmt->execute();

// Bind result variables
$stmt->bind_result($invoice_id, $customer_id, $location, $start_date, $end_date, $total_amount,
                   $total_commission, $total_luggage_cost, $advance_used, $net_paid, $created_at, $customer_name);

// Fetch result
$stmt->fetch();

// Store data in an array for easier handling in HTML
$invoice_data = [
    'Invoice ID' => $invoice_id,
    'Customer Name' => $customer_name,
    'Location' => $location,
    'Start Date' => $start_date,
    'End Date' => $end_date,
    'Total Amount' => $total_amount,
    'Total Commission' => $total_commission,
    'Total Luggage Cost' => $total_luggage_cost,
    'Advance Used' => $advance_used,
    'Net Paid' => $net_paid,
    'Created At' => $created_at
];

// Close statement
$stmt->close();

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Invoice Details</title>
    <style>
        
        .content {
            margin-left: 220px;
            padding: 20px;
        }
        header h1 {
            color: #2980b9;
        }
       
        
        header h1 {
            color: #2980b9;
        }
        .top-options {
            display: flex;
            align-items: center;
            gap: 10px;
        }
       th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th:first-child, td:first-child {
            width: 30%;
        }
        th:nth-child(2), td:nth-child(2) {
            width: 20%;
        }
        th:nth-child(3), td:nth-child(3) {
            width: 20%;
            text-align: center;
        }
        th:nth-child(4), td:nth-child(2) {
            width: 30%;
        }
        
        .notification {
            color: white;
            padding: 10px;
            margin: 10px 0;
            display: none;
            border-radius: 4px;
        }
        .success {
            background-color: green;
        }
        .error {
            background-color: red;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f0f0f0;
            padding: 20px;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .invoice-container table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .invoice-container table th,
        .invoice-container table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .invoice-container table th {
            background-color: #f2f2f2;
        }
        .invoice-container .download-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: blue;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .invoice-container .download-btn:hover {
            background-color: #45a049;
        }
        th {
            background-color: blue;
            color: black;
        }
        
    </style>
</head>
<body>
<div class="sidebar">
        <h2>Bloom Software</h2>
        
        <a href="flowers.php">Flowers</a>
        <a href="billing.php">Billing</a>
        <a href="customer.php">Customers</a>
        <?php if ($_SESSION['role'] == 'superadmin') { ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="advance_payment.php">Advance Payment</a>
            <a href="lease_advances.php">Lease Advance</a>
            <a href="settings.php">Settings</a>
        <?php } ?>
        <a href="generate_invoice1.php">Generate Invoice</a>
        <a href="transaction_report.php">Transaction Report</a>
        <a href="logout.php">Logout</a>
    </div>
    
    <div class="invoice-container">
        <h2>Invoice Details</h2>
        <?php if (!empty($invoice_data)): ?>
        <table>
            <tr>
                <th>Field</th>
                <th>Value</th>
            </tr>
            <?php foreach ($invoice_data as $field => $value): ?>
            <tr>
                <td><?php echo htmlspecialchars($field); ?></td>
                <td><?php echo htmlspecialchars($value); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <a href="download_invoice.php?invoice_id=<?php echo urlencode($invoice_id); ?>" class="download-btn">Download Invoice</a>
        <?php else: ?>
        <p>Invoice not found</p>
        <?php endif; ?>
    </div>
</body>
</html>
