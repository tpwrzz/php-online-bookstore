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

// ---------------- USERS ----------------
$usersPerPage = 12;
$currentusersPage = isset($_GET['users_page']) && is_numeric($_GET['users_page']) ? (int) $_GET['users_page'] : 1;
$usersOffset = ($currentusersPage - 1) * $usersPerPage;

$totalusers = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$totalusersPages = max(ceil($totalusers / $usersPerPage), 1);
$users = $conn->query("SELECT user_id, username, email, role, created_at FROM users ORDER BY user_id ASC");

if (isset($_POST['update_password'])) {
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
    $stmt->bind_param("si", $hash, $_POST['user_id']);
    $stmt->execute();
    header("Location: users.php");
    exit;
}

if (isset($_POST['update_role'])) {
    $stmt = $conn->prepare("UPDATE users SET role=? WHERE user_id=?");
    $stmt->bind_param("si", $_POST['role'], $_POST['user_id']);
    $stmt->execute();
    header("Location: users.php");
    exit;
}

if (isset($_POST['delete_user'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=? AND role!='ADMIN'");
    $stmt->bind_param("i", $_POST['user_id']);
    $stmt->execute();
    header("Location: users.php");
    exit;
}

if (isset($_POST['create_admin'])) {
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'ADMIN')");
    $stmt->bind_param("sss", $_POST['username'], $_POST['email'], $hash);
    $stmt->execute();
    header("Location: users.php");
    exit;
}

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../../src/img/books.png" type="image/x-icon">
    <title>Users Admin Page</title>
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
                <li class="me-2"><a href="orders.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Orders</a>
                </li>
                <li class="me-2"><a href="users.php"
                        class="inline-block p-4 border-b  rounded-t-base text-blue-600 border-blue-600">Users</a>
                </li>
                <li class="me-2"><a href="genres.php"
                        class="inline-block p-4 border-b border-transparent rounded-t-base hover:text-blue-600 hover:border-blue-600">Genres</a>
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
            <!-- Users Tab -->
            <div id="users" class="tab-content  mb-4">
                <h2 class="text-xl font-semibold mb-4">Manage Users</h2>
                <h3 class="text-lg font-semibold mb-2">Create Admin</h3>
                <form method="POST" class="flex space-x-2 mb-6">
                    <input type="text" name="username" placeholder="Username" required class="border px-2 py-1 rounded">
                    <input type="email" name="email" placeholder="Email" required class="border px-2 py-1 rounded">
                    <input type="password" name="password" placeholder="Password" required
                        class="border px-2 py-1 rounded">
                    <button type="submit" name="create_admin"
                        class="bg-blue-500 text-white px-2 rounded">Create</button>
                </form>
                <table class="min-w-full bg-white border mb-6">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Username</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Role</th>
                            <th class="px-4 py-2 text-left">Created At</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $users->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2"><?= $row['user_id'] ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['username']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['email']) ?></td>
                                <td class="px-4 py-2">
                                    <form method="POST" class="flex space-x-2">
                                        <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                                        <select name="role" class="border px-2 py-1 rounded">
                                            <option value="USER" <?= $row['role'] == 'USER' ? 'selected' : '' ?>>User</option>
                                            <option value="ADMIN" <?= $row['role'] == 'ADMIN' ? 'selected' : '' ?>>Admin
                                            </option>
                                        </select>
                                        <button type="submit" name="update_role"
                                            class="bg-green-500 text-white px-2 rounded">Update</button>
                                    </form>
                                </td>
                                <td class="px-4 py-2"><?= $row['created_at'] ?></td>
                                <td class="px-4 py-2">
                                    <form method="POST" onsubmit="return confirm('Delete user?')">
                                        <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                                        <button type="submit" name="delete_user"
                                            class="bg-red-500 text-white px-2 rounded">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php $queryParams = $_GET;
                unset($queryParams['users_page']);
                $queryString = http_build_query($queryParams);
                ?>
                <nav class="flex justify-center items-center gap-x-1 mt-4" aria-label="users Pagination">
                    <a href="?<?= $queryString ?>&users_page=<?= max($currentusersPage - 1, 1) ?>"
                        class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentusersPage == 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                        &lt; Prev
                    </a>

                    <?php for ($p = 1; $p <= $totalusersPages; $p++): ?>
                        <a href="?<?= $queryString ?>&users_page=<?= $p ?>"
                            class="px-3 py-2 rounded-lg <?= $p == $currentusersPage ? 'bg-[#618792] text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <a href="?<?= $queryString ?>&users_page=<?= min($currentusersPage + 1, $totalusersPages) ?>"
                        class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 <?= $currentusersPage == $totalusersPages ? 'opacity-50 pointer-events-none' : '' ?>">
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