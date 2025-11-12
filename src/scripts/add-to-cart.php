<?php
session_start();
include('src/scripts/db-connect.php');

if (!isset($_POST['book_id']) || !is_numeric($_POST['book_id'])) {
    die("Invalid book ID.");
}

$bookId = intval($_POST['book_id']);

// Initialize the cart if it doesn't exist yet
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add the book to the session cart (avoid duplicates)
if (!in_array($bookId, $_SESSION['cart'])) {
    $_SESSION['cart'][] = $bookId;
}

// Redirect back to the previous page
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
