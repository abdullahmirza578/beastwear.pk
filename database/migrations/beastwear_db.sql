-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2026 at 11:10 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `beastwear_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `city` varchar(255) NOT NULL,
  `area` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `address_type` varchar(20) NOT NULL DEFAULT 'home',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `name`, `phone`, `city`, `area`, `address`, `postal_code`, `address_type`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 2, 'Abdullah mirza', '03458848846', 'Karachi', NULL, 'NEW SAEEDABAD BALDIA TOWN KARACHI BISMILLAH CHOWK DILSHAD BAKER GULISTAN SCHOOL', NULL, 'home', 1, '2026-10-09 23:49:15', '2026-10-09 23:49:15'),
(2, 3, 'ZAIN', '+923458848846', 'Karachi', NULL, 'NEW SAEEDABAD BALDIA TOWN KARACHI BISMILLAH CHOWK DILSHAD BAKER GULISTAN SCHOOL', NULL, 'home', 1, '2026-10-10 22:50:59', '2026-10-10 22:50:59');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `mobile_image` varchar(255) DEFAULT NULL,
  `button_text` varchar(255) DEFAULT NULL,
  `button_url` varchar(255) DEFAULT NULL,
  `position` varchar(50) NOT NULL DEFAULT 'home',
  `status` varchar(20) NOT NULL DEFAULT 'inactive',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `subtitle`, `image`, `mobile_image`, `button_text`, `button_url`, `position`, `status`, `starts_at`, `ends_at`, `created_at`, `updated_at`) VALUES
(1, 'Wear Your Beast', 'Everyday essentials, designed in Pakistan.', 'banners/XtAHCJjTlkhxV9Xatr2xuoU2UpOY6MxFwHUlJ4Fs.png', 'banners/O8HMiIlEQ96Ucw6E5NmekgygC0hdEkbYu4dPtIUL.jpg', 'Shop Now', '/shop', 'home-hero', 'active', NULL, NULL, '2026-10-07 17:47:57', '2026-10-09 17:57:43');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(2, 2, '2026-10-07 18:06:50', '2026-10-07 18:06:50'),
(3, 3, '2026-10-10 22:25:02', '2026-10-10 22:25:02');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cart_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `selected_design_image_id` bigint(20) UNSIGNED DEFAULT NULL,
  `selection_key` varchar(80) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `product_variant_id`, `quantity`, `price`, `created_at`, `updated_at`, `selected_design_image_id`, `selection_key`) VALUES
(14, 3, 5, 23, 1, 8000.00, '2026-10-10 23:48:22', '2026-10-10 23:48:22', 20, 'v23-d20'),
(15, 3, 6, 30, 1, 4000.00, '2026-10-11 02:57:16', '2026-10-11 02:57:16', 30, 'v30-d30');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'T-Shirts', 't-shirts', NULL, NULL, 'active', '2026-10-07 17:47:57', '2026-10-07 17:47:57'),
(2, 'Oversized T-Shirts', 'oversized', NULL, NULL, 'active', '2026-10-07 17:47:57', '2026-10-07 17:47:57'),
(3, 'Hoodies', 'hoodies', NULL, 'storage/categories/hsVoKaLnVobF4NQGgCcERhIjBBN17dpSgTXsvOJG.jpg', 'active', '2026-10-07 17:47:57', '2026-10-10 22:44:48'),
(4, 'Sweatshirts', 'sweatshirts', NULL, NULL, 'active', '2026-10-07 17:47:57', '2026-10-07 17:47:57'),
(5, 'Trousers', 'trousers', NULL, NULL, 'active', '2026-10-07 17:47:57', '2026-10-07 17:47:57'),
(6, 'Kids Clothing', 'kids', NULL, NULL, 'active', '2026-10-07 17:47:57', '2026-10-07 17:47:57');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `type` varchar(20) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `minimum_order_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `maximum_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(10) UNSIGNED DEFAULT NULL,
  `used_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usages`
--

CREATE TABLE `coupon_usages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `coupon_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2026_10_08_000000_add_is_admin_to_users_table', 1),
(6, '2026_10_08_010000_create_ecommerce_tables', 2),
(7, '2026_10_09_000001_add_mobile_image_to_banners_table', 3),
(8, '2026_10_10_000001_create_shipping_settings_table', 4),
(9, '2026_10_10_000002_create_site_settings_table', 5),
(10, '2026_10_10_000003_add_prepaid_payment_destinations_to_site_settings', 6),
(11, '2026_10_11_000001_add_shipping_details_to_orders_table', 7),
(12, '2026_10_11_000002_add_public_tracking_id_to_orders_table', 8),
(13, '2026_10_11_000003_add_configurable_product_options', 9),
(14, '2026_10_11_000004_make_cart_option_selections_unique', 10);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `coupon_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_number` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(30) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `city` varchar(255) NOT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(20) NOT NULL DEFAULT 'COD',
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
  `order_status` varchar(20) NOT NULL DEFAULT 'pending',
  `shipping_status` varchar(20) NOT NULL DEFAULT 'pending',
  `customer_note` text DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `shipping_courier` varchar(100) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `public_tracking_id` varchar(24) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `coupon_id`, `order_number`, `customer_name`, `customer_phone`, `customer_email`, `shipping_address`, `city`, `postal_code`, `subtotal`, `discount`, `shipping_fee`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `shipping_status`, `customer_note`, `admin_note`, `created_at`, `updated_at`, `shipping_courier`, `tracking_number`, `public_tracking_id`) VALUES
(1, 2, NULL, 'BW-261010-CZ0X7SO2', 'Abdullah mirza', '03458848846', 'abdullhmirza490@gmail.com', 'NEW SAEEDABAD BALDIA TOWN KARACHI BISMILLAH CHOWK DILSHAD BAKER GULISTAN SCHOOL', 'Karachi', NULL, 7758.09, 0.00, 150.00, 7908.09, 'JazzCash', 'paid', 'confirmed', 'pending', NULL, NULL, '2026-10-09 23:49:15', '2026-10-10 20:26:45', NULL, NULL, 'BW-TRK-RRFRSZHJEWST'),
(2, 2, NULL, 'BW-261011-Q6AUYFYJ', 'Abdullah mirza', '03458848846', 'abdullhmirza490@gmail.com', 'NEW SAEEDABAD BALDIA TOWN KARACHI BISMILLAH CHOWK DILSHAD BAKER GULISTAN SCHOOL', 'Karachi', NULL, 1804.12, 0.00, 250.00, 2054.12, 'COD', 'pending', 'out_for_delivery', 'out_for_delivery', NULL, NULL, '2026-10-10 20:23:37', '2026-10-10 23:26:20', NULL, '123', 'BW-TRK-TNO8AOY6FB0G'),
(3, 2, NULL, 'BW-261011-8TIMJPPB', 'Abdullah mirza', '03458848846', 'abdullhmirza490@gmail.com', 'NEW SAEEDABAD BALDIA TOWN KARACHI BISMILLAH CHOWK DILSHAD BAKER GULISTAN SCHOOL', 'Karachi', NULL, 2010.00, 0.00, 150.00, 2160.00, 'JazzCash', 'pending', 'packed', 'pending', NULL, NULL, '2026-10-10 20:24:35', '2026-10-10 23:14:29', 'TCS', '12345', 'BW-TRK-HYTRZNPLJD1C'),
(4, 2, NULL, 'BW-261011-JCQE3CXZ', 'Abdullah mirza', '03458848846', 'abdullhmirza490@gmail.com', 'NEW SAEEDABAD BALDIA TOWN KARACHI BISMILLAH CHOWK DILSHAD BAKER GULISTAN SCHOOL', 'Karachi', NULL, 2255.15, 0.00, 150.00, 2405.15, 'JazzCash', 'pending', 'confirmed', 'pending', NULL, NULL, '2026-10-10 20:38:53', '2026-10-10 23:06:01', 'TCS', '123', 'BW-TRK-SA0ZYEHGNCA6'),
(5, 3, NULL, 'BW-261011-Q6DIWGRK', 'ZAIN', '+923458848846', 'abdullhmirza4490@gmail.com', 'NEW SAEEDABAD BALDIA TOWN KARACHI BISMILLAH CHOWK DILSHAD BAKER GULISTAN SCHOOL', 'Karachi', NULL, 1234.04, 0.00, 150.00, 1384.04, 'JazzCash', 'paid', 'confirmed', 'pending', 'urgent delivery', NULL, '2026-10-10 22:50:59', '2026-10-10 22:52:15', NULL, NULL, 'BW-TRK-EFXBDPH7OZC6'),
(6, 3, NULL, 'BW-261011-9VO4VETW', 'ZAIN', '+923458848846', 'abdullhmirza4490@gmail.com', 'NEW SAEEDABAD BALDIA TOWN KARACHI BISMILLAH CHOWK DILSHAD BAKER GULISTAN SCHOOL', 'Karachi', NULL, 1234.04, 0.00, 250.00, 1484.04, 'COD', 'pending', 'out_for_delivery', 'pending', NULL, NULL, '2026-10-10 22:53:25', '2026-10-10 23:04:05', NULL, NULL, 'BW-TRK-WUDVEJC5WHSK');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `design_name` varchar(100) DEFAULT NULL,
  `design_image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_variant_id`, `product_name`, `sku`, `size`, `color`, `quantity`, `price`, `total`, `created_at`, `updated_at`, `design_name`, `design_image_path`) VALUES
(1, 1, NULL, NULL, 'tshirt', 'DW', 's', 'pink blue', 1, 1005.00, 1005.00, '2026-10-09 23:49:15', '2026-10-09 23:49:15', NULL, NULL),
(2, 1, NULL, NULL, 'BeastWear Classic Tee', 'BW-CLASSIC-TEE', 'S', 'Black', 3, 1800.00, 5400.00, '2026-10-09 23:49:15', '2026-10-09 23:49:15', NULL, NULL),
(3, 1, 3, 14, 'Abdullah mirza', 'DW3', 's', 'pink /blue', 3, 451.03, 1353.09, '2026-10-09 23:49:15', '2026-10-09 23:49:15', NULL, NULL),
(4, 2, 3, 15, 'Abdullah mirza', 'DW3', 'm', 'pink /blue', 1, 451.03, 451.03, '2026-10-10 20:23:37', '2026-10-10 20:23:37', NULL, NULL),
(5, 2, 3, 16, 'Abdullah mirza', 'DW3', 'l', 'pink /blue', 3, 451.03, 1353.09, '2026-10-10 20:23:37', '2026-10-10 20:23:37', NULL, NULL),
(6, 3, NULL, NULL, 'tshirt', 'DW', 's', 'pink blue', 2, 1005.00, 2010.00, '2026-10-10 20:24:35', '2026-10-10 20:24:35', NULL, NULL),
(7, 4, 3, 15, 'Abdullah mirza', 'DW3', 'm', 'pink /blue', 2, 451.03, 902.06, '2026-10-10 20:38:53', '2026-10-10 20:38:53', NULL, NULL),
(8, 4, 3, 17, 'Abdullah mirza', 'DW3', 'xl.xxl', 'pink /blue', 3, 451.03, 1353.09, '2026-10-10 20:38:53', '2026-10-10 20:38:53', NULL, NULL),
(9, 5, 4, 18, 'nextag', 'DW3A', 's', NULL, 1, 1234.04, 1234.04, '2026-10-10 22:50:59', '2026-10-10 22:50:59', NULL, NULL),
(10, 6, 4, 18, 'nextag', 'DW3A', 's', NULL, 1, 1234.04, 1234.04, '2026-10-10 22:53:25', '2026-10-10 22:53:25', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('abdullhmirza490@gmail.com', '$2y$12$hFjwFQM.j6XqMJc0FhqzB.K7BlSGGMsr1qOKtjQ1FgalkxuY6.Ari', '2026-10-10 22:20:30');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL DEFAULT 'COD',
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_details`)),
  `proof_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `transaction_id`, `payment_method`, `amount`, `status`, `paid_at`, `payment_details`, `proof_path`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'JazzCash', 7908.09, 'paid', '2026-10-10 20:26:45', '{\"proof_submitted_at\":\"2026-10-09T23:49:56.127297Z\"}', 'payment-proofs/7A6mIpCVOesnjrlbUwlzrZUTXHlZD44qRcB6fyY7.png', '2026-10-09 23:49:15', '2026-10-10 20:26:45'),
(2, 2, NULL, 'COD', 2054.12, 'pending', NULL, NULL, NULL, '2026-10-10 20:23:37', '2026-10-10 20:23:37'),
(3, 3, NULL, 'JazzCash', 2160.00, 'pending', NULL, NULL, NULL, '2026-10-10 20:24:35', '2026-10-10 20:24:35'),
(4, 4, NULL, 'JazzCash', 2405.15, 'pending', NULL, NULL, NULL, '2026-10-10 20:38:53', '2026-10-10 20:38:53'),
(5, 5, NULL, 'JazzCash', 1384.04, 'paid', '2026-10-10 22:52:15', '{\"proof_submitted_at\":\"2026-10-10T22:51:15.613394Z\"}', 'payment-proofs/YuGX4jGHM3utvS33jO7SbbtTTiruALIvbuLFuxL3.jpg', '2026-10-10 22:50:59', '2026-10-10 22:52:15'),
(6, 6, NULL, 'COD', 1484.04, 'pending', NULL, NULL, NULL, '2026-10-10 22:53:25', '2026-10-10 22:53:25');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `design_selection_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `size_selection_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `color_selection_enabled` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `description`, `short_description`, `price`, `sale_price`, `cost_price`, `stock_quantity`, `status`, `featured`, `created_at`, `updated_at`, `design_selection_enabled`, `size_selection_enabled`, `color_selection_enabled`) VALUES
(3, 1, 'Abdullah mirza', 'abdullah-mirza', 'DW3', NULL, 'DXAS', 36210.00, 451.00, 252.02, 23, 'active', 1, '2026-10-07 18:38:21', '2026-10-10 22:46:14', 0, 1, 1),
(4, 4, 'nextag', 'nextag', 'DW3A', NULL, 'DXAS', 1234.00, NULL, NULL, 55, 'active', 1, '2026-10-10 22:47:53', '2026-10-10 22:47:53', 0, 1, 0),
(5, 5, 'ZAIN', 'zain', 'DW3A3', NULL, 'DXAS', 8000.00, NULL, NULL, 3, 'active', 1, '2026-10-10 23:46:35', '2026-10-10 23:47:37', 1, 1, 0),
(6, 3, 'anssjjs', 'anssjjs', 'IKJA', 'JADS', 'ojmklS', 34515415.00, 4000.00, NULL, 34, 'active', 1, '2026-10-11 02:55:29', '2026-10-11 02:56:21', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_selectable_design` tinyint(1) NOT NULL DEFAULT 0,
  `design_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image`, `sort_order`, `is_primary`, `created_at`, `updated_at`, `is_selectable_design`, `design_name`) VALUES
