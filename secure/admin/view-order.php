<?php
session_start();
include('../../src/scripts/db-connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../public/auth.php");
    exit;
}

// Check admin
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT role FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$role = $stmt->get_result()->fetch_assoc()['role'];
if ($role !== 'ADMIN') {
    die("Access denied");
}

// ---------------- ORDER DETAILS ----------------
$order_id = $_GET['id'] ?? null;

if (!$order_id || !is_numeric($order_id)) {
    die("Invalid order ID");
}

// Fetch order + user
$stmt = $conn->prepare("
    SELECT o.*, u.username, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found");
}

// Fetch items with books, authors, genres
$stmt = $conn->prepare("
    SELECT 
        oi.order_item_id, oi.quantity, oi.price_each,
        b.book_id, b.title, b.cover_img,
        CONCAT(a.first_name, ' ', a.last_name) AS author,
        g.name AS genre
    FROM order_items oi
    INNER JOIN books b ON oi.book_id = b.book_id
    LEFT JOIN authors a ON b.author_id = a.author_id
    LEFT JOIN genres g ON b.genre_id = g.genre_id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ---------------- UPDATE STATUS ----------------
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $old_status = $order['status'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // If order becomes CANCELLED → restore stock
        if ($old_status !== 'cancelled' && $new_status === 'cancelled') {

            // Get all items again inside transaction
            $stmt = $conn->prepare("
                SELECT book_id, quantity
                FROM order_items
                WHERE order_id = ?
            ");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $itemsToRestore = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Restore stock for each item
            $stmtRestore = $conn->prepare("
                UPDATE books 
                SET stock_qty = stock_qty + ? 
                WHERE book_id = ?
            ");

            foreach ($itemsToRestore as $it) {
                $stmtRestore->bind_param("ii", $it['quantity'], $it['book_id']);
                $stmtRestore->execute();
            }
        }

        // Update the order status
        $stmt = $conn->prepare("UPDATE orders SET status=? WHERE order_id=?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();

        $conn->commit();

        header("Location: view-order.php?id=" . $order_id);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        die("Error processing status update: " . $e->getMessage());
    }
}

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../../src/img/books.png" type="image/x-icon">
    <title>View Order Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="flex flex-col min-h-screen bg-[#f8fafc]">
    <nav class="relative bg-gray-800 dark:bg-blue-200">
        <div class="mx-20 max-w-full sm:px-6 lg:px-8">
            <div class="relative flex h-20 items-center justify-center-safe">
                <!-- Mobile menu button -->
                <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
                    <button id="hamburger-btn" type="button"
                        class="relative inline-flex items-center justify-center rounded-md p-2 text-blue-800 hover:bg-white/5 hover:text-[#1b1b1e] focus:outline-2 focus:-outline-offset-1 focus:outline-indigo-500">
                        <span class="sr-only">Open main menu</span>
                        <!-- Hamburger Icon -->
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <!-- Close Icon -->
                        <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Logo -->
                <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
                    <div class="flex shrink-0 items-center">
                        <img src="../../src/img/books.png" alt="Bookstore" class="h-10 w-auto" />
                    </div>

                    <!-- Desktop Menu -->
                    <div class="hidden sm:ml-6 sm:block">
                        <div class="flex space-x-4">
                            <div
                                class="rounded-md bg-[#618792] px-3 py-2 text-lg font-medium text-white dark:bg-gray-950/50 ">
                                Online
                                Bookstore Admin</div>
                        </div>
                    </div>
                </div>

                <!-- Right Icons -->
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                    <el-dropdown class="relative ml-3">
                        <div
                            class="relative flex rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                            <span class="sr-only">Open user menu</span>
                            <img src="../../src/img/setting.png" alt="User Avatar" class="size-10 rounded-full" />
                        </div>
                    </el-dropdown>
                </div>
            </div>
        </div>
    </nav>
    <main class="w-full max-w-[1600px] mx-auto px-8">
        <!-- Breadcrumb -->
        <nav class="flex my-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li aria-current="page">
                    <a href="orders.php" class="flex items-center">
                        <svg class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z" />
                        </svg>
                        <span class="ms-1 text-sm font-medium text-[#618792] md:ms-2">Admin Page</span>
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="rtl:rotate-180 w-3 h-3 text-[#618792] mx-1" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <span class="ms-1 text-sm font-medium text-[#618792] md:ms-2">Order View</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-2xl font-bold mt-4 mb-6">Order #<?= $order_id ?></h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-10 w-full">


            <!-- ORDER INFO -->
            <div class="bg-white shadow p-6 rounded">
                <h2 class="text-lg font-semibold mb-2">Order Information</h2>
                <p><strong>User:</strong> <?= htmlspecialchars($order['username']) ?> (<?= $order['email'] ?>)</p>
                <p><strong>Total Price:</strong> $<?= number_format($order['total_price'], 2) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
                <p><strong>Created:</strong> <?= $order['created_at'] ?></p>

                <form method="POST" class="mt-4 flex items-center gap-3">
                    <select name="status" class="border px-3 py-1 rounded">
                        <?php
                        $statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
                        foreach ($statuses as $s):
                            ?>
                            <option value="<?= $s ?>" <?= $order['status'] == $s ? 'selected' : '' ?>>
                                <?= ucfirst($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="update_status" class="bg-green-600 text-white px-4 py-1 rounded">
                        Update Status
                    </button>
                </form>
            </div>

            <!-- ORDER ITEMS -->
            <div class="bg-white shadow p-6 rounded">
                <h2 class="text-lg font-semibold mb-3">Items</h2>

                <table class="w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">Book</th>
                            <th class="px-3 py-2 text-left">Author</th>
                            <th class="px-3 py-2 text-left">Genre</th>
                            <th class="px-3 py-2 text-center">Qty</th>
                            <th class="px-3 py-2 text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr class="border-b">
                                <td class="px-3 py-2 flex items-center gap-3">
                                    <img src="../../src/img/covers/<?= $item['cover_img'] ?>"
                                        class="w-12 h-16 rounded shadow">
                                    <a href="../../public/book.php?id=<?= $item['book_id'] ?>"
                                        class="text-blue-700 underline">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </a>
                                </td>
                                <td class="px-3 py-2"><?= htmlspecialchars($item['author']) ?></td>
                                <td class="px-3 py-2"><?= htmlspecialchars($item['genre']) ?></td>
                                <td class="px-3 py-2 text-center"><?= $item['quantity'] ?></td>
                                <td class="px-3 py-2 text-right">$<?= number_format($item['price_each'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
    <footer class="z-20 w-full bg-blue-200 place-self-end mt-auto">
        <div class="mx-10 px-2 sm:px-6 lg:px-8">
            <div class="relative flex h-16 items-center justify-between">
                <span
                    class="rounded-md px-3 py-2 text-sm font-medium text-[#618792] hover:bg-white/20 hover:text-[#618792]">©
                    2025 <a href="https://github.com/tpwrzz/php-online-bookstore" class="hover:underline">Poverjuc
                        Tatiana</a> IAFR2302
                </span>
            </div>
        </div>
    </footer>
</body>

</html>