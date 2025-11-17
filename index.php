<?php
session_start();
include('src/scripts/db-connect.php');
$isLoggedIn = isset($_SESSION['user_id']); ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="src/img/books.png" type="image/x-icon">
    <title>Bookstore Online</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="flex flex-col min-h-screen bg-[#f8fafc]">
    <nav class="relative bg-gray-800 dark:bg-blue-200">
        <div class="mx-20 max-w-full sm:px-6 lg:px-8">
            <div class="relative flex h-20 items-center justify-center-safe">
                <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
                    <button id="hamburger-btn" type="button"
                        class="relative inline-flex items-center justify-center rounded-md p-2 text-blue-800 hover:bg-white/5 hover:text-[#1b1b1e] focus:outline-2 focus:-outline-offset-1 focus:outline-indigo-500">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
                    <div class="flex shrink-0 items-center">
                        <img src="src/img/books.png" alt="Bookstore" class="h-10 w-auto" />
                    </div>
                    <div class="hidden sm:ml-6 sm:block">
                        <div class="flex space-x-4">
                            <a href="#" aria-current="page"
                                class="rounded-md bg-[#618792] px-3 py-2 text-lg font-medium text-white dark:bg-gray-950/50 hover:bg-gray-950/70">Online
                                Bookstore</a>
                        </div>
                    </div>
                </div>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                    <?php
                    $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                    ?>
                    <a href="public/cart.php" class="relative">
                        <img src="src/img/shopping-cart.png" alt="Cart" class="size-9" />
                        <?php if ($cartCount > 0): ?>
                            <span
                                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <el-dropdown class="relative ml-3">
                        <a href='<?= isset($_SESSION['user_id']) ? "secure/user/myinfo.php" : "public/auth.php" ?>'
                            class="relative flex rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                            <span class="sr-only">Open user menu</span>
                            <img src="<?= isset($_SESSION['user_id']) ? 'src/img/avatar.png' : 'src/img/login.png' ?>"
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

    <div class="flex flex-col md:flex-row gap-4 bg-[#f8fafc] max-w-full ">
        <!-- Sidebar -->
        <aside class="ml-10 mb-5 mt-5 mr-5 max-w-full md:w-64 sticky">
            <h2 class="text-xl font-semibold mb-4">Filters</h2>
            <form method="GET" action="" class="space-y-4">
                <!-- Genre Filter -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-[#1b1b1e] mb-2">Genre</label>
                    <select name="genre"
                        class="w-full border border-gray-300 rounded-md p-2 text-[#1b1b1e] focus:outline-none focus:ring-2 focus:ring-[#8AB2C1]">
                        <option value="">All</option>
                        <?php
                        include('src/scripts/db-connect.php');
                        $genreQuery = "SELECT genre_id, name FROM genres ORDER BY name ASC";
                        $result = $conn->query($genreQuery);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $selected = (isset($_GET['genre']) && $_GET['genre'] == $row['genre_id']) ? 'selected' : '';
                                echo '<option value="' . $row['genre_id'] . '" ' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- Price Filter -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-[#1b1b1e] mb-2">Price range</label>
                    <div class="flex items-center space-x-2">
                        <input type="number" name="min_price" placeholder="Min" min="0"
                            value="<?= isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '' ?>"
                            class="w-1/2 border border-gray-300 rounded-md p-2 text-[#1b1b1e] focus:outline-none focus:ring-2 focus:ring-[#8AB2C1]">
                        <input type="number" name="max_price" placeholder="Max" min="0"
                            value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '' ?>"
                            class="w-1/2 border border-gray-300 rounded-md p-2 text-[#1b1b1e] focus:outline-none focus:ring-2 focus:ring-[#8AB2C1]">
                    </div>
                </div>

                <hr class="my-4">

                <h2 class="text-xl font-semibold mb-4">Sort By</h2>

                <!-- Sort Options -->
                <div class="mb-4">
                    <select name="sort" id="sort"
                        class="w-full border border-gray-300 rounded-md p-2 text-[#1b1b1e] focus:outline-none focus:ring-2 focus:ring-[#8AB2C1]">
                        <option value="">Default</option>
                        <option value="price_asc" <?= isset($_GET['sort']) && $_GET['sort'] == 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= isset($_GET['sort']) && $_GET['sort'] == 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="newest" <?= isset($_GET['sort']) && $_GET['sort'] == 'newest' ? 'selected' : '' ?>>
                            Newest</option>
                    </select>
                </div>

                <div class="flex space-x-2">
                    <a href="index.php"
                        class="flex-1 text-[#618792] border border-[#618792] py-2 rounded-md font-medium text-center hover:bg-[#618792] hover:text-white transition">
                        Reset
                    </a>
                    <button type="submit"
                        class="flex-1 bg-[#618792]/80 text-white py-2 rounded-md font-medium hover:bg-[#618792] transition">
                        Apply
                    </button>

                </div>
            </form>
        </aside>

        <?php
        // === Pagination ===
        $cardsPerPage = 12;
        $currentPage = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $offset = ($currentPage - 1) * $cardsPerPage;

        // === Filters ===
        $whereClauses = [];
        $params = [];
        $types = '';

        if (!empty($_GET['genre'])) {
            $whereClauses[] = "books.genre_id = ?";
            $params[] = (int) $_GET['genre'];
            $types .= 'i';
        }
        if (!empty($_GET['min_price'])) {
            $whereClauses[] = "books.price >= ?";
            $params[] = (float) $_GET['min_price'];
            $types .= 'd';
        }
        if (!empty($_GET['max_price'])) {
            $whereClauses[] = "books.price <= ?";
            $params[] = (float) $_GET['max_price'];
            $types .= 'd';
        }

        $whereSQL = '';
        if (!empty($whereClauses)) {
            $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
        }

        // === Sorting ===
        $orderSQL = '';
        if (!empty($_GET['sort'])) {
            switch ($_GET['sort']) {
                case 'price_asc':
                    $orderSQL = 'ORDER BY books.price ASC';
                    break;
                case 'price_desc':
                    $orderSQL = 'ORDER BY books.price DESC';
                    break;
                case 'newest':
                    $orderSQL = 'ORDER BY books.published_at ASC';
                    break;
            }
        }

        // === Count total books ===
        $countSql = "SELECT COUNT(*) as total FROM books $whereSQL";
        $stmtCount = $conn->prepare($countSql);
        if ($types)
            $stmtCount->bind_param($types, ...$params);
        $stmtCount->execute();
        $totalCards = $stmtCount->get_result()->fetch_assoc()['total'];
        $totalPages = ceil($totalCards / $cardsPerPage);

        // === Fetch books ===
        $sql = "
SELECT books.book_id, books.title, books.price, books.cover_img,
       CONCAT(authors.first_name, ' ', authors.last_name) AS author_name,
       genres.name AS genre_name
FROM books
JOIN authors ON books.author_id = authors.author_id
JOIN genres ON books.genre_id = genres.genre_id
$whereSQL
$orderSQL
LIMIT ? OFFSET ?
";

        $paramsWithLimit = $params;
        $typesWithLimit = $types . 'ii';
        $paramsWithLimit[] = $cardsPerPage;
        $paramsWithLimit[] = $offset;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($typesWithLimit, ...$paramsWithLimit);
        $stmt->execute();
        $result = $stmt->get_result();
        ?>

        <main class="flex-1 m-5">
            <div class="grid gap-5 grid-cols-1 sm:grid-cols-2 md:grid-cols-4 max-w-full mx-5">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div
                            class="bg-white rounded-2xl shadow hover:shadow-md transition transform hover:-translate-y-1 flex flex-col justify-between">
                            <a href="public/book.php?id=<?= $row['book_id'] ?>" class="block">
                                <div
                                    class="aspect-[3/4] w-full overflow-hidden rounded-t-2xl flex justify-center items-center bg-gray-100">
                                    <img src="src/img/covers/<?= $row['cover_img'] ?>"
                                        alt="<?= htmlspecialchars($row['title']) ?>" class="object-contain w-[340px] h-[420px]">
                                </div>
                            </a>
                            <div class="flex justify-between items-center p-4">
                                <div class="flex flex-col w-[70%]">
                                    <h3 class="text-lg font-semibold text-[#1b1b1e] truncate">
                                        <?= htmlspecialchars($row['title']) ?>
                                    </h3>
                                    <p class="text-sm text-gray-600 mb-1 truncate"><?= htmlspecialchars($row['author_name']) ?>
                                    </p>
                                    <p class="text-sm text-gray-500 mb-1 truncate"><?= htmlspecialchars($row['genre_name']) ?>
                                    </p>
                                    <p class="font-medium text-[#618792]"><?= number_format($row['price'], 2) ?> €</p>
                                </div>
                                <form action="src/scripts/add-to-cart.php" method="POST" class="ml-3 flex-shrink-0">
                                    <input type="hidden" name="book_id" value="<?= $row['book_id'] ?>">
                                    <button type="submit"
                                        class="bg-[#618792]/90 text-white py-2 px-4 rounded-md font-medium hover:bg-[#618792] transition">
                                        Add
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full text-center text-gray-500  text-lg">
                        No books found.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php
            // Сохраняем все GET-параметры кроме 'page'
            $queryParams = $_GET;
            unset($queryParams['page']);
            $queryString = http_build_query($queryParams);
            ?>
            <nav class="flex justify-center items-center gap-x-1 mt-4" aria-label="Pagination">
                <!-- Previous button -->
                <a href="?page=<?= max($currentPage - 1, 1) ?>"
                    class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentPage == 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                    &lt; Prev
                </a>

                <!-- Page numbers -->
                <?php if ($totalPages == 0)
                    $totalPages = 1;
                for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?<?= $queryString ?>&page=<?= $p ?>"
                        class="px-3 py-2 rounded-lg <?= $p == $currentPage ? 'bg-[#618792] text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>

                <!-- Next button -->
                <a href="?page=<?= min($currentPage + 1, $totalPages) ?>"
                    class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentPage == $totalPages ? 'opacity-50 pointer-events-none' : '' ?>">
                    Next &gt;
                </a>
            </nav>
        </main>
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
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const btn = document.getElementById("hamburger-btn");
        const menu = document.getElementById("mobile-menu");
        const openIcon = btn.querySelector("svg.block");
        const closeIcon = btn.querySelector("svg.hidden");

        btn.addEventListener("click", () => {
            menu.classList.toggle("hidden");
            openIcon.classList.toggle("hidden");
            closeIcon.classList.toggle("hidden");
        });
    });
</script>