(2, 3, 'products/Kb6rKy2x6FVOWGDFRxwEaJdLfB3abL5L3Aaz0RIN.jpg', 0, 1, '2026-10-07 18:38:21', '2026-10-10 22:46:50', 0, NULL),
(3, 3, 'products/lZJHryniWYePlrlxo1wei9tTJ3Un7ZzhV5jPOYXH.jpg', 1, 0, '2026-10-07 18:38:21', '2026-10-10 22:46:50', 0, NULL),
(4, 3, 'products/2GlZdXgeYYjWcffhjySVhCycty4xSwH1JtfYZav7.jpg', 2, 0, '2026-10-07 18:38:21', '2026-10-10 22:46:50', 0, NULL),
(5, 3, 'products/DEgKyG6XYrZiXSDE8rGADj75B1qz2AaTE6adiIGb.jpg', 3, 0, '2026-10-07 18:38:21', '2026-10-10 22:46:50', 0, NULL),
(6, 3, 'products/ZVxJzaoKvk6JauaRmugQWfGLziP5U388wranbwfW.png', 4, 0, '2026-10-07 18:38:21', '2026-10-10 22:46:50', 0, NULL),
(7, 3, 'products/d2602jPVW0IrT8fhHExTSpZch0GGvXS5KzeAL27n.jpg', 5, 0, '2026-10-07 18:38:21', '2026-10-10 22:46:50', 0, NULL),
(8, 4, 'products/vYlU9jZDqj09kzGmUSEK1JY99ZISQCoXFClKox3u.jpg', 0, 1, '2026-10-10 22:48:22', '2026-10-10 22:48:22', 0, NULL),
(9, 4, 'products/78tZmIXEAeKaCJEOqcA6kotxyTe4FmxVlX672mux.jpg', 1, 0, '2026-10-10 22:48:22', '2026-10-10 22:48:22', 0, NULL),
(10, 4, 'products/ddntdO69tRPbnebLGmuCXZxuaqkBlCXgwEE5T4Kc.jpg', 2, 0, '2026-10-10 22:48:22', '2026-10-10 22:48:22', 0, NULL),
(11, 4, 'products/YTPjrjcvylfuQ7Epnf2uxfSqFoHdZIaaAD1Hmt7r.jpg', 3, 0, '2026-10-10 22:48:22', '2026-10-10 22:48:22', 0, NULL),
(12, 4, 'products/9WtiFclLKoDfbaXSVkdFeHnxjYxBdOJ3dh5BONRE.jpg', 4, 0, '2026-10-10 22:48:22', '2026-10-10 22:48:22', 0, NULL),
(13, 4, 'products/diF2wAjAZoX4JIebTKspZIjmU6q5L1XYpPS9K25k.jpg', 5, 0, '2026-10-10 22:48:22', '2026-10-10 22:48:22', 0, NULL),
(14, 4, 'products/2dCNPZ6rCIRIP5U2aYFOsJm6N0O3myC4Ow5g0T8m.jpg', 6, 0, '2026-10-10 22:48:22', '2026-10-10 22:48:22', 0, NULL),
(15, 4, 'products/Hx6jGDHFnLOkATVoYOhtWOyU2eWrh3MhFxfFbYop.jpg', 7, 0, '2026-10-10 22:48:22', '2026-10-10 22:48:22', 0, NULL),
(16, 4, 'products/dvxDSPQ1d50tYfps4W58FCBGWqk4OaL7lBukpwa8.jpg', 8, 0, '2026-10-10 22:48:22', '2026-10-10 22:48:22', 0, NULL),
(17, 4, 'products/bhiZ23Vil4kTGPweOwM9P7KRwNaochZgu0mwVxvO.jpg', 9, 0, '2026-10-10 22:48:22', '2026-10-10 22:48:22', 0, NULL),
(18, 4, 'products/9tDUFoE72JO4glNhGRlVOavIJtbf2ppH5dQavKc2.jpg', 10, 0, '2026-10-10 22:48:22', '2026-10-10 22:48:22', 0, NULL),
(19, 5, 'products/TQW6OWNh6ealyDwQkuBUQZtSNkPAuKV21DFBGJHR.png', 0, 1, '2026-10-10 23:46:35', '2026-10-10 23:47:58', 0, NULL),
(20, 5, 'products/vZATuv1qCLKwDguWeHgiDpu7zoJHBjWANRgDgdfu.jpg', 1, 0, '2026-10-10 23:46:35', '2026-10-10 23:47:58', 1, '4'),
(21, 5, 'products/urAvE4YDFYgvvhblSt6B3bFG0jjFgvZd9nWMhSSC.jpg', 2, 0, '2026-10-10 23:46:35', '2026-10-10 23:47:58', 1, 'e'),
(22, 5, 'products/1KN9RcJwijb64GLArLn8n780DzplLol98Egg4jZU.jpg', 3, 0, '2026-10-10 23:46:35', '2026-10-10 23:47:58', 1, '99674a955884742522c52c23a131f41c'),
(23, 5, 'products/PToyH16peRVQhTv8h9M5I2EvUMrGqQpzODfhhSeN.jpg', 4, 0, '2026-10-10 23:46:35', '2026-10-10 23:47:58', 1, 'w'),
(24, 5, 'products/Bltv0gOuquS86je9KZ3jQDuW9Y6rMw9UFS3BEVQD.jpg', 5, 0, '2026-10-10 23:46:35', '2026-10-10 23:47:58', 1, 'd'),
(25, 5, 'products/mwmiwnPs8wOcMyJaCAEea6xhtIdl9yKU8TYeeG3b.jpg', 6, 0, '2026-10-10 23:46:35', '2026-10-10 23:47:58', 1, 's'),
(26, 5, 'products/I9WT3OLmMdhlTNobRHSYybilltb3jo4un8u8Wzjs.jpg', 7, 0, '2026-10-10 23:46:35', '2026-10-10 23:47:58', 1, 'x'),
(27, 5, 'products/6SPylPNJHawIOumBMZZnVquoOzcFwsOeymh4iGeo.jpg', 8, 0, '2026-10-10 23:46:35', '2026-10-10 23:47:58', 1, 'h1'),
(28, 5, 'products/GnGMaqDE8Qpw8wuOVlV7eJV63wGFNbIAJ5nSulV5.jpg', 9, 0, '2026-10-10 23:46:35', '2026-10-10 23:47:58', 1, 'g'),
(29, 6, 'products/zmRvVGa7OEuShEjUszhdQmk17S9pTKDzlTlnVFMo.jpg', 0, 1, '2026-10-11 02:55:29', '2026-10-11 02:56:43', 0, NULL),
(30, 6, 'products/jmuxHMNNaIyQuU6xtWGaEbs1Xb398gAgDPA4shIE.jpg', 1, 0, '2026-10-11 02:55:29', '2026-10-11 02:56:43', 1, 'WhatsApp Image 2026 05 10 at 2.35.03 AM (1)'),
(31, 6, 'products/pX1bSP0iVJJdA3h4DChkFB44neTUREZ7ifNi8laf.jpg', 2, 0, '2026-10-11 02:55:29', '2026-10-11 02:56:43', 1, 'WhatsApp Image 2026 05 10 at 2.35.03 AM'),
(32, 6, 'products/gymrF0lyEo62BgPfIqmhRwilGOZSJYHQd1qotG31.jpg', 3, 0, '2026-10-11 02:55:29', '2026-10-11 02:56:43', 1, 'WhatsApp Image 2026 05 10 at 2.35.02 AM (1)'),
(33, 6, 'products/tsEIHShwwpx0b0uLc1JgvFwLsXhxiKOjyWUtuMgS.jpg', 4, 0, '2026-10-11 02:55:29', '2026-10-11 02:56:43', 1, 'WhatsApp Image 2026 05 10 at 2.35.02 AM'),
(34, 6, 'products/50hIGVNzyGSotI3uGogoUH8apPOzsZl2L6d124ll.jpg', 5, 0, '2026-10-11 02:55:29', '2026-10-11 02:56:43', 1, 'WhatsApp Image 2026 05 10 at 2.35.01 AM'),
(35, 6, 'products/04ToZULD7BgqNLuL0f4SIXAlHyLmZbcjHCxU67RD.png', 6, 0, '2026-10-11 02:55:29', '2026-10-11 02:56:43', 1, 'Gemini Generated Image mfi69ymfi69ymfi6');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `size` varchar(20) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `stock_quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `additional_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `size`, `color`, `stock_quantity`, `additional_price`, `created_at`, `updated_at`) VALUES
(14, 3, 's', 'pink /blue', 0, 0.03, '2026-10-07 18:39:47', '2026-10-09 23:49:15'),
(15, 3, 'm', 'pink /blue', 0, 0.03, '2026-10-07 18:39:48', '2026-10-10 20:38:53'),
(16, 3, 'l', 'pink /blue', 0, 0.03, '2026-10-07 18:39:48', '2026-10-10 20:23:37'),
(17, 3, 'xl.xxl', 'pink /blue', 0, 0.03, '2026-10-07 18:39:48', '2026-10-10 20:38:53'),
(18, 4, 's', NULL, 12, 0.04, '2026-10-10 22:47:53', '2026-10-10 22:53:25'),
(19, 4, 'm', NULL, 14, 0.04, '2026-10-10 22:47:53', '2026-10-10 22:47:53'),
(20, 4, 'l', NULL, 14, 0.04, '2026-10-10 22:47:53', '2026-10-10 22:47:53'),
(21, 4, 'xl.xxl', NULL, 14, 0.04, '2026-10-10 22:47:53', '2026-10-10 22:47:53'),
(22, 5, 's', NULL, 6, 0.00, '2026-10-10 23:46:35', '2026-10-10 23:47:58'),
(23, 5, 'm', NULL, 6, 0.00, '2026-10-10 23:46:35', '2026-10-10 23:47:58'),
(24, 5, 'l', NULL, 6, 0.00, '2026-10-10 23:46:35', '2026-10-10 23:47:58'),
(25, 5, 'xl.xxl', NULL, 6, 0.00, '2026-10-10 23:46:35', '2026-10-10 23:47:58'),
(26, 6, 's', 'pink', 34, 0.00, '2026-10-11 02:55:29', '2026-10-11 02:56:43'),
(27, 6, 's', 'blue', 34, 0.00, '2026-10-11 02:55:29', '2026-10-11 02:56:43'),
(28, 6, 's', 'yellow', 34, 0.00, '2026-10-11 02:55:29', '2026-10-11 02:56:43'),
(29, 6, 'm', 'pink', 34, 0.00, '2026-10-11 02:55:29', '2026-10-11 02:56:43'),
(30, 6, 'm', 'blue', 34, 0.00, '2026-10-11 02:55:29', '2026-10-11 02:56:43'),
(31, 6, 'm', 'yellow', 34, 0.00, '2026-10-11 02:55:29', '2026-10-11 02:56:43'),
(32, 6, 'l', 'pink', 34, 0.00, '2026-10-11 02:55:29', '2026-10-11 02:56:43'),
(33, 6, 'l', 'blue', 34, 0.00, '2026-10-11 02:55:29', '2026-10-11 02:56:43'),
(34, 6, 'l', 'yellow', 34, 0.00, '2026-10-11 02:55:29', '2026-10-11 02:56:43'),
(35, 6, 'xl.xxl', 'pink', 34, 0.00, '2026-10-11 02:55:29', '2026-10-11 02:56:43'),
(36, 6, 'xl.xxl', 'blue', 34, 0.00, '2026-10-11 02:55:29', '2026-10-11 02:56:43'),
(37, 6, 'xl.xxl', 'yellow', 34, 0.00, '2026-10-11 02:55:29', '2026-10-11 02:56:43');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `comment` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_methods`
--

CREATE TABLE `shipping_methods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estimated_days` smallint(5) UNSIGNED NOT NULL DEFAULT 3,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping_methods`
--

