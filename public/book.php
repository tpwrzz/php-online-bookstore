<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
include('../src/scripts/db-connect.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid book ID.");
}

$bookId = intval($_GET['id']);

$stmt = $conn->prepare("SELECT title, description, price, cover_img, CONCAT(authors.first_name, ' ', authors.last_name) AS author_name, genres.name AS genre_name, books.author_id, books.genre_id FROM books JOIN authors ON books.author_id = authors.author_id JOIN genres ON books.genre_id = genres.genre_id WHERE books.book_id = ? LIMIT 1");
$stmt->bind_param("i", $bookId);
$stmt->execute();
$result = $stmt->get_result();


if ($result && $result->num_rows > 0) {
    $book = $result->fetch_assoc();
} else {
    die("Book not found.");
}

// Similar books query
$priceLower = $book['price'] - 2;
$priceUpper = $book['price'] + 2;

$priceLower = $book['price'] - 2;
$priceUpper = $book['price'] + 2;

$stmt2 = $conn->prepare("
    SELECT 
        books.book_id,
        books.title,
        books.price,
        books.cover_img,
        CONCAT(authors.first_name, ' ', authors.last_name) AS author_name
    FROM books
    JOIN authors ON books.author_id = authors.author_id
    JOIN genres ON books.genre_id = genres.genre_id
    WHERE books.book_id != ?
      AND (books.author_id = ? OR books.genre_id = ? OR (books.price BETWEEN ? AND ?))
    LIMIT 10
");
$stmt2->bind_param("iiidd", $bookId, $book['author_id'], $book['genre_id'], $priceLower, $priceUpper);
$stmt2->execute();
$similarResult = $stmt2->get_result();

$similarBooks = [];
if ($similarResult && $similarResult->num_rows > 0) {
    while ($row = $similarResult->fetch_assoc()) {
        $similarBooks[] = $row;
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
                            class="ms-1 text-sm font-medium text-[#618792] md:ms-2"><?= htmlspecialchars($book['title']) ?></span>
                    </div>
                </li>
            </ol>
        </nav>
        <!-- Book -->
        <div class="flex flex-col md:flex-row gap-10 mt-4">

            <!-- Cover -->
            <div
                class="flex-shrink-0 w-full md:w-[300px] h-[450px] bg-[#f8fafc] flex justify-center items-center rounded-2xl overflow-hidden border border-[#618792]/30">
                <img src="../src/img/covers/<?= $book['cover_img'] ?>" alt="<?= htmlspecialchars($book['title']) ?>"
                    class="object-contain w-full h-full">
            </div>

            <!-- Book Info -->
            <div class="flex-1 flex flex-col justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-3"><?= htmlspecialchars($book['title']) ?></h1>
                    <p class="mb-1"><strong>Author:</strong> <?= htmlspecialchars($book['author_name']) ?></p>
                    <p class="mb-1"><strong>Genre:</strong> <?= htmlspecialchars($book['genre_name']) ?></p>
                    <p class="text-[#618792] font-bold text-2xl mt-3 mb-5"><?= number_format($book['price'], 2) ?> €</p>
                    <hr class="border-[#618792]/50 my-4">
                    <p class="leading-relaxed"><?= nl2br(htmlspecialchars($book['description'])) ?></p>
                </div>

                <!-- Add to Cart Button -->
                <form action="../src/scripts/add-to-cart.php" method="POST"
                    class="mt-6 md:mt-10 flex items-center gap-4">
                    <input type="hidden" name="book_id" value="<?= $bookId ?>">
                    <!-- Counter -->
                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden h-12">
                        <button type="button" id="decrement-button" data-input-counter-decrement="quantity-input"
                            class="bg-[#f8fafc] dark:bg-[#f8fafc] dark:hover:bg-[#f8fafc] hover:bg-[#f8fafc]  rounded-s-lg p-3 h-11 focus:ring-gray-100 dark:focus:ring-gray-700 focus:ring-2 focus:outline-none">
                            <svg class="w-3 h-3 text-[#618792]/90 dark:text-[#618792]/90" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M1 1h16" />
                            </svg></button>
                        <input type="number" name="quantity" id="quantity-input" value="1" min="1"
                            class="w-20 text-center border-none focus:ring-0 focus:outline-none text-[#618792]/90 font-medium">
                        <button type="button" id="increment-button" data-input-counter-increment="quantity-input"
                            class="bg-[#f8fafc] dark:bg-[#f8fafc] dark:hover:bg-[#f8fafc]  hover:bg-[#618792]/90  rounded-e-lg p-3 h-11 focus:ring-gray-100 dark:focus:ring-gray-700 focus:ring-2 focus:outline-none">
                            <svg class="w-3 h-3 text-[#618792]/90 dark:text-[#618792]/90" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M9 1v16M1 9h16" />
                            </svg></button>
                    </div>

                    <!-- Add to cart button -->
                    <button type="submit"
                        class="w-full md:w-48 bg-[#618792]/90 text-white py-3 rounded-xl font-semibold hover:bg-[#618792] hover:shadow-lg transition">
                        Add to Cart
                    </button>
                </form>
            </div>

        </div>

        <!-- Similar Books Section -->
        <?php if (count($similarBooks) > 0): ?>
            <section class="mt-12 mb-4">
                <h2 class="text-2xl font-bold text-[#618792] mb-4">Similar Books</h2>

                <div class="relative">
                    <!-- Carousel Wrapper -->
                    <div class="overflow-hidden">
                        <div id="similar-books-carousel" class="flex transition-transform duration-500"
                            style="transform: translateX(0);">
                            <?php foreach ($similarBooks as $similar): ?>
                                <a href="book.php?id=<?= $similar['book_id'] ?>" class="flex-shrink-0 w-1/5 px-2 min-h-[250px]">
                                    <!-- fixed min-height for card -->
                                    <div
                                        class="bg-[#f8fafc] rounded-xl border border-[#618792]/30 overflow-hidden flex flex-col h-full">
                                        <img src="../src/img/covers/<?= $similar['cover_img'] ?>"
                                            alt="<?= htmlspecialchars($similar['title']) ?>"
                                            class="object-contain w-full h-48 flex-shrink-0">

                                        <div class="p-2 text-sm flex flex-col justify-between flex-1">
                                            <div class="mb-1">
                                                <!-- Fixed-height title container for up to 2 lines -->
                                                <p
                                                    class="font-semibold text-[#618792] text-sm h-[2.4rem] leading-tight overflow-hidden">
                                                    <?= htmlspecialchars($similar['title']) ?>
                                                </p>
                                                <p class="text-[#618792]/90 text-xs">
                                                    <?= htmlspecialchars($similar['author_name']) ?>
                                                </p>
                                            </div>
                                            <p class="text-[#618792] font-bold text-sm">
                                                <?= number_format($similar['price'], 2) ?> €
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <button id="prev-btn"
                        class="absolute top-1/2 -translate-y-1/2 -translate-x-2/2 left-0 p-2 bg-[#618792]/50 rounded-full hover:bg-[#618792]/70">
                        &#8592;
                    </button>
                    <button id="next-btn"
                        class="absolute top-1/2 -translate-y-1/2 translate-x-2/2 right-0 p-2 bg-[#618792]/50 rounded-full hover:bg-[#618792]/70">
                        &#8594;
                    </button>
                </div>
            </section>
        <?php endif; ?>

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
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const btn = document.getElementById("hamburger-btn");
        const menu = document.getElementById("mobile-menu");
        btn.addEventListener("click", () => {
            menu.classList.toggle("hidden");
            btn.querySelectorAll("svg").forEach(svg => svg.classList.toggle("hidden"));
        });
    });
    document.addEventListener("DOMContentLoaded", () => {
        // Hamburger
        const btn = document.getElementById("hamburger-btn");
        const menu = document.getElementById("mobile-menu");
        btn.addEventListener("click", () => {
            menu.classList.toggle("hidden");
            btn.querySelectorAll("svg").forEach(svg => svg.classList.toggle("hidden"));
        });

        // Similar books carousel
        const carousel = document.getElementById('similar-books-carousel');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        let index = 0;
        const itemWidth = carousel.children[0].offsetWidth + 16; // 16 = px padding

        function updateCarousel() {
            carousel.style.transform = `translateX(-${index * itemWidth}px)`;
        }

        prevBtn.addEventListener('click', () => {
            if (index > 0) index--;
            updateCarousel();
        });

        nextBtn.addEventListener('click', () => {
            if (index < carousel.children.length - 5) index++; // показываем 5 элементов
            updateCarousel();
        });
    });
    document.addEventListener("DOMContentLoaded", () => {
        const decrement = document.getElementById("decrement-button");
        const increment = document.getElementById("increment-button");
        const input = document.getElementById("quantity-input");

        decrement.addEventListener("click", () => {
            let val = parseInt(input.value) || 1;
            if (val > 1) input.value = val - 1;
        });

        increment.addEventListener("click", () => {
            let val = parseInt(input.value) || 1;
            input.value = val + 1;
        });
    });
</script>