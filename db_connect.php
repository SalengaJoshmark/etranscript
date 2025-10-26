<?php
$host = "localhost";
$user = "root";  // default XAMPP username
$pass = "";       // default XAMPP password (empty)
$db = "e_transcript_db"; // your database name

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
