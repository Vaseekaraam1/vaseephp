<?php
// Database connection
$mysqli = new mysqli("localhost", "root", "cubensquare", "bloomprj");

// Check connection
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

// Check if customer_id is set in the POST request
if (isset($_POST['customer_id'])) {
    // Get the customer_id from POST request
    $customer_id = $_POST['customer_id'];

    try {
        // Prepare the delete statement
        $stmt = $mysqli->prepare("DELETE FROM customer WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);

        // Execute the statement
        if ($stmt->execute()) {
            echo "Customer deleted successfully.";
        } else {
            throw new Exception("Could not delete customer.");
        }

        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        // Foreign key constraint error
        if ($e->getCode() == 1451) {
            echo "Cannot delete customer because there are related lease advances. Please remove them first.";
        } else {
            // General error
            echo "Error: " . $e->getMessage();
        }
    } catch (Exception $e) {
        // General exception
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Error: customer_id is not set.";
}

$mysqli->close();
?>
