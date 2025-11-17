<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
include('../src/scripts/db-connect.php');

// Структура корзины: [book_id => quantity]
$booksInCart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (!$isLoggedIn) {
    header("Location: auth.php");
    exit;
}
$cartItems = [];
$total = 0;
if (empty($booksInCart)) {
    $result = [];

} else {
    // Получаем книги из базы
    $bookIds = array_keys($booksInCart);
    $placeholders = implode(',', array_fill(0, count($bookIds), '?'));
    $sql = "SELECT book_id, title, price, cover_img FROM books WHERE book_id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $types = str_repeat('i', count($bookIds));
    $stmt->bind_param($types, ...$bookIds);
    $stmt->execute();
    $result = $stmt->get_result();

    // Собираем все книги в массив для удобного вывода

    while ($row = $result->fetch_assoc()) {
        $qty = $booksInCart[$row['book_id']];
        $subtotal = $row['price'] * $qty;
        $total += $subtotal;
        $cartItems[] = [
            'id' => $row['book_id'],
            'title' => $row['title'],
            'price' => $row['price'],
            'quantity' => $qty,
            'subtotal' => $subtotal,
            'cover' => $row['cover_img']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../src/img/books.png" type="image/x-icon">
    <title>Cart</title>
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
                        </div>
                    </div>
                </div>

                <!-- Right Icons -->
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                    <?php
                    // session_start();
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
                        <a href='<?= isset($_SESSION['user_id']) ? "../secure/user/myinfo.php" : "public/auth.php" ?>'
                            class="relative flex rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                            <span class="sr-only">Open user menu</span>
                            <img src="<?= isset($_SESSION['user_id']) ? '../src/img/avatar.png' : '../src/img/login.png' ?>"
                                alt="User Avatar" class="size-10 rounded-full" />
                        </a>
                    </el-dropdown>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="sm:hidden hidden px-2 pt-2 pb-3 space-y-1">
            <a href="../index.php" aria-current="page"
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
                        <span class="ms-1 text-sm font-medium text-[#618792] md:ms-2">Cart</span>
                    </div>
                </li>
            </ol>
        </nav>
        </ul>
        <!---Shoppint Cart--->
        <div class="max-w-5xl max-lg:max-w-2xl mx-auto bg-white p-4">
            <div class="border-b border-gray-300 pb-4">
                <h2 class="text-slate-900 text-2xl font-semibold">Shopping Cart</h2>
                <p class="text-sm text-slate-600 mt-2">Review the items in your cart.</p>
            </div>

            <div class="grid lg:grid-cols-3 gap-10 mt-12">
                <div class="lg:col-span-2 space-y-4">
                    <?php if (!empty($cartItems)): ?>
                    <?php foreach ($cartItems as $item): ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 items-start sm:gap-4 gap-6 cart-item"
                        data-price="<?= $item['price'] ?>">
                        <div class="col-span-2 flex items-start gap-4">
                            <div class="w-32 h-32 shrink-0 bg-gray-100 p-3 rounded-md">
                                <img src="../src/img/covers/<?= $item['cover'] ?>"
                                    class="w-full h-full object-contain" />
                            </div>
                            <div class="flex flex-col">
                                <h3 class="text-base font-semibold text-slate-900">
                                    <?= htmlspecialchars($item['title']) ?>
                                </h3>

                                <button type="button" data-remove-id="<?= $item['id'] ?>"
                                    class="mt-4 font-semibold text-red-500 text-xs flex items-center gap-2 shrink-0 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 fill-current inline"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M19 7a1 1 0 0 0-1 1v11.191A1.92 1.92 0 0 1 15.99 21H8.01A1.92 1.92 0 0 1 6 19.191V8a1 1 0 0 0-2 0v11.191A3.918 3.918 0 0 0 8.01 23h7.98A3.918 3.918 0 0 0 20 19.191V8a1 1 0 0 0-1-1Zm1-3h-4V2a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v2H4a1 1 0 0 0 0 2h16a1 1 0 0 0 0-2ZM10 4V3h4v1Z"
                                            data-original="#000000"></path>
                                        <path
                                            d="M11 17v-7a1 1 0 0 0-2 0v7a1 1 0 0 0 2 0Zm4 0v-7a1 1 0 0 0-2 0v7a1 1 0 0 0 2 0Z"
                                            data-original="#000000"></path>
                                    </svg>
                                    REMOVE
                                </button>
                            </div>
                        </div>

                        <div class="sm:ml-auto max-sm:flex max-sm:justify-between max-sm:gap-4 max-sm:col-span-full">
                            <h4 class="text-base font-semibold text-slate-900 item-price">
                                €
                                <?= number_format($item['price'], 2) ?>
                            </h4>
                            <div
                                class="flex items-center px-2.5 py-1.5 border border-gray-300 text-slate-900 text-xs font-medium rounded-md mt-4">
                                <span class="cursor-pointer decrement" data-id="<?= $item['id'] ?>">-</span>
                                <span class="mx-3 quantity">
                                    <?= $item['quantity'] ?>
                                </span>
                                <span class="cursor-pointer increment" data-id="<?= $item['id'] ?>">+</span>
                            </div>
                            <h4 class="text-base font-semibold text-slate-900 mt-4 subtotal">€
                                <?= number_format($item['price'] * $item['quantity'], 2) ?>
                            </h4>
                        </div>


                    </div>
                    <hr class="border-gray-300" />
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="text-center p-4 bg-gray-100 rounded text-gray-700">
                        Your cart is empty.
                    </div>
                    <?php endif; ?>
                </div>

                <div class="bg-gray-100 rounded-md p-4 h-max">

                    <ul class="text-slate-500 font-medium mt-6 space-y-4">
                        <li class="flex flex-wrap gap-4 text-sm text-slate-900" id="total">Total <span
                                class="ml-auto font-semibold">€
                                <?= number_format($total, 2) ?>
                            </span>
                        </li>
                    </ul>

                    <div class="mt-8 space-y-3">
                        <?php if (!empty($cartItems)): ?>
                        <button type="button" id="checkout-btn"
                            class="text-sm px-4 py-2.5 w-full font-medium tracking-wide bg-gray-800 hover:bg-gray-900 text-white rounded-md cursor-pointer">Checkout</button>
                        <?php endif ?>
                        <a href="../index.php"
                            class="text-sm px-4 py-2.5 w-full font-medium tracking-wide bg-transparent text-slate-900 border border-gray-300 rounded-md inline-block text-center">
                            Continue Shopping
                        </a>
                    </div>
                </div>
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
<script>
    document.addEventListener("DOMContentLoaded", () => {
        function updateCartTotals() {
            let total = 0;
            document.querySelectorAll('.cart-item').forEach(item => {
                const qty = parseInt(item.querySelector('.quantity').textContent);
                const price = parseFloat(item.dataset.price);
                const subtotalEl = item.querySelector('.subtotal');
                const subtotal = qty * price;
                subtotalEl.textContent = '€' + subtotal.toFixed(2);
                total += subtotal;
                console.log(`Item updated: price=${price}, qty=${qty}, subtotal=${subtotal}`);
            });

            const totalEl = document.querySelector('#total span');
            if (totalEl) totalEl.textContent = '€' + total.toFixed(2);
            console.log(`Cart totals updated: subtotal=${total}, total=${total}`);
        }

        document.querySelectorAll('.increment').forEach(btn => {
            btn.addEventListener('click', () => {
                const qtyEl = btn.parentElement.querySelector('.quantity');
                qtyEl.textContent = parseInt(qtyEl.textContent) + 1;
                console.log('Increment clicked, new qty:', qtyEl.textContent);
                updateCartTotals();
            });
        });

        document.querySelectorAll('.decrement').forEach(btn => {
            btn.addEventListener('click', () => {
                const qtyEl = btn.parentElement.querySelector('.quantity');
                let qty = parseInt(qtyEl.textContent);
                if (qty > 1) {
                    qtyEl.textContent = qty - 1;
                    console.log('Decrement clicked, new qty:', qtyEl.textContent);
                    updateCartTotals();
                } else {
                    console.log('Decrement clicked, qty is already 1');
                }
            });
        });

        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.closest('.cart-item').remove();
                console.log('Item removed');
                updateCartTotals();
            });
        });
    });

    document.getElementById('checkout-btn').addEventListener('click', () => {
        <?php if ($isLoggedIn): ?>
            // если залогинен, переходим на форму доставки
            window.location.href = 'order.php';
        <?php else: ?>
            // если не залогинен, переходим на форму логина/регистрации
            window.location.href = 'auth.php';
        <?php endif; ?>
    });
</script>