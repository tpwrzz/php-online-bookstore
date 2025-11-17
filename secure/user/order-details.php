<?php
session_start();
require_once('../../src/scripts/db-connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../public/auth.php");
    exit;
}

$loggedInUserId = (int) $_SESSION['user_id'];


if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(400);
    die("Invalid order ID");
}

$orderId = (int) $_GET['id'];

$sqlOrder = "
    SELECT 
        o.order_id,
        o.user_id,
        o.full_name,
        o.address,
        o.total_price,
        o.status,
        o.created_at,
        u.email
    FROM orders o
    INNER JOIN users u ON u.user_id = o.user_id
    WHERE o.order_id = ? AND o.user_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sqlOrder);
$stmt->bind_param("ii", $orderId, $loggedInUserId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    http_response_code(404);
    die("Order not found.");
}

$sqlItems = "
    SELECT 
        oi.order_item_id,
        oi.book_id,
        oi.quantity,
        oi.price_each,

        b.title,
        b.cover_img,
        b.price AS book_price,

        CONCAT(a.first_name, ' ', a.last_name) AS author_name,
        g.name AS genre_name

    FROM order_items oi
    INNER JOIN books b ON oi.book_id = b.book_id
    LEFT JOIN authors a ON b.author_id = a.author_id
    LEFT JOIN genres g ON b.genre_id = g.genre_id

    WHERE oi.order_id = ?
";

$stmt = $conn->prepare($sqlItems);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


function statusBadge(string $status): string
{
    return match ($status) {
        'pending' => 'bg-yellow-200 text-yellow-800',
        'processing' => 'bg-blue-200 text-blue-800',
        'completed' => 'bg-green-200 text-green-800',
        'cancelled' => 'bg-red-200 text-red-800',
        default => 'bg-gray-200 text-gray-800',
    };
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../../src/img/books.png" type="image/x-icon">
    <title>Orders User Page</title>
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
                            <a href="../../index.php" aria-current="page"
                                class="rounded-md bg-[#618792] px-3 py-2 text-lg font-medium text-white dark:bg-gray-950/50 hover:bg-gray-950/70">Online
                                Bookstore</a>
                        </div>
                    </div>
                </div>

                <!-- Right Icons -->
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                    <?php
                    $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                    ?>
                    <a href="../../public/cart.php" class="relative">
                        <img src="../../src/img/shopping-cart.png" alt="Cart" class="size-9" />
                        <?php if ($cartCount > 0): ?>
                            <span
                                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    </button>
                    <el-dropdown class="relative ml-3">
                        <a href='<?=
                            isset($_SESSION['user_id']) ? 'myinfo.php' : '../../public/auth.php'
                            ?>'
                            class="relative flex rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                            <span class="sr-only">Open user menu</span>
                            <img src="../../src/img/avatar.png" alt="User Avatar" class="size-10 rounded-full" />
                        </a>
                        <el-menu anchor="bottom end" popover
                            class="w-48 origin-top-right rounded-md bg-white py-1 shadow-lg outline outline-black/5 dark:bg-gray-800 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10">
                            <a href="#"
                                class="block px-4 py-2 text-sm text-[#1b1b1e] focus:bg-[#618792]/90 dark:text-gray-300 dark:focus:bg-white/5">Your
                                profile</a>
                            <a href="#"
                                class="block px-4 py-2 text-sm text-[#1b1b1e] focus:bg-[#618792]/90 dark:text-gray-300 dark:focus:bg-white/5">Settings</a>
                            <a href="#"
                                class="block px-4 py-2 text-sm text-[#1b1b1e] focus:bg-[#618792]/90 dark:text-gray-300 dark:focus:bg-white/5">Sign
                                out</a>
                        </el-menu>
                    </el-dropdown>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="sm:hidden hidden px-2 pt-2 pb-3 space-y-1">
            <a href="#" aria-current="page"
                class="block rounded-md bg-[#618792] px-3 py-2 text-base font-medium text-[#1b1b1e] dark:bg-gray-950/50">Online
                Bookstore</a>
        </div>
    </nav>
    <main class="flex-1 w-full px-8 py-8">

        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="../../index.php"
                        class="inline-flex items-center text-sm font-medium text-[#618792] hover:text-[#1b1b1e]">
                        Home
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="rtl:rotate-180 w-3 h-3 text-[#618792] mx-1" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <span class="ms-1 text-sm font-medium text-[#618792] md:ms-2">Order Details</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header Row -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <h1 class="text-3xl font-semibold">Order #<?= $order['order_id'] ?></h1>
            <span class="px-3 py-1 rounded-md text-sm <?= statusBadge($order['status']) ?>">
                <?= ucfirst($order['status']) ?>
            </span>
        </div>

        <!-- Order Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 text-gray-700">

            <div class="bg-white shadow-sm p-5 rounded-lg border border-gray-200">
                <h2 class="font-semibold text-gray-800 mb-1">Order Date</h2>
                <p><?= date("d M Y, H:i", strtotime($order['created_at'])) ?></p>
            </div>

            <div class="bg-white shadow-sm p-5 rounded-lg border border-gray-200">
                <h2 class="font-semibold text-gray-800 mb-1">Customer</h2>
                <p><?= htmlspecialchars($order['full_name']) ?></p>
                <p class="text-sm text-gray-600"><?= htmlspecialchars($order['email']) ?></p>
            </div>

            <div class="bg-white shadow-sm p-5 rounded-lg border border-gray-200">
                <h2 class="font-semibold text-gray-800 mb-1">Shipping Address</h2>
                <p><?= nl2br(htmlspecialchars($order['address'])) ?></p>
            </div>

        </div>

        <!-- Items Section -->
        <h2 class="text-xl font-semibold mb-3">Items</h2>

        <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-4 mb-6 overflow-x-auto w-full">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="py-2 text-left">Book</th>
                        <th class="py-2 text-left">Author</th>
                        <th class="py-2 text-left">Genre</th>
                        <th class="py-2 text-center">Qty</th>
                        <th class="py-2 text-right">Price</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr class="border-b last:border-0">
                            <td class="py-3 flex items-center gap-3">
                                <a href="../../public/book.php?id=<?= $item['book_id'] ?>">
                                    <img src="../../src/img/covers/<?= htmlspecialchars($item['cover_img']) ?>"
                                        class="w-16 h-24 rounded shadow-sm border border-gray-200" alt="">
                                </a>
                                <a href="../../public/book.php?id=<?= $item['book_id'] ?>"
                                    class="font-semibold text-[#618792] hover:text-[#1b1b1e]">
                                    <?= htmlspecialchars($item['title']) ?>
                                </a>
                            </td>

                            <td><?= htmlspecialchars($item['author_name']) ?></td>
                            <td><?= htmlspecialchars($item['genre_name']) ?></td>
                            <td class="text-center"><?= $item['quantity'] ?></td>
                            <td class="text-right">$<?= number_format($item['price_each'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Total -->
        <div class="flex justify-end mb-10">
            <p class="text-2xl font-semibold">Total: $<?= number_format($order['total_price'], 2) ?></p>
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