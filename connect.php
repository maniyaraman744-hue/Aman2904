<?php
// Replace placeholders locally. Never commit real credentials.
$host = "YOUR_RDS_ENDPOINT";
$user = "admin";
$password = "YOUR_RDS_PASSWORD";
$db = "studentdb";

$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Connection Failed");
}
?>
