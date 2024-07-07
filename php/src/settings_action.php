<?php
session_start();
include('db.php'); // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['action'] == 'add') {
        // Add Admin
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into database
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $hashed_password);

        if ($stmt->execute()) {
            $_SESSION['notification'] = "Admin added successfully.";
            $_SESSION['notification_type'] = "success";
        } else {
            $_SESSION['notification'] = "Error adding admin.";
            $_SESSION['notification_type'] = "error";
        }

        $stmt->close();
        $conn->close();

        header("Location: settings.php");
        exit();
    } elseif ($_POST['action'] == 'edit') {
        // Edit Admin
        $admin_id = $_POST['admin_id'];
        $new_username = $_POST['username'];
        $new_password = $_POST['password'];

        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update admin in database
        $sql = "UPDATE users SET username = ?, password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $new_username, $hashed_password, $admin_id);

        if ($stmt->execute()) {
            $_SESSION['notification'] = "Admin updated successfully.";
            $_SESSION['notification_type'] = "success";
        } else {
            $_SESSION['notification'] = "Error updating admin.";
            $_SESSION['notification_type'] = "error";
        }

        $stmt->close();
        $conn->close();

        header("Location: settings.php");
        exit();
    } elseif ($_POST['action'] == 'delete') {
        // Delete Admin
        $admin_id = $_POST['admin_id'];

        // Delete admin from database
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $admin_id);

        if ($stmt->execute()) {
            $_SESSION['notification'] = "Admin deleted successfully.";
            $_SESSION['notification_type'] = "success";
        } else {
            $_SESSION['notification'] = "Error deleting admin.";
            $_SESSION['notification_type'] = "error";
        }

        $stmt->close();
        $conn->close();

        header("Location: settings.php");
        exit();
    }
}
?>
