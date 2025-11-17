<?php
session_start();
include('../src/scripts/db-connect.php');

if (isset($_SESSION['user_id'])) {
    header("Location: ../secure/user/myinfo.php");
    exit;
}

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $cpassword = trim($_POST['cpassword'] ?? '');

    if ($action === 'register') {
        if (!$email || !$password || !$cpassword) {
            $errors[] = "Please fill in all fields.";
        } elseif ($password !== $cpassword) {
            $errors[] = "Passwords do not match.";
        } else {
            $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $errors[] = "Email already registered.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password_hash, email, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("sss", $email, $hash, $email);
                if ($stmt->execute()) {
                    if ($result['role'] == "USER") {
                        $_SESSION['user_id'] = $result['user_id'];
                        $_SESSION['username'] = $username;
                        header('Location: ../secure/user/myinfo.php');
                    } else {
                        $_SESSION['user_id'] = $result['user_id'];
                        $_SESSION['username'] = $username;
                        header('Location: ../secure/admin/orders.php');
                    }
                } else {
                    $errors[] = "Registration failed. Try again.";
                }
            }
        }
    } elseif ($action === 'login') {
        if (!$username || !$password) {
            $errors[] = "Please enter username and password.";
        } else {
            $stmt = $conn->prepare("SELECT user_id, password_hash, role FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result && password_verify($password, $result['password_hash'])) {
                if ($result['role'] == "USER") {
                    $_SESSION['user_id'] = $result['user_id'];
                    $_SESSION['username'] = $username;
                    header('Location: ../secure/user/myinfo.php');
                } else {
                    $_SESSION['user_id'] = $result['user_id'];
                    $_SESSION['username'] = $username;
                    header('Location: ../secure/admin/orders.php');
                }
                exit;
            } else {
                $errors[] = "Invalid username or password.";
            }
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
    <title>LogIn</title>
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
                        <a href='<?= isset($_SESSION['user_id']) ? "../secure/user/myinfo.php" : "auth.php" ?>'
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
        <!-- <nav class="flex my-6" aria-label="Breadcrumb">
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
                        <span class="ms-1 text-sm font-medium text-[#618792] md:ms-2">SignIn / LogIn</span>
                    </div>
                </li>
            </ol>
        </nav>--->
        <!--- login register--->
        <div id="login-form" class="flex flex-col items-center justify-center py-6 px-4">
            <div class="max-w-[480px] w-full">
                <a href="#"><img src="../src/img/books.png" alt="logo" class="w-20 mb-8 mx-auto block" /></a>

                <div class="p-6 sm:p-8 rounded-2xl bg-white border border-gray-200 shadow-sm">
                    <h1 class="text-gray-900 text-center text-3xl font-semibold">Sign in</h1>

                    <?php if ($errors): ?>
                    <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">
                        <?php foreach ($errors as $err)
                            echo "<p>$err</p>"; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" class="mt-12 space-y-6">
                        <input type="hidden" name="action" value="login">
                        <div>
                            <label class="text-[#618792] text-sm font-medium mb-2 block">User name</label>
                            <input name="username" type="text" required
                                class="w-full text-gray-900 text-sm border border-[#618792]/90 px-4 py-3 rounded-md outline-[#618792]/60"
                                placeholder="Enter username" />
                        </div>
                        <div>
                            <label class="text-[#618792] text-sm font-medium mb-2 block">Password</label>
                            <input name="password" type="password" required
                                class="w-full text-gray-900 text-sm border border-[#618792]/90 px-4 py-3 rounded-md outline-[#618792]/60"
                                placeholder="Enter password" />
                        </div>
                        <button type="submit"
                            class="w-full py-2 px-4 text-[15px] font-medium tracking-wide rounded-md text-white bg-[#618792]/90 hover:bg-[#618792] focus:outline-none cursor-pointer">
                            Sign in
                        </button>
                    </form>

                    <p class="text-gray-900 text-sm mt-6 text-center">
                        Don’t have an account?
                        <a href="javascript:void(0);"
                            class="text-[#618792]/90 hover:underline font-semibold to-register">Register here</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- REGISTER FORM -->
        <div id="register-form" class="flex flex-col items-center justify-center py-6 px-4 hidden">
            <div class="max-w-[480px] w-full">
                <a href="#"><img src="../src/img/books.png" alt="logo" class="w-20 mb-8 mx-auto block" /></a>
                <div class="p-6 sm:p-8 rounded-2xl bg-white border border-[#618792]/50 shadow-sm">
                    <h1 class="text-gray-900] text-center text-3xl font-semibold">Register</h1>


                    <form method="POST">
                        <input type="hidden" name="action" value="register">
                        <div class="space-y-6">
                            <div>
                                <label class="text-[#618792] text-sm font-medium mb-2 block">Email</label>
                                <input name="email" type="email" required
                                    class="text-gray-900 bg-white border border-[#618792]/60 w-full text-sm px-4 py-3 rounded-md outline-[#618792]/90"
                                    placeholder="Enter email" />
                            </div>
                            <div>
                                <label class="text-[#618792] text-sm font-medium mb-2 block">Password</label>
                                <input name="password" type="password" required
                                    class="text-gray-900 bg-white border border-gray-300 w-full text-sm px-4 py-3 rounded-md outline-[#618792]/90"
                                    placeholder="Enter password" />
                            </div>
                            <div>
                                <label class="text-[#618792] text-sm font-medium mb-2 block">Confirm Password</label>
                                <input name="cpassword" type="password" required
                                    class="text-gray-900 bg-white border border-gray-300 w-full text-sm px-4 py-3 rounded-md outline-[#618792]/90"
                                    placeholder="Confirm password" />
                            </div>
                        </div>

                        <div class="mt-12">
                            <button type="submit"
                                class="w-full py-3 px-4 text-sm tracking-wider font-medium rounded-md text-white bg-[#618792]/90 hover:bg-[#618792] focus:outline-none cursor-pointer">
                                Create an account
                            </button>
                        </div>
                        <p class="text-gray-900 text-sm mt-6 text-center">
                            Already have an account?
                            <a href="javascript:void(0);"
                                class="text-[#618792] font-medium hover:underline ml-1 to-login">Login here</a>
                        </p>
                    </form>
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
    document.addEventListener('DOMContentLoaded', () => {
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const switchToLogin = document.querySelectorAll('.to-login');
        const switchToRegister = document.querySelectorAll('.to-register');

        switchToLogin.forEach(btn => btn.addEventListener('click', () => {
            registerForm.classList.add('hidden');
            loginForm.classList.remove('hidden');
        }));
        switchToRegister.forEach(btn => btn.addEventListener('click', () => {
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
        }));
    });

    document.querySelector('.to-register').addEventListener('click', () => {
        document.getElementById('loginForm').classList.add('hidden');
        document.getElementById('registerForm').classList.remove('hidden');
    });
    document.querySelector('.to-login').addEventListener('click', () => {
        document.getElementById('registerForm').classList.add('hidden');
        document.getElementById('loginForm').classList.remove('hidden');
    });
</script>