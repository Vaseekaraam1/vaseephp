<?php
session_start();
include('db.php'); // Adjust the path to match your db.php location

// Check if form fields are set and not empty
if (isset($_POST['id'], $_POST['shift1_price'], $_POST['shift2_price'], $_POST['shift3_price'], $_POST['shift4_price'])) {
    // Sanitize and validate input (ensure to sanitize user inputs properly)
    $id = intval($_POST['id']);
    $shift1_price = floatval($_POST['shift1_price']);
    $shift2_price = floatval($_POST['shift2_price']);
    $shift3_price = floatval($_POST['shift3_price']);
    $shift4_price = floatval($_POST['shift4_price']);

    // Prepare and execute SQL UPDATE statement
    $stmt = $conn->prepare("UPDATE flower_price 
                            SET shift1_price = ?, shift2_price = ?, shift3_price = ?, shift4_price = ?
                            WHERE id = ?");
    $stmt->bind_param("ddddd", $shift1_price, $shift2_price, $shift3_price, $shift4_price, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Update successful
        echo "Price updated successfully";
    } else {
        // No rows affected, suggest error
        echo "Error updating price";
    }

    $stmt->close();
} else {
    // Invalid or incomplete data received
    echo "Incomplete data received";
}

$conn->close();
?>
