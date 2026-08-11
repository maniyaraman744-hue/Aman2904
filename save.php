<?php
include "connect.php";

$name = $_POST['name'];
$email = $_POST['email'];

$sql = "INSERT INTO students(name, email)
        VALUES('$name', '$email')";

if (mysqli_query($conn, $sql)) {
    echo "<h2>Student Registered Successfully</h2>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
