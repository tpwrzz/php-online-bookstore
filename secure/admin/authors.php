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

// ---------------- AUTHORS ----------------
// Pagination
$authorsPerPage = 12;
$currentAuthorsPage = isset($_GET['authors_page']) && is_numeric($_GET['authors_page']) ? (int) $_GET['authors_page'] : 1;
$authorsOffset = ($currentAuthorsPage - 1) * $authorsPerPage;

// Total count
$totalAuthors = $conn->query("SELECT COUNT(*) as total FROM authors")->fetch_assoc()['total'];
$totalAuthorsPages = max(ceil($totalAuthors / $authorsPerPage), 1);

$stmt = $conn->prepare("SELECT * FROM authors ORDER BY author_id ASC LIMIT ?, ?");
$stmt->bind_param("ii", $authorsOffset, $authorsPerPage);
$stmt->execute();
$authors = $stmt->get_result();


// Create author
if (isset($_POST['create_author'])) {
    $stmt = $conn->prepare("INSERT INTO authors (first_name, last_name) VALUES (?, ?)");
    $stmt->bind_param("ss", $_POST['first_name'], $_POST['last_name']);
    $stmt->execute();
    header("Location: authors.php");
    exit;
}

// Update author
if (isset($_POST['update_author'])) {
    $stmt = $conn->prepare("UPDATE authors SET first_name=?, last_name=? WHERE author_id=?");
    $stmt->bind_param("ssi", $_POST['first_name'], $_POST['last_name'], $_POST['author_id']);
    $stmt->execute();
    header("Location: authors.php");
    exit;
}

// Delete author (only if no books)
if (isset($_POST['delete_author'])) {
    $author_id = $_POST['author_id'];
    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM books WHERE author_id=?");
    $check->bind_param("i", $author_id);
    $check->execute();
    $cnt = $check->get_result()->fetch_assoc()['cnt'];

    if ($cnt == 0) {
        $stmt = $conn->prepare("DELETE FROM authors WHERE author_id=?");
        $stmt->bind_param("i", $author_id);
        $stmt->execute();
    }
    header("Location: authors.php");
    exit;
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../../src/img/books.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="flex flex-col min-h-screen bg-[#f8fafc]">
    <!-- NAV -->
    <nav class="relative bg-gray-800 dark:bg-blue-200">
        <div class="mx-20 max-w-full sm:px-6 lg:px-8">
            <div class="relative flex h-20 items-center justify-center-safe">
                <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
                    <div class="flex shrink-0 items-center">
                        <img src="../../src/img/books.png" alt="Bookstore" class="h-10 w-auto" />
                    </div>
                    <div class="hidden sm:ml-6 sm:block">
                        <div class="flex space-x-4">
                            <div
                                class="rounded-md bg-[#618792] px-3 py-2 text-lg font-medium text-white dark:bg-gray-950/50">
                                Online Bookstore Admin
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                    <div 
                            class="relative flex rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                            <span class="sr-only">Open user menu</span>
                            <img src="../../src/img/setting.png" alt="User Avatar" class="size-10 rounded-full" />
                        </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-full mx-20">
        <!-- Tabs -->
        <div class="text-sm font-medium text-center text-body border-b border-default mb-4">
            <ul class="flex flex-wrap -mb-px" id="tabs">
                <li class="me-2"><a href="orders.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Orders</a>
                </li>
                <li class="me-2"><a href="users.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Users</a>
                </li>
                <li class="me-2"><a href="genres.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Genres</a>
                </li>
                <li class="me-2"><a href="authors.php"
                        class="inline-block p-4 border-b border-blue-600 text-blue-600 rounded-t-base">Authors</a></li>
                <li class="me-2"><a href="books.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Books</a>
                </li>
                <li class="ml-auto">
                    <a href="../logout.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-red-600 hover:border-red-600">Logout</a>
                </li>
            </ul>
        </div>

        <!-- AUTHORS -->
        <div id="authors" class="tab-content mb-4">
            <h2 class="text-xl font-semibold mb-4">Manage Authors</h2>
            <h3 class="text-lg font-semibold mb-4">Add Author</h3>

            <!-- Create form -->
            <form method="POST" class="flex space-x-2 mb-2">
                <input type="text" name="first_name" placeholder="First name" required class="border px-2 py-1 rounded">
                <input type="text" name="last_name" placeholder="Last name" required class="border px-2 py-1 rounded">
                <button type="submit" name="create_author" class="bg-blue-500 text-white px-2 rounded">Add</button>
            </form>

            <!-- Authors table -->
            <table class="min-w-full bg-white border mb-6">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">First Name</th>
                        <th class="px-4 py-2 text-left">Last Name</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $authors->fetch_assoc()): ?>
                        <form method="POST" class="flex space-x-2">
                            <tr class="border-b">
                                <td class="px-4 py-2">
                                    <input type="hidden" name="author_id" value="<?= $row['author_id'] ?>">
                                    <?= $row['author_id'] ?>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="text" name="first_name" value="<?= htmlspecialchars($row['first_name']) ?>"
                                        class="border px-2 py-1 rounded w-32">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="text" name="last_name" value="<?= htmlspecialchars($row['last_name']) ?>"
                                        class="border px-2 py-1 rounded w-32">
                                </td>
                                <td class="px-4 py-2">
                                    <button type="submit" name="update_author"
                                        class="bg-green-500 text-white px-2 rounded">Update</button>
                                    <button type="submit" name="delete_author" class="bg-red-500 text-white px-2 rounded"
                                        onclick="return confirm('Delete author?')">Delete</button>

                                </td>
                            </tr>
                        </form>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php
            $queryParams = $_GET;
            unset($queryParams['authors_page']);
            $queryString = http_build_query($queryParams);
            ?>
            <nav class="flex justify-center items-center gap-x-1 mt-4" aria-label="authors Pagination">
                <a href="?<?= $queryString ?>&authors_page=<?= max($currentAuthorsPage - 1, 1) ?>"
                    class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentAuthorsPage == 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                    &lt; Prev
                </a>
                <?php for ($p = 1; $p <= $totalAuthorsPages; $p++): ?>
                    <a href="?<?= $queryString ?>&authors_page=<?= $p ?>"
                        class="px-3 py-2 rounded-lg <?= $p == $currentAuthorsPage ? 'bg-[#618792] text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
                <a href="?<?= $queryString ?>&authors_page=<?= min($currentAuthorsPage + 1, $totalAuthorsPages) ?>"
                    class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentAuthorsPage == $totalAuthorsPages ? 'opacity-50 pointer-events-none' : '' ?>">
                    Next &gt;
                </a>
            </nav>
        </div>
    </div>

    <footer class="z-20 w-full bg-blue-200 place-self-end mt-auto">
        <div class="mx-10 px-2 sm:px-6 lg:px-8">
            <div class="relative flex h-16 items-center justify-between">
                <span
                    class="rounded-md px-3 py-2 text-sm font-medium text-[#618792] hover:bg-white/20 hover:text-[#618792]">
                    © 2025 <a href="https://github.com/tpwrzz/php-online-bookstore" class="hover:underline">Poverjuc
                        Tatiana</a> IAFR2302
                </span>
            </div>
        </div>
    </footer>
</body>

</html>