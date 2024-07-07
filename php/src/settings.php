<?php
session_start();
include('db.php'); // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Function to fetch all admin users
function fetchAdmins($conn) {
    $sql = "SELECT user_id, username FROM users WHERE role = 'admin'";
    $result = $conn->query($sql);
    $admins = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
    }
    return $admins;
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        // Handle addition of admin
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Example validation (you should validate and sanitize inputs properly)
        if (!empty($username) && !empty($password)) {
            // Example SQL query (make sure to use prepared statements for security)
            $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'admin')";
            if ($conn->query($sql) === TRUE) {
                $_SESSION['notification'] = "Admin user added successfully.";
                $_SESSION['notification_type'] = "success";
            } else {
                $_SESSION['notification'] = "Error adding admin user: " . $conn->error;
                $_SESSION['notification_type'] = "error";
            }
        } else {
            $_SESSION['notification'] = "Username and password are required.";
            $_SESSION['notification_type'] = "error";
        }
    } elseif ($action === 'edit') {
        // Handle editing of admin
        $admin_id = $_POST['admin_id'];
        $newUsername = $_POST['username'];
        $newPassword = $_POST['password'];

        // Example validation and SQL update query
        if (!empty($newUsername) && !empty($newPassword)) {
            $sql = "UPDATE users SET username = '$newUsername', password = '$newPassword' WHERE user_id = '$admin_id'";
            if ($conn->query($sql) === TRUE) {
                $_SESSION['notification'] = "Admin user edited successfully.";
                $_SESSION['notification_type'] = "success";
            } else {
                $_SESSION['notification'] = "Error editing admin user: " . $conn->error;
                $_SESSION['notification_type'] = "error";
            }
        } else {
            $_SESSION['notification'] = "Username and password are required.";
            $_SESSION['notification_type'] = "error";
        }
    } elseif ($action === 'delete') {
        // Handle deletion of admin
        $admin_id = $_POST['admin_id'];

        // Example deletion logic
        $sql = "DELETE FROM users WHERE user_id = '$admin_id'";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['notification'] = "Admin user deleted successfully.";
            $_SESSION['notification_type'] = "success";
        } else {
            $_SESSION['notification'] = "Error deleting admin user: " . $conn->error;
            $_SESSION['notification_type'] = "error";
        }
    }

    // Redirect back to settings page
    header("Location: settings.php");
    exit();
}

// Fetch all admin users
$admins = fetchAdmins($conn);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link your external CSS file -->
</head>
<style>
    body {
    font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; 
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
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
    margin-bottom: 20px;
}

.sidebar a {
    display: block;
    color: #fff;
    text-decoration: none;
    margin: 10px 0;
    padding: 10px;
    background-color: #1f497d; /* Link background color */
    transition: background-color 0.3s;
}

.sidebar a:hover {
    background-color: #133b68; /* Link hover color */
    text-decoration: none;
}

.content {
    margin-left: 220px; /* Adjusted for sidebar width */
    padding: 20px;
    box-sizing: border-box;
    width: calc(100% - 220px); /* Making sure the content takes up the remaining width */
}

header h1 {
    color: #2980b9;
}

button {
    padding: 8px 15px;
    margin: 5px 0;
    background-color: #012652; /* Button color */
    color: #fff;
    border: none;
    cursor: pointer;
}

button:hover {
    background-color: #133b68; /* Button hover color */
}

input[type="text"], input[type="number"], input[type="date"], select {
    padding: 10px;
    margin: 10px 0;
    width: 100%;
    box-sizing: border-box;
}


/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 30%; /* Adjust the modal width here */
    height: auto; /* Adjust the modal height here */
    max-width: 500px; /* Set maximum width */
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);
    animation-name: modalopen;
    animation-duration: 0.4s;
}

@keyframes modalopen {
    from {opacity: 0}
    to {opacity: 1}
}

.close {
    color: #aaaaaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}

/* Table styling */
.admin-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; /* Fixed table layout */
    margin-top: 20px;
}

.admin-table th, .admin-table td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.admin-table th {
    background-color: #133b68;
    color: #fff;
}

.admin-table td:first-child {
    width: 15%; /* Adjust width for Username column */
}

.admin-table td:nth-child(2) {
    width: 60%; /* Adjust width for Edit column */
    text-align: center;
}

.admin-table td:nth-child(3) {
    width: 15%; /* Adjust width for Delete column */
    text-align: center;
}

.admin-table td form {
    display: flex;
    align-items: center;
}

.admin-table td form input[type="text"],
.admin-table td form input[type="password"] {
    width: 100%; /* Full width input fields in forms */
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .admin-table td form input[type="text"],
    .admin-table td form input[type="password"] {
        width: 100%; /* Full width input fields on smaller screens */
    }
}


</style>
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
<div class="cd"> <h1 style="color: #2980b9;">Settings </h1> </div>
        <!-- Display notification -->
        <?php if (!empty($notification)) { ?>
            <div class="notification <?php echo $notificationType; ?>">
                <?php echo $notification; ?>
            </div>
        <?php } ?>

        <!-- Add Admin Button -->
        <button onclick="openModal()">Add Admin</button>

        <!-- Modal for Add Admin -->
        <div id="addAdminModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Add Admin</h2>
                <form action="settings_action.php" method="post">
                    <input type="hidden" name="action" value="add">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Add Admin</button>
                </form>
            </div>
        </div>

        <!-- Edit/Delete Admins Section -->
        <div class="admin-list">
            <h2>Manage Admins</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($admin['username']); ?></td>
                            <td>
                            <form action="settings_action.php" method="post">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="admin_id" value="<?php echo $admin['user_id']; ?>">
    <input type="text" name="username" placeholder="New Username" required style="width: 200px; padding: 8px; margin-bottom: 10px;">
    <br>
    <input type="password" name="password" placeholder="New Password" required style="width: 200px; padding: 8px; margin-bottom: 10px;">
    <br>
    <button type="submit" style="padding: 10px 20px; background-color: #2980b9; color: #fff; border: none; cursor: pointer;">Edit</button>
</form>

                            </td>
                            <td>
                                <form action="settings_action.php" method="post">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="admin_id" value="<?php echo $admin['user_id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JavaScript to control modal -->
    <script>
        // Get the modal
        var modal = document.getElementById('addAdminModal');

        // Function to open the modal
        function openModal() {
            modal.style.display = 'block';
        }

        // Function to close the modal
        function closeModal() {
            modal.style.display = 'none';
        }

        // Close the modal if user clicks outside of it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
