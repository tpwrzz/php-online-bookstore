<?php
session_start();
include('../../src/scripts/db-connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// get user info
$user_sql = "SELECT username, email, role, created_at FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// get user orders
$order_sql = "SELECT order_id, total_price, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Info</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">

<div class="max-w-3xl mx-auto bg-white rounded-xl border border-gray-200 shadow-sm p-8">
    <h1 class="text-2xl font-semibold mb-4">My Profile</h1>
    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
    <p><strong>Member since:</strong> <?= htmlspecialchars($user['created_at']) ?></p>

    <h2 class="text-xl font-semibold mt-8 mb-4">My Orders</h2>
    <?php if ($orders->num_rows > 0): ?>
        <ul class="space-y-2">
            <?php while ($order = $orders->fetch_assoc()): ?>
                <li class="border rounded-md p-3 flex justify-between">
                    <span>Order #<?= $order['order_id'] ?> — <?= $order['created_at'] ?></span>
                    <span class="font-semibold"><?= number_format($order['total_price'], 2) ?> €</span>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No orders yet.</p>
    <?php endif; ?>

    <div class="mt-8 text-right">
        <a href="logout.php" class="text-red-600 hover:underline">Logout</a>
    </div>
</div>

</body>
</html>
