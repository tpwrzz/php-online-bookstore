<?php
session_start();
include('../src/scripts/db-connect.php'); 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['fullname']);  
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        die("Please fill in all fields.");
    }

    // check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email already registered!'); window.history.back();</script>";
        exit;
    }

    $check->close();

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role, email, created_at) VALUES (?, ?, 'USER', ?, NOW())");
    $stmt->bind_param("sss", $username, $password_hash, $email);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'USER';

        header("Location: order.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
