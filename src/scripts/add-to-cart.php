<?php
session_start();

$bookId = $_POST['book_id'];
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$bookId])) {
    $_SESSION['cart'][$bookId] += $quantity;
} else {
    $_SESSION['cart'][$bookId] = $quantity;
}

// Перенаправление обратно на страницу книги или на страницу каталога
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;