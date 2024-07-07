<?php
session_start();
include('db.php');

// Check session and role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'superadmin') {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle form submission to add new advance payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['customer_id'])) {
    $customer_id = $_POST['customer_id'];
    $amount = $_POST['amount'];
    $date = date('Y-m-d', strtotime($_POST['date']));
    $used_amount = 0;

    // Insert new advance payment
    $stmt = $conn->prepare("INSERT INTO customer_advance (customer_id, advance_amount, used_amount, advance_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idds", $customer_id, $amount, $used_amount, $date);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Advance payment added successfully.";
        // Redirect to prevent form re-submission
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    } else {
        $_SESSION['message'] = "Error adding advance payment: " . $stmt->error;
    }
    $stmt->close();
}

// Handle form submission to edit advance payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    $amount = $_POST['edit_amount'];
    $date = date('Y-m-d', strtotime($_POST['edit_date']));

    // Update advance payment
    $stmt = $conn->prepare("UPDATE customer_advance SET advance_amount = ?, advance_date = ? WHERE advance_id = ?");
    $stmt->bind_param("dsi", $amount, $date, $edit_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Advance payment updated successfully.";
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    } else {
        $_SESSION['message'] = "Error updating advance payment: " . $stmt->error;
    }
    $stmt->close();
}

// Handle deletion of advance payment
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Delete advance payment
    $stmt = $conn->prepare("DELETE FROM customer_advance WHERE advance_id = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Advance payment deleted successfully.";
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    } else {
        $_SESSION['message'] = "Error deleting advance payment: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch customers for dropdown
$customers_result = $conn->query("SELECT customer_id, name FROM customer");
if (!$customers_result) {
    die("Query failed: " . $conn->error);
}

// Fetch all advance payments
$advances_result = $conn->query("SELECT ca.advance_id, ca.advance_date, c.name, ca.advance_amount, ca.used_amount, ca.balance_amount
                                 FROM customer_advance ca
                                 JOIN customer c ON ca.customer_id = c.customer_id");
if (!$advances_result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Advance Payment Management</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #fff;
    padding: 10px;
    border-bottom: 1px solid #ccc;
}

header h1 {
    margin: 0;
}               
header h1 {
            color: #2980b9;
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
.cd {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #fff;
    padding: 10px;
    border-bottom: 1px solid #ccc;
}   

.cd h1 {
    margin: 0;
}

.cd h1 {
    color: #2980b9;
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
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .edit-button, .delete-button, .view-button {
            margin: 0 5px;
        }
        .delete-button {
            color: red;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: palegreen;
            color: #333;
        }
        th {
            background-color: #133b68;
            color: #fff;
        }
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

.table {
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#date", {
                dateFormat: "Y-m-d",
                defaultDate: new Date()
            });

            var addModal = document.getElementById("addModal");
            var editModal = document.getElementById("editModal");
            var deleteModal = document.getElementById("deleteModal");

            var btn = document.getElementById("myBtn");
            var addClose = document.getElementsByClassName("close-add")[0];
            var editClose = document.getElementsByClassName("close-edit")[0];
            var deleteClose = document.getElementsByClassName("close-delete")[0];

            btn.onclick = function() {
                addModal.style.display = "block";
            }

            addClose.onclick = function() {
                addModal.style.display = "none";
            }

            editClose.onclick = function() {
                editModal.style.display = "none";
            }

            deleteClose.onclick = function() {
                deleteModal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == addModal) {
                    addModal.style.display = "none";
                }
                if (event.target == editModal) {
                    editModal.style.display = "none";
                }
                if (event.target == deleteModal) {
                    deleteModal.style.display = "none";
                }
            }

            flatpickr("#edit_date", {
                dateFormat: "Y-m-d"
            });

            document.querySelectorAll('.edit-button').forEach(button => {
                button.addEventListener('click', function() {
                    var row = this.parentElement.parentElement;
                    document.getElementById('edit_id').value = row.dataset.id;
                    document.getElementById('edit_date').value = row.querySelector('.advance_date').innerText;
                    document.getElementById('edit_amount').value = row.querySelector('.advance_amount').innerText;
                    editModal.style.display = 'block';
                });
            });

            document.querySelectorAll('.delete-button').forEach(button => {
                button.addEventListener('click', function() {
                    var row = this.parentElement.parentElement;
                    document.getElementById('delete_id').value = row.dataset.id;
                    deleteModal.style.display = 'block';
                });
            });

            document.querySelectorAll('.view-button').forEach(button => {
                button.addEventListener('click', function() {
                    var row = this.parentElement.parentElement;
                    var advanceId = row.dataset.id;

                    // Redirect to view_advance_payment.php with advance_id as query parameter
                    window.location.href = 'view_advance_payment.php?id=' + advanceId;
                });
            });

            // Initialize Select2
            $('#customer').select2({
                width: '100%'  // Set constant width
            });
        });
    </script>
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
    
<div class="content">
<div class="cd"> <h1 style="color: #2980b9;">Advance Payment Management </h1> </div>
    <?php
    // Display session message if exists
    if (isset($_SESSION['message'])) {
        echo '<div class="message">' . $_SESSION['message'] . '</div>';
        unset($_SESSION['message']); // Clear the message after displaying once
    }
    ?>
    <!-- Trigger/Open The Modal -->
    <button id="myBtn">Add Advance Payment</button>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close close-add">&times;</span>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="customer">Customer:</label>
                    <select name="customer_id" id="customer">
                        <?php while ($row = $customers_result->fetch_assoc()) { ?>
                            <option value="<?= $row['customer_id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount">Advance Amount:</label>
                    <input type="number" step="0.01" name="amount" id="amount" required>
                </div>
                <div class="form-group">
                    <label for="date">Advance Date:</label>
                    <input type="text" name="date" id="date" required>
                </div>
                <button type="submit" class="button">Save</button>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close close-edit">&times;</span>
            <form method="POST" action="">
                <input type="hidden" name="edit_id" id="edit_id">
                <div class="form-group">
                    <label for="edit_date">Advance Date:</label>
                    <input type="text" name="edit_date" id="edit_date" required>
                </div>
                <div class="form-group">
                    <label for="edit_amount">Advance Amount:</label>
                    <input type="number" step="0.01" name="edit_amount" id="edit_amount" required>
                </div>
                <button type="submit" class="button">Save</button>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close close-delete">&times;</span>
            <form method="GET" action="">
                <input type="hidden" name="delete_id" id="delete_id">
                <p>Are you sure you want to delete this advance payment?</p>
                <button type="submit" class="button">Yes, Delete</button>
            </form>
        </div>
    </div>

    <h2>Advance Payments</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Customer Name</th>
                <th>Advance Amount</th>
                <th>Used Amount</th>
                <th>Balance Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $advances_result->fetch_assoc()) { ?>
                <tr data-id="<?= $row['advance_id'] ?>">
                    <td class="advance_date"><?= htmlspecialchars($row['advance_date']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td class="advance_amount"><?= htmlspecialchars($row['advance_amount']) ?></td>
                    <td><?= htmlspecialchars($row['used_amount']) ?></td>
                    <td><?= htmlspecialchars($row['balance_amount']) ?></td>
                    <td>
                        <a href="view_advance_payment.php?id=<?= $row['advance_id'] ?>" class="view-button">View</a>
                        <button class="edit-button">Edit</button>
                        <button class="delete-button">Delete</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
