<?php
$servername = "localhost";
$username = "root";
$password = "cubensquare";
$dbname = "bloomprj";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT DISTINCT location FROM billing";
$result = $conn->query($sql);

if ($result === FALSE) {
    die("Error fetching locations: " . $conn->error);
}

$locations = [];
while ($row = $result->fetch_assoc()) {
    $locations[] = $row['location'];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($locations);
?>
