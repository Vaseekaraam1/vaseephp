<?php
session_start();
include('db.php'); // Adjust the path to match your db.php location

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_flower'])) {
    $flower_id = $_POST['flower_id'];
    $flower_name = $_POST['flower_name'];
    $luggage_cost = $_POST['luggage_cost'];

    $stmt = $conn->prepare("UPDATE flower SET flower_name = ?, luggage_cost = ? WHERE id = ?");
    $stmt->bind_param("sdi", $flower_name, $luggage_cost, $flower_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Flower updated successfully.";
    } else {
        $_SESSION['message'] = "Error updating flower: " . $stmt->error;
    }
    $stmt->close();

    header("Location: flowers.php");
    exit();
} else {
    $_SESSION['message'] = "Invalid request.";
    header("Location: flowers.php");
    exit();
}
?>
