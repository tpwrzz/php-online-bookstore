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

// ---------------- BOOKS ----------------
$booksPerPage = 10;
$currentbooksPage = isset($_GET['books_page']) && is_numeric($_GET['books_page']) ? (int) $_GET['books_page'] : 1;
$booksOffset = ($currentbooksPage - 1) * $booksPerPage;

$totalbooks = $conn->query("SELECT COUNT(*) as total FROM books")->fetch_assoc()['total'];
$totalbooksPages = max(ceil($totalbooks / $booksPerPage), 1);

// Helper: sanitize string
function sanitize($str)
{
    return trim(htmlspecialchars($str, ENT_QUOTES));
}

if (isset($_POST['create_book'])) {
    $title = sanitize($_POST['title'] ?? '');
    $author_id = (int) ($_POST['author_id'] ?? 0);
    $genre_id = (int) ($_POST['genre_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $stock_qty = intval($_POST['stock_qty'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $published_at = sanitize($_POST['published_at'] ?? '');

    $errors = [];

    // Validation
    if ($title === '' || strlen($title) > 255)
        $errors[] = "Title is required (max 255 chars).";
    if ($author_id <= 0)
        $errors[] = "Please select a valid author.";
    if ($genre_id <= 0)
        $errors[] = "Please select a valid genre.";
    if ($price < 0 || $price > 9999)
        $errors[] = "Price must be between 0 and 9999.";
    if ($stock_qty < 0 || $stock_qty > 9999)
        $errors[] = "Stock quantity must be between 0 and 9999.";
    if (strlen($description) > 1000)
        $errors[] = "Description max length is 1000 chars.";

    $coverFileName = null;
    if (!empty($_FILES['cover_image']['name'])) {
        $coverFileName = basename($_FILES['cover_image']['name']);
        chdir("../../");
        $targetPath = getcwd() . "/src/img/covers/" . $coverFileName;
        if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetPath)) {
            $errors[] = "Image upload failed.";
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO books (title, author_id, genre_id, price, stock_qty, cover_img, description,published_at)
            VALUES (?, ?, ?, ?, ?, ?, ?,?)
        ");
        $stmt->bind_param("siidisss", $title, $author_id, $genre_id, $price, $stock_qty, $coverFileName, $description, $published_at);
        $stmt->execute();
        header("Location: books.php");
        exit;
    }
}

if (isset($_POST['update_book'])) {
    $book_id = intval($_POST['book_id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $author_id = intval($_POST['author_id'] ?? 0);
    $genre_id = intval($_POST['genre_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $stock_qty = intval($_POST['stock_qty'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $published_at = sanitize($_POST['published_at'] ?? '');

    $errors = [];

    // Validation
    if ($book_id <= 0)
        $errors[] = "Invalid book ID.";
    if ($title === '' || strlen($title) > 255)
        $errors[] = "Title is required (max 255 chars).";
    if ($author_id <= 0)
        $errors[] = "Please select a valid author.";
    if ($genre_id <= 0)
        $errors[] = "Please select a valid genre.";
    if ($price < 0 || $price > 9999)
        $errors[] = "Price must be between 0 and 9999.";
    if ($stock_qty < 0 || $stock_qty > 9999)
        $errors[] = "Stock quantity must be between 0 and 9999.";
    if (strlen($description) > 1000)
        $errors[] = "Description max length is 1000 chars.";

    $coverFileName = null;
    if (!empty($_FILES['cover_image']['name'])) {
        $coverFileName = basename($_FILES['cover_image']['name']);
        chdir("../../");
        $targetPath = getcwd() . "/src/img/covers/" . $coverFileName;
        if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetPath)) {
            $errors[] = "Image upload failed.";
        }
    }

    if (empty($errors)) {
        if ($coverFileName !== null) {
            $stmt = $conn->prepare("
                UPDATE books
                SET title=?, author_id=?, genre_id=?, price=?, stock_qty=?, cover_img=?, description=?, published_at=?
                WHERE book_id=?
            ");
            $stmt->bind_param("siidisssi", $title, $author_id, $genre_id, $price, $stock_qty, $coverFileName, $description, $published_at, $book_id);
        } else {
            $stmt = $conn->prepare("
                UPDATE books
                SET title=?, author_id=?, genre_id=?, price=?, stock_qty=?, description=?, published_at=?
                WHERE book_id=?
            ");
            $stmt->bind_param("siidissi", $title, $author_id, $genre_id, $price, $stock_qty, $description, $published_at, $book_id);
        }
        $stmt->execute();
        header("Location: books.php?books_page=$currentBooksPage");
        exit;
    }
}

if (isset($_POST['delete_book'])) {
    $book_id = $_POST['book_id'];

    $check = $conn->prepare("
        SELECT COUNT(*) AS cnt
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.book_id = ? AND o.status NOT IN ('completed', 'cancelled')
    ");
    $check->bind_param("i", $book_id);
    $check->execute();
    $cnt = $check->get_result()->fetch_assoc()['cnt'];

    if ($cnt == 0) {
        $stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();

        $conn->query("DELETE FROM order_items WHERE book_id = $book_id");
    } else {
        echo "<div class='text-red-600 text-center mb-4'>
                ❌ Cannot delete: this book is in an active order.
              </div>";
    }

    header("Location: books.php?books_page=$currentBooksPage");
    exit;
}

$books = $conn->query("
    SELECT b.book_id, b.title, b.author_id, b.price, b.description, b.stock_qty,b.published_at, b.cover_img, g.name AS genre, b.genre_id
    FROM books b
    LEFT JOIN genres g ON b.genre_id = g.genre_id
    ORDER BY b.book_id ASC
    LIMIT $booksOffset, $booksPerPage
");
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../../src/img/books.png" type="image/x-icon">
    <title>Books Admin Page</title>
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

    <div class="max-w-full mx-20">
        <!-- Tabs -->
        <div class="text-sm font-medium text-center text-body border-b border-default mb-4">
            <ul class="flex flex-wrap -mb-px" id="tabs">
                <li class="me-1"><a href="orders.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Orders</a>
                </li>
                <li class="me-1"><a href="users.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Users</a>
                </li>
                <li class="me-1"><a href="genres.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Genres</a>
                </li>
                <li class="me-1"><a href="authors.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Authors</a>
                </li>
                <li class="me-1"><a href="books.php"
                        class="inline-block p-4 border-b  rounded-t-base text-blue-600 border-blue-600">Books</a>
                </li>
                <li class="ml-auto">
                    <a href="../logout.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-red-600 hover:border-red-600">Logout</a>
                </li>
            </ul>

        </div>

        <!-- Tab Contents -->
        <div id="tab-contents" class="mb-4">
            <!-- Books Tab -->
            <div id="books" class="tab-content mb-4">
                <h2 class="text-xl font-semibold mb-4">Manage Books</h2>
                <?php if (!empty($errors)): ?>
                    <div class="mb-4 p-2 border border-red-500 bg-red-100 text-red-700 rounded">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <h3 class="text-lg font-semibold mb-4">Add Book</h3>
                <form method="POST" enctype="multipart/form-data" class="flex flex-wrap gap-2 mb-6">
                    <!-- Title -->
                    <input type="text" name="title" placeholder="Title" required class="border px-2 py-1 rounded w-40">
                    <!-- Description -->
                    <textarea name="description" required placeholder="Description (optional, max 1000 chars)"
                        class="border px-2 py-1 rounded w-full md:w-96 resize-none"></textarea>
                    <!-- Genre -->
                    <select name="genre_id" required class="border px-2 py-1 rounded w-40">
                        <option value="">Select genre</option>
                        <?php
                        $allGenres = $conn->query("SELECT genre_id, name FROM genres ORDER BY name");
                        while ($g = $allGenres->fetch_assoc()):
                            ?>
                            <option value="<?= $g['genre_id'] ?>">
                                <?= htmlspecialchars($g['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <!-- Author -->
                    <select name="author_id" required class="border px-2 py-1 rounded w-40">
                        <option value="">Select author</option>
                        <?php
                        $allAuthors = $conn->query("SELECT author_id, first_name, last_name FROM authors ORDER BY first_name");
                        while ($a = $allAuthors->fetch_assoc()):
                            ?>
                            <option value="<?= $a['author_id'] ?>">
                                <?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <!-- Price -->
                    <input type="number" name="price" placeholder="Price (e.g. 3.99)" required
                        class="border px-2 py-1 rounded w-32" step="0.01" min="0">

                    <!-- Stock Quantity -->
                    <input type="number" name="stock_qty" placeholder="Qty" required
                        class="border px-2 py-1 rounded w-24" step="1" min="0">
                    <!-- Published -->
                    <input type="date" name="published_at" required class="border px-2 py-1 rounded w-32">
                    <!-- Cover Image -->
                    <input type="file" name="cover_image" required accept="image/*"
                        class="border px-2 py-1 rounded w-60">

                    <!-- Submit -->
                    <button type="submit" name="create_book" class="bg-blue-500 text-white px-4 py-1 rounded">
                        Add
                    </button>
                </form>

                <table class="min-w-full bg-white border mb-6">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-2">ID</th>
                            <th class="px-4 py-2">Title</th>
                            <th class="px-4 py-2">Description</th>
                            <th class="px-4 py-2">Genre</th>
                            <th class="px-4 py-2">Author</th>
                            <th class="px-4 py-2">Price (€)</th>
                            <th class="px-4 py-2">Stock q-ty</th>
                            <th class="px-4 py-2">Published Date</th>
                            <th class="px-4 py-2">Cover image</th>
                            <th class="px-4 py-2">Actions</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $books->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2"><?= $row['book_id'] ?></td>

                                <td class="px-4 py-2">
                                    <form method="POST" enctype="multipart/form-data" class="flex flex-col space-y-2">
                                        <input type="hidden" name="book_id" value="<?= $row['book_id'] ?>">
                                        <input type="text" name="title" required
                                            value="<?= htmlspecialchars($row['title']) ?>"
                                            class="border px-2 py-1 rounded w-full">
                                </td>
                                <td class="px-4 py-2">
                                    <textarea name="description" required
                                        class="border px-2 py-1 rounded w-full h-16 resize-none"
                                        placeholder="Description (max 1000 chars)"><?= htmlspecialchars($row['description'] ?? '') ?></textarea>
                                </td>
                                <!-- Genre -->
                                <td class="px-4 py-2">
                                    <select name="genre_id" required class="border px-2 py-1 rounded w-full">
                                        <?php
                                        $allGenres = $conn->query("SELECT genre_id, name FROM genres ORDER BY name");
                                        while ($g = $allGenres->fetch_assoc()):
                                            ?>
                                            <option value="<?= $g['genre_id'] ?>" <?= $g['genre_id'] == $row['genre_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($g['name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </td>

                                <!-- Author -->
                                <td class="px-4 py-2">
                                    <select name="author_id" required class="border px-2 py-1 rounded w-full">
                                        <?php
                                        $allAuthors = $conn->query("SELECT author_id,  first_name, last_name FROM authors ORDER BY first_name");
                                        while ($a = $allAuthors->fetch_assoc()):
                                            ?>
                                            <option value="<?= $a['author_id'] ?>" <?= $a['author_id'] == $row['author_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </td>

                                <!-- Price -->
                                <td class="px-4 py-2">
                                    <input type="number" required name="price"
                                        value="<?= number_format($row['price'], 2, '.', '') ?>" step="0.01" min="0"
                                        class="border px-2 py-1 rounded w-24">
                                </td>

                                <!-- Stock -->
                                <td class="px-4 py-2">
                                    <input type="number" required name="stock_qty" value="<?= (int) $row['stock_qty'] ?>"
                                        min="0" max="9999" step="1" class="border px-2 py-1 rounded w-20">
                                </td>
                                <!-- Published -->
                                <td class="px-4 py-2">
                                    <input type="date" required name="published_at"
                                        value="<?= htmlspecialchars($row['published_at'] ?? '') ?>"
                                        class="border px-2 py-1 rounded w-32">
                                </td>
                                <!-- Cover -->
                                <td class="px-4 py-2 text-center">
                                    <?php if (!empty($row['cover_img'])): ?>
                                        <img src="../../src/img/covers/<?= htmlspecialchars($row['cover_img']) ?>" alt="cover"
                                            class="w-12 h-16 object-cover mx-auto mb-2 border">
                                    <?php else: ?>
                                        <div class="text-gray-400 text-sm mb-2">No image</div>
                                    <?php endif; ?>
                                    <input type="file" name="cover_image" accept="image/*"
                                        class="text-sm cursor-pointer font-bold">
                                </td>

                                <!-- Actions -->
                                <td class="px-4 py-2 flex flex-col space-y-2">
                                    <button type="submit" name="update_book"
                                        class="bg-green-500 text-white px-2 py-1 rounded">Update</button>
                                    <button type="submit" name="delete_book" class="bg-red-500 text-white px-2 py-1 rounded"
                                        onclick="return confirm('Delete book?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Create Book -->

                <?php $queryParams = $_GET;
                unset($queryParams['books_page']);
                $queryString = http_build_query($queryParams);
                ?>
                <nav class="flex justify-center items-center gap-x-1 mt-4" aria-label="books Pagination">
                    <a href="?<?= $queryString ?>&books_page=<?= max($currentbooksPage - 1, 1) ?>"
                        class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentbooksPage == 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                        &lt; Prev
                    </a>

                    <?php for ($p = 1; $p <= $totalbooksPages; $p++): ?>
                        <a href="?<?= $queryString ?>&books_page=<?= $p ?>"
                            class="px-3 py-2 rounded-lg <?= $p == $currentbooksPage ? 'bg-[#618792] text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <a href="?<?= $queryString ?>&books_page=<?= min($currentbooksPage + 1, $totalbooksPages) ?>"
                        class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentbooksPage == $totalbooksPages ? 'opacity-50 pointer-events-none' : '' ?>">
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