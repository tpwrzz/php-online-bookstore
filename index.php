<!doctype html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="src/img/books.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body>
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
                        <img src="src/img/books.png" alt="Bookstore" class="h-10 w-auto" />
                    </div>

                    <!-- Desktop Menu -->
                    <div class="hidden sm:ml-6 sm:block">
                        <div class="flex space-x-4">
                            <a href="#" aria-current="page"
                                class="rounded-md bg-[#618792] px-3 py-2 text-lg font-medium text-white dark:bg-gray-950/50 hover:bg-gray-950/70">Online
                                Bookstore</a>
                            <a href="#"
                                class="rounded-md px-3 py-2 text-lg font-medium text-[#618792] hover:bg-white/40">Books
                                In English</a>
                            <a href="#"
                                class="rounded-md px-3 py-2 text-lg font-medium text-[#618792] hover:bg-white/40">Books
                                under $5</a>
                            <a href="#"
                                class="rounded-md px-3 py-2 text-lg font-medium text-[#618792] hover:bg-white/40">Rated
                                10</a>
                        </div>
                    </div>
                </div>

                <!-- Right Icons -->
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                    <button type="button"
                        class="relative rounded-full p-1 text-gray-400 focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500 dark:hover:text-[#1b1b1e]">
                        <span class="sr-only">Login</span>
                        <img src="src/img/shopping-cart.png" alt="" class="size-9" />
                    </button>
                    <el-dropdown class="relative ml-3">
                        <button
                            class="relative flex rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                            <span class="sr-only">Open user menu</span>
                            <img src="src/img/avatar.png" alt="" class="size-10 rounded-full" />
                        </button>
                        <el-menu anchor="bottom end" popover
                            class="w-48 origin-top-right rounded-md bg-white py-1 shadow-lg outline outline-black/5 dark:bg-gray-800 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10">
                            <a href="#"
                                class="block px-4 py-2 text-sm text-[#1b1b1e] focus:bg-gray-100 dark:text-gray-300 dark:focus:bg-white/5">Your
                                profile</a>
                            <a href="#"
                                class="block px-4 py-2 text-sm text-[#1b1b1e] focus:bg-gray-100 dark:text-gray-300 dark:focus:bg-white/5">Settings</a>
                            <a href="#"
                                class="block px-4 py-2 text-sm text-[#1b1b1e] focus:bg-gray-100 dark:text-gray-300 dark:focus:bg-white/5">Sign
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
                In English</a>
            <a href="#"
                class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-white/5 hover:text-[#1b1b1e]">Books
                under $5</a>
            <a href="#"
                class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-white/5 hover:text-[#1b1b1e]">Rated
                10</a>
        </div>
    </nav>

    <div class="flex flex-col md:flex-row gap-4 bg-[#f8fafc] max-w-full">
        <!-- Sidebar -->
        <aside class="ml-10 mb-5 mt-5 mr-5 max-w-full md:w-64 sticky">
            <h2 class="text-xl font-semibold mb-4">Filters</h2>

            <!-- Genre Filter -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-[#1b1b1e] mb-2">Genre</label>
                <select
                    class="w-full border border-gray-300 rounded-md p-2 text-[#1b1b1e] focus:outline-none focus:ring-2 focus:ring-blue-500"
                    name="genre">
                    <option value="">All</option>
                    <?php
                    include('src/scripts/db-connect.php');

                    $genreQuery = "SELECT genre_id, name FROM genres ORDER BY name ASC";
                    $result = $conn->query($genreQuery);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['genre_id'] . '">' . htmlspecialchars($row['name']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- Price Filter -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-[#1b1b1e] mb-2">Price range</label>
                <div class="flex items-center space-x-2">
                    <input type="number" placeholder="Min" min="0"
                        class="w-1/2 border border-gray-300 rounded-md p-2 text-[#1b1b1e] focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="number" placeholder="Max" min="0"
                        class="w-1/2 border border-gray-300 rounded-md p-2 text-[#1b1b1e] focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <hr class="my-4">

            <h2 class="text-xl font-semibold mb-4">Sort By</h2>

            <!-- Sort Options -->
            <div class="mb-4">
                <select id="sort"
                    class="w-full border border-gray-300 rounded-md p-2 text-[#1b1b1e] focus:outline-none focus:ring-2 focus:ring-[#8AB2C1]">
                    <option>Default</option>
                    <option>Price: Low to High</option>
                    <option>Price: High to Low</option>
                    <option>Newest</option>
                </select>
            </div>

            <button class="w-full bg-[#618792]/80 text-white py-2 rounded-md font-medium hover:bg-[#618792] transition">
                Apply
            </button>
        </aside>

        <?php
        include('src/scripts/db-connect.php');

        // Cards per page
        $cardsPerPage = 12;

        // Get current page from URL, default 1
        $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($currentPage < 1)
            $currentPage = 1;

        // Count total books for pagination
        $countSql = "SELECT COUNT(*) as total FROM books";
        $countResult = $conn->query($countSql);
        $totalCards = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($totalCards / $cardsPerPage);

        // Calculate OFFSET
        $offset = ($currentPage - 1) * $cardsPerPage;

        // Fetch only the current page books
        $sql = "
SELECT 
    books.book_id,
    books.title,
    books.price,
    books.cover_img,
    CONCAT(authors.first_name, ' ', authors.last_name) AS author_name,
    genres.name AS genre_name
FROM books
JOIN authors ON books.author_id = authors.author_id
JOIN genres ON books.genre_id = genres.genre_id
LIMIT $cardsPerPage OFFSET $offset
";

        $result = $conn->query($sql);
        ?>
        <main class="flex-1 m-5">
            <div class="grid gap-5 grid-cols-1 sm:grid-cols-2 md:grid-cols-4 max-w-full mx-5">
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <div class="w-80 rounded-lg shadow-sm overflow-hidden flex flex-col">
                        <a href="#">
                            <!-- Keep aspect ratio, fit into max height if too big -->
                            <img class="w-full max-h-80 object-contain" src="src/img/covers/<?= $row['cover_img'] ?>"
                                alt="<?= htmlspecialchars($row['title']) ?>" />
                        </a>
                        <div class="px-4 pb-4 mt-2 flex flex-col flex-grow">
                            <a href="#">
                                <h5 class="text-lg font-semibold tracking-tight text-[#618792] dark:text-[#1b1b1e]">
                                    <?= htmlspecialchars($row['title']) ?>
                                </h5>
                            </a>
                            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($row['author_name']) ?> |
                                <?= htmlspecialchars($row['genre_name']) ?>
                            </p>
                            <div class="flex items-center justify-between mt-3">
                                <span
                                    class="text-xl font-bold text-[#618792] dark:text-[#1b1b1e]">$<?= $row['price'] ?></span>
                                <a href="#"
                                    class="text-[#f8fafc] bg-[#001021]/50 hover:bg-[#001021]/80 focus:ring-4 focus:outline-none font-medium rounded-lg text-sm px-3 py-2 text-center dark:bg-[#001021]/50 dark:hover:bg-[#001021]/80">
                                    Add
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <!-- Pagination -->
            <nav class="flex justify-center items-center gap-x-1 mt-4" aria-label="Pagination">
                <!-- Previous button -->
                <a href="?page=<?= max($currentPage - 1, 1) ?>"
                    class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentPage == 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                    &lt; Prev
                </a>

                <!-- Page numbers -->
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?page=<?= $p ?>"
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
    <footer class="z-20 w-full bg-blue-200 ">
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
        const openIcon = btn.querySelector("svg.block");
        const closeIcon = btn.querySelector("svg.hidden");

        btn.addEventListener("click", () => {
            menu.classList.toggle("hidden");
            openIcon.classList.toggle("hidden");
            closeIcon.classList.toggle("hidden");
        });
    });
</script>