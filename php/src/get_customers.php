<?php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['location'])) {
    $location = $_POST['location'];
    $query = "SELECT customer_id, name FROM customer WHERE location = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $location);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    echo '<option value="">Select Customer</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['customer_id'] . '">' . $row['name'] . '</option>';
    }

    $conn->close();
}
?>