<?php
session_start();
include('../src/scripts/db-connect.php');

// проверка логина
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!$full_name || !$address || !$email) {
        $errors[] = "All fields are required";
    } else {
        // Здесь можно вставить логику создания заказа в таблицу `orders`
        // Пример (без корзины):
        $stmt = $conn->prepare("INSERT INTO orders (user_id, full_name, address, total_price, status, created_at) VALUES (?, ?, ?, ?, 'Pending', NOW())");
        $totalPrice = 0; // можно считать сумму из $_SESSION['cart']
        $stmt->bind_param("issd", $_SESSION['user_id'], $full_name, $address, $totalPrice);
        if ($stmt->execute()) {
            $success = true;
            unset($_SESSION['cart']); // очистка корзины после заказа
        } else {
            $errors[] = "Failed to create order. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../src/img/books.png" type="image/x-icon">
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
                        <img src="../src/img/books.png" alt="Bookstore" class="h-10 w-auto" />
                    </div>

                    <!-- Desktop Menu -->
                    <div class="hidden sm:ml-6 sm:block">
                        <div class="flex space-x-4">
                            <a href="../index.php" aria-current="page"
                                class="rounded-md bg-[#618792] px-3 py-2 text-lg font-medium text-white dark:bg-gray-950/50 hover:bg-gray-950/70">Online
                                Bookstore</a>
                            <a href="#"
                                class="rounded-md px-3 py-2 text-lg font-medium text-[#618792] hover:bg-white/40">Books
                                under €5</a>
                            <a href="#"
                                class="rounded-md px-3 py-2 text-lg font-medium text-[#618792] hover:bg-white/40">Redaction
                                Selected</a>
                        </div>
                    </div>
                </div>

                <!-- Right Icons -->
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                    <?php

                    $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                    ?>
                    <a href="cart.php" class="relative">
                        <img src="../src/img/shopping-cart.png" alt="Cart" class="size-9" />
                        <?php if ($cartCount > 0): ?>
                            <span
                                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    </button>
                    <el-dropdown class="relative ml-3">
                        <button onclick="window.location.href='<?=
                            isset($_SESSION['user_id']) ? '../secure/user/myinfo.php' : 'auth.php'
                            ?>'" class="relative flex rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                            <span class="sr-only">Open user menu</span>
                            <img src="../src/img/avatar.png" alt="User Avatar" class="size-10 rounded-full" />
                        </button>
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
    <main class="flex-1 max-w-6xl mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="flex my-6" aria-label="Breadcrumb">
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
                        <span
                            class="ms-1 text-sm font-medium text-[#618792] md:ms-2">Complete order Information</span>
                    </div>
                </li>
            </ol>
        </nav>
        <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
            <h2 class="text-xl font-semibold mb-4">Delivery Information</h2>

            <?php if ($errors): ?>
                <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">
                    <?php foreach ($errors as $err)
                        echo "<p>$err</p>"; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 text-green-700 p-2 mb-4 rounded">
                    Your order has been placed successfully!
                </div>
            <?php else: ?>
                <form method="POST" class="space-y-4">
                    <form id="user-form">
                        <div>
                            <h3 class="text-base text-slate-900 font-semibold mb-4">User Details</h3>
                            <div class="space-y-3">
                                <div class="relative flex items-center">
                                    <input type="text" name="full_name" placeholder="Full Name"
                                        class="px-4 py-2.5 bg-white text-slate-900 rounded-md w-full text-sm border-b border-gray-200 pr-10 focus:border-gray-800 outline-none" />
                                </div>
                                <div class="relative flex items-center">
                                    <input type="text" name="address" placeholder="Address"
                                        class="px-4 py-2.5 bg-white text-slate-900 rounded-md w-full text-sm border-b border-gray-200 pr-10 focus:border-gray-800 outline-none" />
                                </div>
                                <div class="relative flex items-center">
                                    <input type="email" name="email" placeholder="Email"
                                        class="px-4 py-2.5 bg-white text-slate-900 rounded-md w-full text-sm border-b border-gray-200 pr-10 focus:border-gray-800 outline-none" />
                                </div>
                                <button type="submit"
                                    class="text-sm px-4 py-2.5 w-full font-medium tracking-wide bg-gray-800 hover:bg-gray-900 text-white rounded-md cursor-pointer mt-4">Save
                                    Place Order</button>
                            </div>
                        </div>

                    <?php endif; ?>
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