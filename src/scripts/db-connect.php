<?php
$host = "localhost";      // or 127.0.0.1
$user = "root";           // your DB username
$pass = "";               // your DB password
$dbname = "bookstore";    // your database name

$conn = new mysqli($host, $user, $pass, $dbname);

// check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
