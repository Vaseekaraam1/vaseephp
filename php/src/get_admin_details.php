<?php
// Include database connection
include('db.php');

// Check if admin ID is provided via POST
if (isset($_POST['id'])) {
    $adminId = $_POST['id'];
    
    // Query to fetch admin details by admin ID
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $adminId);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            // Return admin details as JSON response
            echo json_encode($admin);
        } else {
            // No admin found with the provided ID
            echo json_encode(['error' => 'Admin not found']);
        }
    } else {
        // Error executing query
        echo json_encode(['error' => 'Query execution failed']);
    }
} else {
    // Admin ID not provided
    echo json_encode(['error' => 'Admin ID not provided']);
}
?>
