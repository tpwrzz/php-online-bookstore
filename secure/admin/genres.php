<?php
session_start();
include('../../src/scripts/db-connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../public/auth.php");
    exit;
}

// Check if user is admin
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$role = $stmt->get_result()->fetch_assoc()['role'];

if ($role !== 'ADMIN') {
    die("Access denied");
}

// ---------------- GENRES ----------------
// genres Pagination
$genresPerPage = 12;
$currentgenresPage = isset($_GET['genres_page']) && is_numeric($_GET['genres_page']) ? (int) $_GET['genres_page'] : 1;
$genresOffset = ($currentgenresPage - 1) * $genresPerPage;

// Total genres count
$totalgenres = $conn->query("SELECT COUNT(*) as total FROM genres")->fetch_assoc()['total'];
$totalgenresPages = max(ceil($totalgenres / $genresPerPage), 1);
$genres = $conn->query("SELECT * FROM genres ORDER BY genre_id ASC");

// Update genre
if (isset($_POST['update_genre'])) {
    $stmt = $conn->prepare("UPDATE genres SET name=? WHERE genre_id=?");
    $stmt->bind_param("si", $_POST['name'], $_POST['genre_id']);
    $stmt->execute();
    header("Location: genres.php");
    exit;
}

// Delete genre (only if no books)
if (isset($_POST['delete_genre'])) {
    $genre_id = $_POST['genre_id'];
    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM books WHERE genre_id=?");
    $check->bind_param("i", $genre_id);
    $check->execute();
    $cnt = $check->get_result()->fetch_assoc()['cnt'];
    if ($cnt == 0) {
        $stmt = $conn->prepare("DELETE FROM genres WHERE genre_id=?");
        $stmt->bind_param("i", $genre_id);
        $stmt->execute();
    }
    header("Location: genres.php");
    exit;
}

// Create genre
if (isset($_POST['create_genre'])) {
    $stmt = $conn->prepare("INSERT INTO genres (name) VALUES (?)");
    $stmt->bind_param("s", $_POST['name']);
    $stmt->execute();
    header("Location: genres.php");
    exit;
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../../src/img/books.png" type="image/x-icon">
    <title>Genres Admin Page</title>
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
                        <a href='<?=
                            isset($_SESSION['user_id']) ? 'secure/user/myinfo.php' : 'public/auth.php'
                            ?>'
                            class="relative flex rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                            <span class="sr-only">Open user menu</span>
                            <img src="../../src/img/setting.png" alt="User Avatar" class="size-10 rounded-full" />
                        </a>
                    </el-dropdown>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-full mx-20">
        <!-- Tabs -->
        <div class="text-sm font-medium text-center text-body border-b border-default mb-4">
            <ul class="flex flex-wrap -mb-px" id="tabs">
                <li class="me-2"><a href="orders.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600 active">Orders</a>
                </li>
                <li class="me-2"><a href="users.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Users</a>
                </li>
                <li class="me-2"><a href="genres.php"
                        class="inline-block p-4 border-b  rounded-t-base text-blue-600 border-blue-600">Genres</a>
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

            <!-- Genres Tab -->
            <div id="genres" class="tab-content  mb-4">
                <h2 class="text-xl font-semibold mb-4">Manage Genres</h2>
                <h3 class="text-lg font-semibold mb-4">Add Genre</h3>
                <form method="POST" class="flex space-x-2 mb-2">
                    <input type="text" name="name" placeholder="New Genre" required class="border px-2 py-1 rounded">
                    <button type="submit" name="create_genre" class="bg-blue-500 text-white px-2 rounded">Add</button>
                </form>
                <table class="min-w-full bg-white border mb-6">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $genres->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2"><?= $row['genre_id'] ?></td>
                                <td class="px-4 py-2">
                                    <form method="POST" class="flex space-x-2">
                                        <input type="hidden" name="genre_id" value="<?= $row['genre_id'] ?>">
                                        <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>"
                                            class="border px-2 py-1 rounded">
                                        <button type="submit" name="update_genre"
                                            class="bg-green-500 text-white px-2 rounded">Update</button>
                                    </form>
                                </td>
                                <td class="px-4 py-2">
                                    <form method="POST" class="flex space-x-2"><button type="submit" name="delete_genre"
                                            class="bg-red-500 text-white px-2 rounded"
                                            onclick="return confirm('Delete genre?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php $queryParams = $_GET;
                unset($queryParams['genres_page']);
                $queryString = http_build_query($queryParams);
                ?>
                <nav class="flex justify-center items-center gap-x-1 mt-4" aria-label="genres Pagination">
                    <a href="?<?= $queryString ?>&genres_page=<?= max($currentgenresPage - 1, 1) ?>"
                        class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentgenresPage == 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                        &lt; Prev
                    </a>

                    <?php for ($p = 1; $p <= $totalgenresPages; $p++): ?>
                        <a href="?<?= $queryString ?>&genres_page=<?= $p ?>"
                            class="px-3 py-2 rounded-lg <?= $p == $currentgenresPage ? 'bg-[#618792] text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <a href="?<?= $queryString ?>&genres_page=<?= min($currentgenresPage + 1, $totalgenresPages) ?>"
                        class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentgenresPage == $totalgenresPages ? 'opacity-50 pointer-events-none' : '' ?>">
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