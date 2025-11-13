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

if ($role == 'ADMIN') {
     header("Location: ../admin/admin.php");
} 
// Get user info
$user_sql = "SELECT username, email, role, created_at FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Pagination setup
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Count total orders
$count_sql = "SELECT COUNT(*) AS total FROM orders WHERE user_id = ?";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$totalOrders = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = max(ceil($totalOrders / $itemsPerPage), 1);

// --- Filters and sorting ---
$conditions = ["user_id = ?"];
$params = [$user_id];
$types = "i";

// Filter by status
if (!empty($_GET['status'])) {
    $conditions[] = "status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}

// Filter by date range
if (!empty($_GET['start_date'])) {
    $conditions[] = "DATE(created_at) >= ?";
    $params[] = $_GET['start_date'];
    $types .= "s";
}
if (!empty($_GET['end_date'])) {
    $conditions[] = "DATE(created_at) <= ?";
    $params[] = $_GET['end_date'];
    $types .= "s";
}

// Sorting by time (newest → oldest by default)
$orderBy = "ORDER BY created_at DESC";
if (!empty($_GET['sort']) && $_GET['sort'] === 'oldest') {
    $orderBy = "ORDER BY created_at ASC";
}

$whereClause = implode(" AND ", $conditions);

$order_sql = "SELECT order_id, created_at, status, total_price, address, full_name
              FROM orders
              WHERE $whereClause
              $orderBy
              LIMIT ?, ?";
$params[] = $offset;
$params[] = $itemsPerPage;
$types .= "ii";

$stmt = $conn->prepare($order_sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../../src/img/books.png" type="image/x-icon">
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
            <a href="#"
                class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-white/5 hover:text-[#1b1b1e]">Books
                under €5</a>
            <a href="#"
                class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-white/5 hover:text-[#1b1b1e]">Redaction
                Selected
            </a>
        </div>
    </nav>
    <main class="flex-1 max-w-full mx-4">
        <!-- Breadcrumb -->
        <nav class="flex my-6 mx-10" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="../index.php"
                        class="inline-flex items-center text-sm font-medium text-[#618792] hover:text-[#1b1b1e]">
                        <svg class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z" />
                        </svg>
                        Home
                    </a>
                </li>

                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="rtl:rotate-180 w-3 h-3 text-[#618792] mx-1" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <span class="ms-1 text-sm font-medium text-[#618792] md:ms-2">Your orders and info</span>
                    </div>
                </li>
            </ol>
        </nav>
        <div class="flex flex-col md:flex-row gap-4 bg-[#f8fafc] max-w-full justify-between">
            <!-- Sidebar -->
            <aside class="ml-10 mr-5 max-w-full md:w-64 sticky">
                <h2 class="text-xl font-semibold mb-4">Filters</h2>
                <form method="GET" action="" class="space-y-4">
                    <!-- ✅ Order Status Filter -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1b1b1e] mb-2">Order Status</label>
                        <select name="status"
                            class="w-full border border-gray-300 rounded-md p-2 text-[#1b1b1e] focus:outline-none focus:ring-2 focus:ring-[#8AB2C1]">
                            <option value="">All</option>
                            <?php
                            $statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
                            foreach ($statuses as $status) {
                                $selected = (isset($_GET['status']) && $_GET['status'] === $status) ? 'selected' : '';
                                echo '<option value="' . $status . '" ' . $selected . '>' . ucfirst($status) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <!-- ✅ Date Range Filter -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1b1b1e] mb-2">Date Range</label>
                        <div class="flex items-center space-x-2">
                            <input type="date" name="start_date"
                                value="<?= isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : '' ?>"
                                class="w-1/2 border border-gray-300 rounded-md p-2 text-[#1b1b1e] focus:outline-none focus:ring-2 focus:ring-[#8AB2C1]">
                            <input type="date" name="end_date"
                                value="<?= isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : '' ?>"
                                class="w-1/2 border border-gray-300 rounded-md p-2 text-[#1b1b1e] focus:outline-none focus:ring-2 focus:ring-[#8AB2C1]">
                        </div>
                    </div>

                    <hr class="my-4">

                    <h2 class="text-xl font-semibold mb-4">Sort By</h2>

                    <!-- ✅ Sort Options -->
                    <div class="mb-4">
                        <select name="sort" id="sort"
                            class="w-full border border-gray-300 rounded-md p-2 text-[#1b1b1e] focus:outline-none focus:ring-2 focus:ring-[#8AB2C1]">
                            <option value="">Newest First</option>
                            <option value="oldest" <?= isset($_GET['sort']) && $_GET['sort'] == 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                        </select>
                    </div>

                    <div class="flex space-x-2">
                        <a href="myinfo.php"
                            class="flex-1 text-[#618792] border border-[#618792] py-2 rounded-md font-medium text-center hover:bg-[#618792] hover:text-white transition">
                            Reset
                        </a>
                        <button type="submit"
                            class="flex-1 bg-[#618792]/80 text-white py-2 rounded-md font-medium hover:bg-[#618792] transition">
                            Apply
                        </button>
                    </div>
                </form>
                <div class="mt-10 p-4 bg-[#f7f9fa] rounded-lg shadow-sm border border-gray-200">
                    <h2 class="text-lg font-semibold mb-3 text-[#1b1b1e]">User Information</h2>
                    <p class="text-sm text-gray-700"><strong>Username:</strong>
                        <?= htmlspecialchars($user['username']) ?></p>
                    <p class="text-sm text-gray-700"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p class="text-sm text-gray-700"><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
                    <p class="text-sm text-gray-700"><strong>Joined:</strong>
                        <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
                    <div class="mt-1 text-right">
                        <a href="../logout.php" class="text-sm text-red-600 hover:underline">Logout</a>
                    </div>
                </div>
            </aside>
            <div class="flex-1 mx-4 mr-10">
                <div class="overflow-x-auto bg-white border border-gray-200 rounded-xl shadow-sm">
                    <table class="min-w-full text-sm text-left text-gray-700">
                        <thead class="bg-gray-100 text-gray-900 text-sm uppercase font-semibold">
                            <tr>
                                <th class="px-6 py-3">Order ID</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Beneficiary</th>
                                <th class="px-6 py-3">Shipment Address</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Total</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if ($orders->num_rows > 0): ?>
                                <?php while ($order = $orders->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 font-medium text-gray-900">#<?= $order['order_id'] ?></td>
                                        <td class="px-6 py-4"><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                        <td class="px-6 py-4 font-semibold"><?= $order['full_name'] ?>
                                        </td>
                                        <td class="px-6 py-4 font-semibold"><?= $order['address'] ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php
                                            $status = $order['status'] ?? 'completed';
                                            $colorClass = match ($status) {
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'processing' => 'bg-blue-100 text-blue-800',
                                                'shipped' => 'bg-purple-100 text-purple-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                                default => 'bg-green-100 text-green-800',
                                            };
                                            ?>
                                            <span
                                                class="inline-block px-3 py-1 text-xs font-medium rounded-full <?= $colorClass ?>">
                                                <?= ucfirst($status) ?>
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 font-semibold"><?= number_format($order['total_price'], 2) ?> €
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="order-details.php?id=<?= $order['order_id'] ?>"
                                                class="text-[#618792] hover:text-[#4f6b75] font-medium">View</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-center text-gray-500">No orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>
                <?php
                $queryParams = $_GET;
                unset($queryParams['page']);
                $queryString = http_build_query($queryParams);

                if ($orders->num_rows > 0): ?>
                    <nav class="flex justify-center items-baseline gap-x-1 mt-4" aria-label="Pagination">
                        <!-- Previous button -->
                        <a href="?<?= $queryString ?>&page=<?= max($currentPage - 1, 1) ?>"
                            class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentPage == 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                            &lt; Prev
                        </a>

                        <!-- Page numbers -->
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <a href="?<?= $queryString ?>&page=<?= $p ?>"
                                class="px-3 py-2 rounded-lg <?= $p == $currentPage ? 'bg-[#618792] text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>

                        <!-- Next button -->
                        <a href="?<?= $queryString ?>&page=<?= min($currentPage + 1, $totalPages) ?>"
                            class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentPage == $totalPages ? 'opacity-50 pointer-events-none' : '' ?>">
                            Next &gt;
                        </a>
                    </nav>

                <?php elseif ($orders->num_rows == 0): ?>
                    <nav class="flex justify-center items-baseline gap-x-1 mt-4" aria-label="Pagination">
                        <a href="?<?= $queryString ?>&page=1"
                            class="px-3 py-2 rounded-lg <?= $p == $currentPage ? 'bg-[#618792] text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
                            1
                        </a>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <footer class="z-20 w-full bg-blue-200 place-self-end  mt-auto">
        <div class="mx-10 px-2 sm:px-6 lg:px-8">
            <div class="relative flex h-16 items-center justify-between">
                <span
                    class="rounded-md px-3 py-2 text-sm font-medium text-[#618792] hover:bg-white/20 hover:text-[#618792]">©
                    2025 <a href="https://github.com/tpwrzz/php-online-bookstore" class="hover:underline">Poverjuc
                        Tatiana</a> IAFR2302
                </span>
                <ul class="flex flex-wrap items-center mt-3 text-sm font-medium sm:mt-0">
                    <li>
                        <a href="#"
                            class="rounded-md px-3 py-2 text-sm font-medium text-[#618792]/80 hover:bg-white/20 hover:text-[#618792]">About</a>
                    </li>
                    <li>
                        <a href="#"
                            class="rounded-md px-3 py-2 text-sm font-medium text-[#618792]/80 hover:bg-white/20 hover:text-[#618792]">Privacy
                            Policy</a>
                    </li>
                    <li>
                        <a href="#"
                            class="rounded-md px-3 py-2 text-sm font-medium text-[#618792]/80 hover:bg-white/20 hover:text-[#618792]">Licensing</a>
                    </li>
                    <li>
                        <a href="#"
                            class="rounded-md px-3 py-2 text-sm font-medium text-[#618792]/80 hover:bg-white/20 hover:text-[#618792]">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </footer>
</body>

</html>