<?php
session_start();
include('db.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check if ID is provided via GET
if (!isset($_GET['id'])) {
    header("Location: settings.php");
    exit();
}

$admin_id = $_GET['id'];

// Fetch admin details
$sql = "SELECT * FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
} else {
    $_SESSION['notification'] = "Admin not found.";
    header("Location: settings.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Admin</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="content">
        <header>
            <h1>Edit Admin</h1>
        </header>
        <div class="admin-section">
            <form id="editAdminForm" method="POST" action="update_admin.php">
                <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="fullName">Full Name:</label>
                    <input type="text" id="fullName" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                </div>
                <div class="form-group">
                    <button type="submit">Update Admin</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
