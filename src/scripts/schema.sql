-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 03:53 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookstore`
--

-- --------------------------------------------------------

--
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `author_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authors`
--

INSERT INTO `authors` (`author_id`, `first_name`, `last_name`, `bio`) VALUES
(1, 'George', 'Orwell', NULL),
(2, 'Jane', 'Austen', NULL),
(3, 'J.K.', 'Rowling', NULL),
(4, 'F. Scott', 'Fitzgerald', NULL),
(5, 'Agatha', 'Christie1', NULL),
(6, 'J.R.R.', 'Tolkien', NULL),
(7, 'Harper', 'Lee', NULL),
(8, 'Dan', 'Brown', NULL),
(9, 'Suzanne', 'Collins', NULL),
(10, 'Yuval Noah', 'Harari', NULL),
(11, 'Leo', 'Tolstoy', NULL),
(12, 'Stephen', 'King', NULL),
(13, 'Mary', 'Shelley', NULL),
(14, 'Ernest', 'Hemingway', NULL),
(15, 'Gabriel', 'García Márquez', NULL),
(17, 'Lindsay', 'Nikole ', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `author_id` int(10) UNSIGNED DEFAULT NULL,
  `genre_id` int(10) UNSIGNED DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_qty` int(11) DEFAULT 0,
  `published_at` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cover_img` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `author_id`, `genre_id`, `price`, `stock_qty`, `published_at`, `description`, `cover_img`) VALUES
(1, '1984', 1, 1, 7.99, 29, '1949-06-08', 'A dystopian social science fiction novel and cautionary tale about totalitarianism.', '1984.jpg'),
(2, 'Pride and Prejudice', 2, 5, 7.49, 25, '1813-01-28', 'A romantic novel of manners that explores love, reputation, and class.', 'pride_prejudice.jpg'),
(3, 'Harry Potter and the Philosopher\'s Stone', 3, 3, 9.99, 50, '1997-06-26', 'The first book in the legendary Harry Potter series.', 'hp1.jpg'),
(4, 'The Great Gatsby', 4, 1, 6.99, 19, '1925-04-10', 'A story about the American dream, love, and tragedy in the Jazz Age.', 'gatsby.jpg'),
(5, 'Murder on the Orient Express', 5, 4, 8.49, 35, '1934-01-01', 'Detective Hercule Poirot investigates a murder aboard a luxury train.', 'orient_express.jpg'),
(6, 'The Lord of the Rings: The Fellowship of the Ring', 6, 3, 10.99, 40, '1954-07-29', 'Epic fantasy adventure through Middle-earth.', 'lotr1.jpg'),
(7, 'To Kill a Mockingbird', 7, 1, 7.99, 32, '1960-07-11', 'A profound novel on racial injustice and childhood innocence.', 'mockingbird.jpg'),
(8, 'The Da Vinci Code', 8, 4, 9.49, 45, '2003-03-18', 'A mystery thriller that blends art, history, and conspiracy.', 'davinci.jpg'),
(9, 'The Hunger Games', 9, 2, 8.99, 50, '2008-09-14', 'A dystopian novel about survival and rebellion in a totalitarian state.', 'hungergames.jpg'),
(10, 'Sapiens: A Brief History of Humankind', 10, 6, 11.99, 25, '2011-01-01', 'An exploration of human evolution and history.', 'sapiens.jpg'),
(11, 'War and Peace', 11, 2, 13.99, 20, '1869-01-01', 'A sweeping story of Russian society during the Napoleonic Wars.', 'war_peace.jpg'),
(12, 'The Shining', 12, 4, 9.49, 30, '1977-01-28', 'A psychological horror novel set in an isolated hotel.', 'shining.jpg'),
(13, 'Frankenstein', 13, 2, 6.99, 14, '1818-01-01', 'A gothic tale about the dangers of ambition and scientific hubris.', 'frankenstein.jpg'),
(14, 'The Old Man and the Sea', 14, 3, 7.49, 19, '1952-09-01', 'A short novel about endurance, struggle, and human spirit.', 'oldmansea.jpg'),
(15, 'One Hundred Years of Solitude', 15, 1, 10.49, 26, '1967-06-05', 'A multi-generational tale of the Buendía family in the fictional town of Macondo.', 'solitude.jpg'),
(16, 'Epic Earth', 17, 6, 12.00, 1, '2025-11-19', 'book book book', 'epicearth.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `genre_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`genre_id`, `name`) VALUES
(1, 'Classic Literature'),
(3, 'Fantasy'),
(7, 'Historical Fiction'),
(4, 'Mystery'),
(6, 'Non-fiction'),
(5, 'Romance'),
(2, 'Science Fiction'),
(8, 'test'),
(9, 'test2');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','shipped','delivered','cancelled') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `full_name`, `address`, `total_price`, `status`, `created_at`) VALUES
(19, 5, 'Tatiana Poverjuc', 'bd. Moscova, 21', 23.97, 'pending', '2025-11-17 19:02:14'),
(20, 5, 'Tatiana Poverjuc', 'bd. Moscova, 21', 21.47, 'pending', '2025-11-17 21:48:14');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED DEFAULT NULL,
  `book_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price_each` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `book_id`, `quantity`, `price_each`) VALUES
(6, 19, 1, 3, 7.99),
(7, 20, 14, 1, 7.49),
(8, 20, 13, 1, 7.49),
(9, 20, 4, 1, 7.49);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('USER','ADMIN') NOT NULL DEFAULT 'USER',
  `email` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `role`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$K4JT5CJ2ek9La9qtGDpV6uSd94DHovlObck6DgtGmkh9mYOptR2KW', 'ADMIN', 'admin@bookstore.com', '2025-11-11 21:00:17'),
(5, 'tpwrzz@gmail.com', '$2y$10$FtK0DT1cb2FKxF/vhTQAbuS5iXCoJzYPT9nUP9XIu9S8Qj3Cdbrz2', 'USER', 'tpwrzz@gmail.com', '2025-11-12 22:00:26'),
(6, 'admintest@test.com', '$2y$10$K4JT5CJ2ek9La9qtGDpV6uSd94DHovlObck6DgtGmkh9mYOptR2KW', 'ADMIN', 'admintest@test.com', '2025-11-13 12:27:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`author_id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `genre_id` (`genre_id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`genre_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `author_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `genre_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `books_ibfk_2` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`genre_id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
