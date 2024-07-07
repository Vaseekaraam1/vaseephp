<?php
$servername = "db";
$username = "myuser";
$password = "cubensquare";
$dbname = "bloomprj";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
