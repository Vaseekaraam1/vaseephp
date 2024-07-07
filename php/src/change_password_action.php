<?php
session_start();
include('db.php'); // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate if new password and confirm password match
    if ($new_password !== $confirm_password) {
        $_SESSION['notification'] = "New passwords do not match.";
        $_SESSION['notification_type'] = "error";
        header("Location: change_password.php");
        exit();
    }

    // Check if current password is correct
    $username = $_SESSION['username'];
    $sql = "SELECT password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();

    if (password_verify($current_password, $hashed_password)) {
        // Hash the new password
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password in database
        $update_sql = "UPDATE users SET password = ? WHERE username = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $hashed_new_password, $username);

        if ($update_stmt->execute()) {
            $_SESSION['notification'] = "Password changed successfully.";
            $_SESSION['notification_type'] = "success";
        } else {
            $_SESSION['notification'] = "Error changing password.";
            $_SESSION['notification_type'] = "error";
        }

        $update_stmt->close();
    } else {
        $_SESSION['notification'] = "Current password is incorrect.";
        $_SESSION['notification_type'] = "error";
    }

    $stmt->close();
    $conn->close();

    header("Location: change_password.php");
    exit();
}
?>
