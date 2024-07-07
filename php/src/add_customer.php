<?php
session_start();
include('db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $location = $_POST['location'] ?? '';
    $commission_amount = $_POST['commission_amount'] ?? 0;

    if ($name && $location) {
        $stmt = $conn->prepare("INSERT INTO customer (name, location, commission_amount) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $name, $location, $commission_amount);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding customer: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Name and Location are required.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
