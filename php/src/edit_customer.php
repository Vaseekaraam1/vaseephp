<?php
session_start();
include('db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $location = $_POST['location'] ?? '';
    $commission_amount = $_POST['commission_amount'] ?? 0;

    if ($customer_id && $name && $location) {
        $stmt = $conn->prepare("UPDATE customer SET name = ?, location = ?, commission_amount = ? WHERE customer_id = ?");
        $stmt->bind_param("ssdi", $name, $location, $commission_amount, $customer_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating customer: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
