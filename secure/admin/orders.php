<?php
session_start();
include('../../src/scripts/db-connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../public/auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$role = $stmt->get_result()->fetch_assoc()['role'];

if ($role !== 'ADMIN') {
    die("Access denied");
}

// ---------------- ORDERS ----------------
$ordersPerPage = 12;
$currentOrdersPage = isset($_GET['orders_page']) && is_numeric($_GET['orders_page']) ? (int) $_GET['orders_page'] : 1;
$ordersOffset = ($currentOrdersPage - 1) * $ordersPerPage;

$totalOrders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
$totalOrdersPages = max(ceil($totalOrders / $ordersPerPage), 1);

$orders = $conn->query("SELECT o.order_id, o.total_price, o.status, o.created_at, u.username 
                        FROM orders o
                        JOIN users u ON o.user_id = u.user_id
                        ORDER BY o.created_at DESC
                        LIMIT $ordersOffset, $ordersPerPage");

if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $old_status = $order['status'];

    $conn->begin_transaction();

    try {
        if ($old_status !== 'cancelled' && $new_status === 'cancelled') {

            $stmt = $conn->prepare("
                SELECT book_id, quantity
                FROM order_items
                WHERE order_id = ?
            ");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $itemsToRestore = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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

if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];

    $stmt = $conn->prepare("SELECT book_id, quantity FROM order_items WHERE order_id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM orders WHERE order_id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    header("Location: orders.php");
    exit;
}

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../../src/img/books.png" type="image/x-icon">
    <title>Orders Admin Page</title>
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
    <div class="flex-1 max-w-full mx-20">
        <!-- Tabs -->
        <div class="text-sm font-medium text-center text-body border-b border-default mb-4">
            <ul class="flex flex-wrap -mb-px" id="tabs">
                <li class="me-2"><a href="orders.php"
                        class="active inline-block p-4 border-b rounded-t-base text-blue-600 border-blue-600">Orders</a>
                </li>
                <li class="me-2"><a href="users.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Users</a>
                </li>
                <li class="me-2"><a href="genres.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Genres</a>
                </li>
                <li class="me-2"><a href="authors.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Authors</a>
                </li>
                <li class="me-2"><a href="books.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Books</a>
                </li>
                <li class="ml-auto">
                    <a href="../logout.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-red-600 hover:border-red-600">Logout</a>
                </li>
            </ul>
        </div>

        <!-- Tab Contents -->
        <div id="tab-contents" class="mb-4">
            <!-- Orders Tab -->
            <div id="orders" class="tab-content">
                <h2 class="text-xl font-semibold mb-4">Manage Orders</h2>
                <?php if (!empty($errors)): ?>
                    <div class="mb-4 p-2 border border-red-500 bg-red-100 text-red-700 rounded">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <table class="min-w-full bg-white border mb-6">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">User</th>
                            <th class="px-4 py-2 text-left">Price</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Created At</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $orders->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-50 ">
                                <td class="px-4 py-2 font-semibold text-blue-700 underline cursor-pointer"
                                    onclick="window.location='view-order.php?id=<?= $row['order_id'] ?>'">
                                    #<?= $row['order_id'] ?>
                                </td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['username']) ?></td>
                                <td class="px-4 py-2"><?= $row['total_price'] ?></td>
                                <td class="px-4 py-2">
                                    <form method="POST" class="flex space-x-2">
                                        <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                        <select name="status" class="border px-2 py-1 rounded">
                                            <?php foreach (['pending', 'processing', 'shipped', 'completed', 'cancelled'] as $status): ?>
                                                <option value="<?= $status ?>" <?= $row['status'] == $status ? 'selected' : '' ?>>
                                                    <?= ucfirst($status) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="update_order_status"
                                            class="bg-green-500 text-white px-2 rounded">Update</button>
                                    </form>
                                </td>
                                <td class="px-4 py-2"><?= $row['created_at'] ?></td>
                                <td class="px-4 py-2">
                                    <!-- Delete order -->
                                    <form method="POST" onsubmit="return confirm('Delete order?')">
                                        <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                        <button type="submit" name="delete_order"
                                            class="bg-red-500 text-white px-2 rounded">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php $queryParams = $_GET;
                unset($queryParams['orders_page']);
                $queryString = http_build_query($queryParams);
                ?>
                <nav class="flex justify-center items-center gap-x-1 mt-4" aria-label="Orders Pagination">
                    <a href="?<?= $queryString ?>&orders_page=<?= max($currentOrdersPage - 1, 1) ?>"
                        class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentOrdersPage == 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                        &lt; Prev
                    </a>

                    <?php for ($p = 1; $p <= $totalOrdersPages; $p++): ?>
                        <a href="?<?= $queryString ?>&orders_page=<?= $p ?>"
                            class="px-3 py-2 rounded-lg <?= $p == $currentOrdersPage ? 'bg-[#618792] text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <a href="?<?= $queryString ?>&orders_page=<?= min($currentOrdersPage + 1, $totalOrdersPages) ?>"
                        class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentOrdersPage == $totalOrdersPages ? 'opacity-50 pointer-events-none' : '' ?>">
                        Next &gt;
                    </a>
                </nav>
            </div>

        </div>
    </div>
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