INSERT INTO `shipping_methods` (`id`, `name`, `description`, `price`, `estimated_days`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Pakistan-wide Standard Delivery', 'Standard courier delivery across Pakistan.', 250.00, 4, 'active', '2026-10-07 17:47:57', '2026-10-07 17:47:57');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_settings`
--

CREATE TABLE `shipping_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nationwide` tinyint(1) NOT NULL DEFAULT 1,
  `default_charge` int(10) UNSIGNED NOT NULL DEFAULT 250,
  `free_threshold` int(10) UNSIGNED NOT NULL DEFAULT 3000,
  `karachi_charge` int(10) UNSIGNED NOT NULL DEFAULT 150,
  `other_city_charge` int(10) UNSIGNED NOT NULL DEFAULT 250,
  `remote_area_charge` int(10) UNSIGNED NOT NULL DEFAULT 350,
  `karachi_days` varchar(50) NOT NULL DEFAULT '1-2',
  `other_days` varchar(50) NOT NULL DEFAULT '3-5',
  `courier` varchar(100) NOT NULL DEFAULT 'Call Courier',
  `cod_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `cod_fee` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping_settings`
--

INSERT INTO `shipping_settings` (`id`, `nationwide`, `default_charge`, `free_threshold`, `karachi_charge`, `other_city_charge`, `remote_area_charge`, `karachi_days`, `other_days`, `courier`, `cod_enabled`, `cod_fee`, `created_at`, `updated_at`) VALUES
(1, 1, 250, 50000, 150, 250, 350, '1-2', '3-5', 'Call Courier', 1, 100, '2026-10-09 18:05:45', '2026-10-09 18:10:53');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_name` varchar(255) NOT NULL DEFAULT 'BeastWear.pk',
  `tagline` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `whatsapp` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `tiktok` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `min_order` int(10) UNSIGNED NOT NULL DEFAULT 1000,
  `order_confirmation` varchar(30) NOT NULL DEFAULT 'whatsapp',
  `cod_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `auto_confirm_prepaid` tinyint(1) NOT NULL DEFAULT 1,
  `low_stock_alerts` tinyint(1) NOT NULL DEFAULT 1,
  `maintenance` tinyint(1) NOT NULL DEFAULT 0,
  `currency` varchar(3) NOT NULL DEFAULT 'PKR',
  `timezone` varchar(50) NOT NULL DEFAULT 'Asia/Karachi',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_account_title` varchar(255) DEFAULT NULL,
  `bank_account_number` varchar(255) DEFAULT NULL,
  `jazzcash_account_title` varchar(255) DEFAULT NULL,
  `jazzcash_account_number` varchar(255) DEFAULT NULL,
  `easypaisa_account_title` varchar(255) DEFAULT NULL,
  `easypaisa_account_number` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `store_name`, `tagline`, `logo`, `favicon`, `email`, `phone`, `whatsapp`, `address`, `instagram`, `facebook`, `tiktok`, `youtube`, `min_order`, `order_confirmation`, `cod_enabled`, `auto_confirm_prepaid`, `low_stock_alerts`, `maintenance`, `currency`, `timezone`, `created_at`, `updated_at`, `bank_name`, `bank_account_title`, `bank_account_number`, `jazzcash_account_title`, `jazzcash_account_number`, `easypaisa_account_title`, `easypaisa_account_number`) VALUES
(1, 'BeastWear.pk', 'Streetwear built for beasts.', NULL, NULL, 'support@beastwear.pk', '021-34567890', '0300-1234567', 'Shop 12, Zamzama Commercial Lane 4, DHA Phase 5, Karachi', 'https://instagram.com/beastwear.pk', 'https://facebook.com/beastwear.pk', 'https://tiktok.com/@beastwear.pk', NULL, 1000, 'whatsapp', 1, 1, 1, 0, 'PKR', 'Asia/Karachi', '2026-10-09 18:27:17', '2026-10-09 23:49:03', NULL, NULL, NULL, '03458848846', 'mirza abdullah baig', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'customer',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `role`, `status`, `remember_token`, `created_at`, `updated_at`, `is_admin`) VALUES
(2, 'Abdullah mirza', 'abdullhmirza490@gmail.com', '03458848846', NULL, '$2y$12$GkBUM3eqaZPAjtL1Ctz9veFF69GLjFxWxgsV6a472d7I/J2B/Uuom', 'customer', 'active', '8WXXax4yEeVCymHdy8pEriV4e4ZoxfOr0TBXBI4pbo7B3TQhOfdDESa4tWMR', '2026-10-07 18:06:50', '2026-10-07 18:06:50', 1),
(3, 'ZAIN', 'abdullhmirza4490@gmail.com', NULL, NULL, '$2y$12$ZG.AziKHNQ4NBY8cpfDSJe0.1kNqxHlC/fLthQkkH5fJgNzLmk1se', 'admin', 'active', 'Fa7bsJ2dWZF7piMBoEE1XpvWGBWwiN9jx8vemnhZviyRndJEmKtTnkTGGOsC', '2026-10-10 22:25:02', '2026-10-10 22:25:02', 0);

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(2, 2, '2026-10-07 18:06:50', '2026-10-07 18:06:50'),
(3, 3, '2026-10-10 22:25:02', '2026-10-10 22:25:02');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_items`
--

CREATE TABLE `wishlist_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wishlist_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addresses_user_id_is_default_index` (`user_id`,`is_default`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `banners_status_index` (`status`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `carts_user_id_unique` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cart_item_options_unique` (`cart_id`,`product_id`,`selection_key`),
  ADD KEY `cart_items_product_id_foreign` (`product_id`),
  ADD KEY `cart_items_product_variant_id_foreign` (`product_variant_id`),
  ADD KEY `cart_items_selected_design_image_id_foreign` (`selected_design_image_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`),
  ADD KEY `categories_status_index` (`status`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contact_messages_status_index` (`status`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`),
  ADD KEY `coupons_status_index` (`status`);

--
-- Indexes for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupon_usages_user_id_foreign` (`user_id`),
  ADD KEY `coupon_usages_order_id_foreign` (`order_id`),
  ADD KEY `coupon_usages_coupon_id_user_id_index` (`coupon_id`,`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD UNIQUE KEY `orders_public_tracking_id_unique` (`public_tracking_id`),
  ADD KEY `orders_coupon_id_foreign` (`coupon_id`),
  ADD KEY `orders_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `orders_payment_status_index` (`payment_status`),
  ADD KEY `orders_order_status_index` (`order_status`),
  ADD KEY `orders_shipping_status_index` (`shipping_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`),
  ADD KEY `order_items_product_variant_id_foreign` (`product_variant_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_transaction_id_unique` (`transaction_id`),
  ADD KEY `payments_order_id_foreign` (`order_id`),
  ADD KEY `payments_status_index` (`status`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_category_id_status_index` (`category_id`,`status`),
  ADD KEY `products_status_index` (`status`),
  ADD KEY `products_featured_index` (`featured`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_sort_order_index` (`product_id`,`sort_order`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_variants_product_id_size_color_unique` (`product_id`,`size`,`color`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviews_user_id_foreign` (`user_id`),
  ADD KEY `reviews_order_id_foreign` (`order_id`),
  ADD KEY `reviews_product_id_status_index` (`product_id`,`status`),
  ADD KEY `reviews_status_index` (`status`);

--
-- Indexes for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipping_methods_status_index` (`status`);

--
-- Indexes for table `shipping_settings`
--
ALTER TABLE `shipping_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlists_user_id_unique` (`user_id`);

--
-- Indexes for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlist_items_wishlist_id_product_id_unique` (`wishlist_id`,`product_id`),
  ADD KEY `wishlist_items_product_id_foreign` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shipping_settings`
--
ALTER TABLE `shipping_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cart_items_selected_design_image_id_foreign` FOREIGN KEY (`selected_design_image_id`) REFERENCES `product_images` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD CONSTRAINT `coupon_usages_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usages_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `coupon_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD CONSTRAINT `wishlist_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_items_wishlist_id_foreign` FOREIGN KEY (`wishlist_id`) REFERENCES `wishlists` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
