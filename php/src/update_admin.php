<?php
session_start();
include('db.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_id'])) {
    $admin_id = $_POST['admin_id'];
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];

    // Update admin details
    $update_sql = "UPDATE admins SET username = ?, full_name = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssi", $username, $full_name, $email, $admin_id);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Admin updated successfully.";
        header("Location: settings.php");
        exit();
    } else {
        $_SESSION['notification'] = "Error updating admin: " . $conn->error;
        header("Location: settings.php");
        exit();
    }

    $stmt->close();
} else {
    $_SESSION['notification'] = "Invalid request.";
    header("Location: settings.php");
    exit();
}
?>
