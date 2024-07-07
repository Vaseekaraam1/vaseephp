<?php
session_start();
include('db.php'); // Adjust the path to match your db.php location

// Check session
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch today's total from billing table
function fetchTodayTotal($conn, $column) {
    $today = date('Y-m-d');
    $sql = "SELECT SUM($column) AS total FROM billing WHERE DATE(created_at) = '$today'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    } else {
        return 0.00;
    }
}

// Function to fetch month-to-date total from billing table
function fetchMonthTotal($conn, $column) {
    $current_month = date('Y-m-01');
    $sql = "SELECT SUM($column) AS total FROM billing WHERE created_at >= '$current_month'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    } else {
        return 0.00;
    }
}

// Function to fetch total quantity and net paid amount for each location for each day
function fetchDailyTotals($conn) {
    $today = date('Y-m-d');
    $sql = "
        SELECT location, SUM(quantity) AS total_quantity, SUM(netpaid) AS total_netpaid
        FROM billing
        WHERE DATE(created_at) = '$today'
        GROUP BY location
    ";
    $result = $conn->query($sql);

    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

// Function to fetch monthly total net paid for each location
function fetchMonthlyNetPaidTotals($conn) {
    $sql = "
        SELECT location, SUM(netpaid) AS monthly_netpaid
        FROM billing
        WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
        GROUP BY location
    ";
    $result = $conn->query($sql);

    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[$row['location']] = $row['monthly_netpaid'];
        }
    }
    return $data;
}

// Fetch today's total revenue, net paid, and commission
$today_revenue = fetchTodayTotal($conn, 'total');
$today_netpaid = fetchTodayTotal($conn, 'netpaid');
$today_commission = fetchTodayTotal($conn, 'commission_amount');

// Fetch month-to-date total revenue, net paid, and commission
$month_revenue = fetchMonthTotal($conn, 'total');
$month_netpaid = fetchMonthTotal($conn, 'netpaid');
$month_commission = fetchMonthTotal($conn, 'commission_amount');

// Fetch daily totals
$daily_totals = fetchDailyTotals($conn);

// Fetch monthly net paid totals for each location
$monthly_netpaid_totals = fetchMonthlyNetPaidTotals($conn);

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        /* Your custom CSS styles here */
        .content {
            margin-left: 220px;
            padding: 20px;
            box-sizing: border-box;
            width: calc(100% - 220px);
        }

        .header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        body {
    font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; 
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
}


        .header-box {
            width: calc(33.333% - 20px);
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .header-box:hover {
            transform: scale(1.05);
            background-color: #e0f7fa;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
    width: 80%; /* Adjust the width as needed */
    margin: 20px auto; /* Center the table with margin */
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 10px;
    text-align: center;
    border: 1px solid #ddd;
}

th {
    background-color: #133b68;
    color: #fff;
}

    </style>
</head>
<body>
    <!-- Sidebar -->
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

    <!-- Content -->
    <div class="content">
        <header>
            <h1 style="color: #2980b9;">Dashboard</h1>
            <button type="button" onclick="window.location.href='index.html'" class="button">View flower report</button>
        </header>

        <!-- Header with dynamic data -->
        <div class="header">
            <div class="header-box">
                <h3>Total Revenue of Today</h3>
                <p id="todayRevenue">Loading...</p>
            </div>
        
            <div class="header-box">
                <h3>Total Net Paid of Today</h3>
                <p id="todayNetPaid">Loading...</p>
            </div>
            <div class="header-box">
                <h3>Total Commission of Today</h3>
                <p id="todayCommission">Loading...</p>
            </div>
            
            <div class="header-box">
                <h3>Total Revenue for Month</h3>
                <p id="monthRevenue">Loading...</p>
            </div>
            <div class="header-box">
                <h3>Total Net Paid for Month</h3>
                <p id="monthNetPaid">Loading...</p>
            </div>
            <div class="header-box">
                <h3>Total Commission for Month</h3>
                <p id="monthCommission">Loading...</p>
            </div>
        </div>

        <!-- Daily Totals Table -->
        <h2 style="text-align: center; color: #2980b9;">Totals for Each Location</h2>
        <table>
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Total Quantity Sold</th>
                    <th>Total Net Paid (Today)</th>
                    <th>Total Net Paid (This Month)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daily_totals as $total) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($total['location']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($total['total_quantity'], 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($total['total_netpaid'], 2)); ?></td>
                        <td><?php echo isset($monthly_netpaid_totals[$total['location']]) ? htmlspecialchars(number_format($monthly_netpaid_totals[$total['location']], 2)) : '0.00'; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- JavaScript for dynamic data -->
        <script>
           // Fetch data from PHP variables
var todayRevenue = "<?php echo number_format($today_revenue, 2); ?>";
var todayNetPaid = "<?php echo number_format($today_netpaid, 2); ?>";
var todayCommission = "<?php echo number_format($today_commission, 2); ?>";
var monthRevenue = "<?php echo number_format($month_revenue, 2); ?>";
var monthNetPaid = "<?php echo number_format($month_netpaid, 2); ?>";
var monthCommission = "<?php echo number_format($month_commission, 2); ?>";

// Update HTML content with fetched data
document.getElementById('todayRevenue').textContent = '₹' + todayRevenue;
document.getElementById('todayNetPaid').textContent = '₹' + todayNetPaid;
document.getElementById('todayCommission').textContent = '₹' + todayCommission;
document.getElementById('monthRevenue').textContent = '₹' + monthRevenue;
document.getElementById('monthNetPaid').textContent = '₹' + monthNetPaid; // Ensure this corresponds to the correct element in your HTML
document.getElementById('monthCommission').textContent = '₹' + monthCommission;

</script>
</div>
</body>
</html>

