-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2025 at 12:50 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `web`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminlist`
--

CREATE TABLE `adminlist` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role` enum('Admin','User') DEFAULT 'User',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `company` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adminlist`
--

INSERT INTO `adminlist` (`id`, `username`, `email`, `role`, `status`, `company`, `password`) VALUES
(2, 'vennila', 'vennila@gmail.com', 'User', 'Active', '', '$2y$10$Sso2qgyzC/ZUHGhDkv5IOui.r3NotKsG6I38YQ7o3bPuwff0csS/S'),
(3, 'Priya', 'priya@gmail.com', 'User', 'Active', 'Samudhra Tech Solutions', '$2y$10$6hZ57xhaqb2jmf4lgUb.OeW1WHjZCyJzts18x7Y/Jd0akCyQ8juke');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(46, 'Dress'),
(2, 'Electronics'),
(48, 'Fruits'),
(4, 'Furnitures'),
(3, 'Grocery'),
(1, 'Stationery');

-- --------------------------------------------------------

--
-- Table structure for table `company_settings`
--

CREATE TABLE `company_settings` (
  `id` int(11) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `company_logo_path` varchar(255) DEFAULT NULL,
  `address_line_1` varchar(150) NOT NULL,
  `address_line_2` varchar(150) DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email_address` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `gstin` varchar(15) NOT NULL,
  `pan_number` varchar(10) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_settings`
--

INSERT INTO `company_settings` (`id`, `company_name`, `company_logo_path`, `address_line_1`, `address_line_2`, `city`, `state`, `pincode`, `phone_number`, `email_address`, `website`, `gstin`, `pan_number`, `bank_name`, `account_number`, `ifsc_code`) VALUES
(1, 'Samudhra Tech Solutions', '/assets/img/logo.png', '1/433x,Muthammal Colony', '3rd Street', 'THOOTHUKUDI', 'TAMIL NADU', '628002', '9990001111', 'support@samudhratechsolutions.com', NULL, '33AAAAA0000A1Z5', 'ABCDE1234F', 'HDFC Bank', '50100123456789', 'HDFC0000005');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zipcode` varchar(10) NOT NULL,
  `ship_address` text DEFAULT NULL,
  `ship_country` varchar(50) DEFAULT NULL,
  `ship_city` varchar(50) DEFAULT NULL,
  `ship_state` varchar(50) DEFAULT NULL,
  `ship_zipcode` varchar(10) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gstin` varchar(20) NOT NULL,
  `pan` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_name`, `company_name`, `address`, `country`, `city`, `state`, `zipcode`, `ship_address`, `ship_country`, `ship_city`, `ship_state`, `ship_zipcode`, `phone`, `email`, `gstin`, `pan`, `created_at`) VALUES
(1, 'Libina', NULL, '24B sathiram bus stand,thoothukudi,TamilNadu', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '9025062327', 'libina@gmail.com', '', NULL, '2025-09-24 14:27:07'),
(2, 'samudhra', NULL, 'kpl, tuty tamilnadu', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '98765445678', 'kal@gamil.com', '', NULL, '2025-09-29 06:26:49'),
(4, 'Abi', NULL, '2/17 thoothukudi', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '9878967895', 'abi@gmail.com', '', NULL, '2025-10-14 07:11:41'),
(5, 'nila', 'STS', '178 a thoothukudi', 'India', 'thoothukudi', 'tamil.nadu', '628001', NULL, NULL, NULL, NULL, NULL, '8946787846', 'nila@gmail.com', '8F977FG5678', 'AAAPA1234A', '2025-10-14 10:02:31'),
(8, 'devi', 'STS', '145 Thoothukudi', 'India', 'Thoothukudi', 'Tamilnadu', '628001', '145 old bus stand thoothukudi', 'india', 'Thoothukdi', 'tamilnadu', '628001', '7890384873', 'devi@gmail.com', '56FG6374687', 'AAAA765', '2025-10-14 10:19:11'),
(13, 'priya', 'STS', '45 Gandhi nagar', 'India', 'Thoothukudi', 'Tamilnadu', '628001', '45 Gandhi nagar', 'India', 'Thoothukudi', 'Tamilnadu', '628001', '7894758956', 'priya@gmail.com', '67GB788746', 'AAAA7883', '2025-10-14 10:26:19');

-- --------------------------------------------------------

--
-- Table structure for table `ledgers`
--

CREATE TABLE `ledgers` (
  `id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `ledger_name` varchar(255) DEFAULT NULL,
  `account_group` varchar(255) DEFAULT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(50) DEFAULT NULL,
  `opening_amount` decimal(10,2) DEFAULT 0.00,
  `gst_details` varchar(255) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `opening_type` varchar(20) DEFAULT 'To Receive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments_in`
--

CREATE TABLE `payments_in` (
  `id` int(11) NOT NULL,
  `pi_number` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `party_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_mode` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments_in`
--

INSERT INTO `payments_in` (`id`, `pi_number`, `date`, `party_id`, `amount`, `payment_mode`, `notes`, `created_at`) VALUES
(1, 'PI/25-26/0001', '2025-10-07', 4, 41.74, 'Cash A/c', '', '2025-10-07 07:43:42'),
(2, 'PI/25-26/0002', '2025-10-07', 1, 500.00, 'Cash A/c', '', '2025-10-07 07:44:00'),
(3, 'PI/25-26/0003', '2025-10-08', 6, 505.00, 'Cash A/c', '', '2025-10-08 13:42:58'),
(4, 'PI/25-26/0004', '2025-10-08', 2, 335.00, 'Cash A/c', '', '2025-10-08 13:58:06');

-- --------------------------------------------------------

--
-- Table structure for table `payments_out`
--

CREATE TABLE `payments_out` (
  `id` int(11) NOT NULL,
  `pm_number` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `party_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_mode` varchar(50) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments_out`
--

INSERT INTO `payments_out` (`id`, `pm_number`, `date`, `party_id`, `amount`, `payment_mode`, `notes`, `created_at`) VALUES
(15, 'PM/25-26/0001', '2025-10-07', 6, 1000.00, 'Cash A/c', 'amount half paid', '2025-10-07 05:56:06'),
(16, 'PM/25-26/0002', '2025-10-07', 3, 857.00, 'Cash A/c', '', '2025-10-07 07:46:13'),
(17, 'PM/25-26/0003', '2025-10-08', 1, 65.00, 'Cash A/c', '', '2025-10-08 13:57:36');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `rate` decimal(10,2) DEFAULT NULL,
  `discount` decimal(5,2) DEFAULT 0.00,
  `gst` decimal(5,2) DEFAULT 0.00,
  `hsn_code` varchar(50) DEFAULT NULL,
  `stock_quantity` decimal(10,2) DEFAULT NULL,
  `mrp` decimal(10,2) DEFAULT NULL,
  `reorder_level` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `category`, `rate`, `discount`, `gst`, `hsn_code`, `stock_quantity`, `mrp`, `reorder_level`) VALUES
(1, 1, 'Note', 'Stationery', 45.00, 5.00, 10.00, '', 110.00, 0.00, 0.00),
(2, 1, 'Book', 'Stationery', 30.00, 5.00, 5.00, '', 86.00, 0.00, 0.00),
(3, 1, 'Pen', 'Stationery', 50.00, 5.00, 10.00, '', 66.00, 0.00, 0.00),
(4, 1, 'Pencil', 'Stationery', 5.00, 2.00, 2.00, NULL, NULL, NULL, NULL),
(5, 2, 'DEll', 'Electronics', 50000.00, 10.00, 18.00, '', -2.00, 0.00, 0.00),
(6, 2, 'Mouse', 'Electronics', 500.00, 5.00, 10.00, '', 31.00, 0.00, 0.00),
(8, NULL, 'wood bed', 'Furnitures', 25000.00, 15.00, 14.95, '', 97.00, 23000.00, 55.00),
(9, NULL, 'Milk', 'Grocery', 20.00, 5.00, 5.00, '', 65.00, 14.98, 0.00),
(58, NULL, 'Kids wear', 'Dress', 500.00, 2.00, 5.00, '2', 100.00, 400.00, 5.00),
(59, NULL, 'Chudi', 'Dress', 800.00, 2.00, 5.00, '3', 498.00, 500.00, 5.00),
(60, NULL, 'Apple', 'Fruits', 300.00, 2.00, 5.00, '1', 185.00, 250.00, 5.00),
(61, NULL, 'orange', 'Fruits', 100.00, 2.00, 5.00, '2', 199.00, 80.00, 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_invoices`
--

CREATE TABLE `purchase_invoices` (
  `pi_id` int(11) NOT NULL,
  `pi_number` varchar(50) NOT NULL,
  `pi_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `supplier_id` int(11) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `supplier_bill_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `tax_mode` enum('Inclusive','Exclusive') DEFAULT 'Inclusive',
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_invoices`
--

INSERT INTO `purchase_invoices` (`pi_id`, `pi_number`, `pi_date`, `due_date`, `supplier_id`, `po_id`, `supplier_bill_no`, `notes`, `terms`, `tax_mode`, `grand_total`, `created_at`) VALUES
(1, 'PI/25-26/0001', '2025-09-01', '2025-09-06', 3, NULL, 'ORG/PI/001', '', '', 'Inclusive', 0.00, '2025-09-24 14:59:44'),
(3, 'PI/25-26/0002', '2025-09-04', '2025-09-08', 4, NULL, 'ORG/-002', '', '', 'Inclusive', 0.00, '2025-09-25 04:52:35'),
(4, 'PI/25-26/0003', '2025-09-09', '2025-09-08', 5, NULL, '123', '', '', 'Inclusive', 0.00, '2025-09-29 07:37:24'),
(36, 'PI/25-26/0004', '2025-10-01', '2025-10-05', 4, NULL, '5677', '', '', 'Inclusive', 2565.00, '2025-10-15 10:33:54');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_invoice_items`
--

CREATE TABLE `purchase_invoice_items` (
  `id` int(11) NOT NULL,
  `pi_id` int(11) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `discount` decimal(5,2) DEFAULT 0.00,
  `gst` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(12,2) NOT NULL,
  `tax_mode` enum('Inclusive','Exclusive') DEFAULT 'Inclusive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_invoice_items`
--

INSERT INTO `purchase_invoice_items` (`id`, `pi_id`, `category`, `product_name`, `quantity`, `rate`, `discount`, `gst`, `line_total`, `tax_mode`) VALUES
(9, 1, 'Furnitures', 'wood bed', 1.00, 25000.00, 15.00, 14.95, 21250.00, 'Inclusive'),
(10, 1, 'Electronics', 'Mouse', 2.00, 500.00, 5.00, 10.00, 950.00, 'Inclusive'),
(12, 3, 'Grocery', 'Milk', 10.00, 20.00, 5.00, 5.00, 190.00, 'Inclusive'),
(13, 4, 'Electronics', 'DEll', 1.00, 50000.00, 10.00, 16.00, 45000.00, 'Inclusive'),
(34, 36, 'Electronics', 'Mouse', 5.00, 500.00, 5.00, 10.00, 2375.00, 'Inclusive'),
(35, 36, 'Grocery', 'Milk', 10.00, 20.00, 5.00, 5.00, 190.00, 'Inclusive');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `po_id` int(11) NOT NULL,
  `po_number` varchar(50) DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `expected_date` date DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `supplier_bill_no` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_mode` enum('Inclusive','Exclusive') NOT NULL DEFAULT 'Inclusive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`po_id`, `po_number`, `po_date`, `expected_date`, `supplier_id`, `supplier_bill_no`, `notes`, `terms`, `total_amount`, `tax_mode`) VALUES
(70, 'PO/25-26/0031', '2025-09-06', '2025-09-13', 4, 'ORG/003', '', '', 0.00, 'Inclusive'),
(78, 'PO/25-26/0033', '2025-09-08', '2025-09-12', 5, 'ORG/003', '', '', 0.00, 'Inclusive'),
(79, 'PO/25-26/0034', '2025-09-01', '2025-09-05', 3, 'ORG/001', '', '', 0.00, 'Inclusive'),
(80, 'PO/25-26/0035', '2025-09-08', '2025-09-11', 6, 'ORG/005', '', '', 0.00, 'Inclusive'),
(81, 'PO/25-26/0036', '2025-10-07', '2025-10-11', 6, 'ORG/002', '', '', 0.00, 'Inclusive'),
(82, 'PO/25-26/0037', '2025-10-02', '2025-10-04', 4, 'ORG/003', '', '', 0.00, 'Inclusive'),
(85, 'PO/25-26/0038', '2025-10-01', '0000-00-00', 1, '', '', '', 1568.00, 'Inclusive'),
(86, 'PO/25-26/0039', '2025-10-01', '2025-10-08', 1, '7895', '', '', 784.00, 'Inclusive'),
(87, 'PO/25-26/0040', '2025-10-02', '2025-10-03', 2, '678', '', '', 85784.00, 'Inclusive');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `item_id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `discount` decimal(5,2) DEFAULT 0.00,
  `gst` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(12,2) NOT NULL,
  `tax_mode` varchar(50) DEFAULT 'Exclusive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`item_id`, `po_id`, `category`, `product_name`, `quantity`, `rate`, `discount`, `gst`, `line_total`, `tax_mode`) VALUES
(52, 70, 'Stationery', 'Book', 1.00, 30.00, 5.00, 5.00, 28.50, 'E'),
(56, 78, 'Electronics', 'Mouse', 5.00, 500.00, 5.00, 10.00, 2375.00, 'Inclusive'),
(57, 79, 'Stationery', 'Book', 25.00, 30.00, 5.00, 5.00, 712.50, 'Inclusive'),
(58, 80, 'Grocery', 'Milk', 15.00, 20.00, 5.00, 5.00, 285.00, 'Inclusive'),
(59, 81, 'Stationery', 'Pen', 1.00, 50.00, 5.00, 10.00, 47.50, 'Inclusive'),
(60, 81, 'Stationery', 'Pencil', 50.00, 5.00, 2.00, 2.00, 245.00, 'Inclusive'),
(61, 82, 'Furnitures', 'wood bed', 1.00, 25000.00, 15.00, 14.95, 21250.00, 'Inclusive'),
(64, 85, 'Dress', 'Chudi', 2.00, 800.00, 2.00, 5.00, 1568.00, 'Inclusive'),
(65, 86, 'Dress', 'Chudi', 1.00, 800.00, 2.00, 5.00, 784.00, 'Inclusive'),
(66, 87, 'Dress', 'Chudi', 1.00, 800.00, 2.00, 5.00, 784.00, 'Inclusive'),
(67, 87, 'Furnitures', 'wood bed', 4.00, 25000.00, 15.00, 14.95, 85000.00, 'Inclusive');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_returns`
--

CREATE TABLE `purchase_returns` (
  `return_id` int(11) NOT NULL,
  `return_no` varchar(50) NOT NULL,
  `bill_no` varchar(50) NOT NULL,
  `bill_date` date NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `state` varchar(100) NOT NULL,
  `tax_mode` varchar(20) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_returns`
--

INSERT INTO `purchase_returns` (`return_id`, `return_no`, `bill_no`, `bill_date`, `supplier_id`, `state`, `tax_mode`, `description`) VALUES
(5, 'PR/25-26/0003', '34565', '2025-10-16', 4, 'Tamil Nadu', 'Inclusive', ''),
(6, 'PR/25-26/0004', 'ORG/001', '2025-10-08', 4, 'Tamil Nadu', 'Inclusive', ''),
(7, 'PR/25-26/0005', '987', '2025-10-01', 6, 'Tamil Nadu', 'Inclusive', ''),
(8, 'PR/25-26/0006', '678', '2025-10-06', 4, 'Tamil Nadu', 'Inclusive', ''),
(9, 'PR/25-26/0007', '789', '2025-10-09', 4, 'Tamil Nadu', 'Inclusive', '');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_return_items`
--

CREATE TABLE `purchase_return_items` (
  `pri_id` int(11) NOT NULL,
  `pr_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `discount` decimal(5,2) NOT NULL,
  `gst` decimal(5,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_return_items`
--

INSERT INTO `purchase_return_items` (`pri_id`, `pr_id`, `product_id`, `quantity`, `rate`, `discount`, `gst`, `line_total`) VALUES
(5, 5, 6, 1.00, 500.00, 5.00, 10.00, 475.00),
(6, 6, 9, 1.00, 20.00, 5.00, 5.00, 19.00),
(7, 7, 3, 5.00, 50.00, 5.00, 10.00, 237.50),
(8, 8, 4, 10.00, 5.00, 2.00, 2.00, 49.00),
(9, 9, 8, 1.00, 25000.00, 15.00, 14.95, 21250.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales_invoices`
--

CREATE TABLE `sales_invoices` (
  `si_id` int(11) NOT NULL,
  `si_number` varchar(50) NOT NULL,
  `si_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_bill_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `tax_mode` enum('Inclusive','Exclusive') DEFAULT 'Inclusive',
  `terms` text DEFAULT NULL,
  `so_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_invoices`
--

INSERT INTO `sales_invoices` (`si_id`, `si_number`, `si_date`, `due_date`, `customer_id`, `customer_bill_no`, `notes`, `tax_mode`, `terms`, `so_id`, `created_at`, `updated_at`, `grand_total`) VALUES
(2, 'SI/25-26/0001', '2025-09-26', '2025-10-10', 1, 'ORG/25-26/01', '', 'Inclusive', '', 13, '2025-09-26 05:19:28', '2025-09-26 05:19:28', 0.00),
(7, 'SI/25-26/0006', '2025-09-29', '2025-10-07', 2, '678', '', 'Inclusive', '', NULL, '2025-10-07 10:00:15', '2025-10-07 10:00:15', 0.00),
(20, 'SI/25-26/0007', '2025-10-05', '2025-10-15', 4, '5674', '', 'Inclusive', '', NULL, '2025-10-15 07:27:27', '2025-10-15 07:27:27', 0.00),
(21, 'SI/25-26/0008', '2025-10-01', '2025-10-05', 5, '576', '', 'Inclusive', '', NULL, '2025-10-15 09:54:30', '2025-10-15 09:54:30', 611.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales_invoice_items`
--

CREATE TABLE `sales_invoice_items` (
  `sii_id` int(11) NOT NULL,
  `si_id` int(11) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `discount` decimal(5,2) DEFAULT 0.00,
  `gst` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(10,2) NOT NULL,
  `tax_mode` enum('Inclusive','Exclusive') DEFAULT 'Inclusive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_invoice_items`
--

INSERT INTO `sales_invoice_items` (`sii_id`, `si_id`, `category`, `product_name`, `quantity`, `rate`, `discount`, `gst`, `line_total`, `tax_mode`) VALUES
(2, 2, 'Furnitures', 'wood bed', 1, 25000.00, 5.00, 14.95, 23750.00, 'Inclusive'),
(7, 7, 'Stationery', 'Pen', 1, 50.00, 5.00, 10.00, 47.50, 'Inclusive'),
(18, 20, 'Dress', 'Chudi', 2, 800.00, 2.00, 5.00, 1568.00, 'Inclusive'),
(19, 20, 'Furnitures', 'wood bed', 1, 25000.00, 15.00, 14.95, 21250.00, 'Inclusive'),
(20, 20, 'Fruits', 'Apple', 15, 300.00, 2.00, 5.00, 4410.00, 'Inclusive'),
(21, 21, 'Fruits', 'orange', 1, 100.00, 2.00, 5.00, 98.00, 'Inclusive'),
(22, 21, 'Stationery', 'Book', 18, 30.00, 5.00, 5.00, 513.00, 'Inclusive');

-- --------------------------------------------------------

--
-- Table structure for table `sales_orders`
--

CREATE TABLE `sales_orders` (
  `so_id` int(11) NOT NULL,
  `so_number` varchar(50) NOT NULL,
  `so_date` date NOT NULL,
  `customer_id` int(11) NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `customer_bill_no` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `tax_mode` varchar(20) DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_orders`
--

INSERT INTO `sales_orders` (`so_id`, `so_number`, `so_date`, `customer_id`, `delivery_date`, `customer_bill_no`, `notes`, `tax_mode`, `terms`, `grand_total`) VALUES
(13, 'SO/25-26/0001', '2025-09-01', 1, '2025-09-25', 'ORG/25-26/01', '', 'Inclusive', '', 0.00),
(18, 'SO/25-26/0006', '2025-09-29', 2, '2025-10-01', 'ORG/009', '', 'Inclusive', '', 0.00),
(19, 'SO/25-26/0007', '2025-10-05', 1, '2025-10-07', NULL, '', 'Inclusive', '', 0.00),
(20, 'SO/25-26/0008', '2025-09-30', 13, '2025-10-04', NULL, '', 'Inclusive', '', 87500.00),
(22, 'SO/25-26/0009', '2025-10-01', 5, '2025-10-05', NULL, '', 'Inclusive', '', 45000.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales_order_items`
--

CREATE TABLE `sales_order_items` (
  `item_id` int(11) NOT NULL,
  `so_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `gst` decimal(10,2) DEFAULT 0.00,
  `line_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_mode` enum('Inclusive','Exclusive') DEFAULT 'Exclusive',
  `category` varchar(100) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_order_items`
--

INSERT INTO `sales_order_items` (`item_id`, `so_id`, `quantity`, `rate`, `discount`, `gst`, `line_total`, `tax_mode`, `category`, `product_name`) VALUES
(6, 13, 1.00, 25000.00, 5.00, 14.95, 23750.00, 'Inclusive', 'Furnitures', 'wood bed'),
(10, 18, 20.00, 5.00, 2.00, 2.00, 98.00, 'Inclusive', 'Stationery', 'Pencil'),
(11, 19, 5.00, 50000.00, 10.00, 18.00, 225000.00, 'Inclusive', 'Electronics', 'DEll'),
(12, 20, 1.00, 50000.00, 10.00, 18.00, 45000.00, 'Inclusive', 'Electronics', 'DEll'),
(13, 20, 2.00, 25000.00, 15.00, 14.95, 42500.00, 'Inclusive', 'Furnitures', 'wood bed'),
(14, 22, 1.00, 50000.00, 10.00, 18.00, 45000.00, 'Inclusive', 'Electronics', 'DEll');

-- --------------------------------------------------------

--
-- Table structure for table `sales_returns`
--

CREATE TABLE `sales_returns` (
  `return_id` int(11) NOT NULL,
  `return_no` varchar(50) NOT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `tax_mode` enum('Inclusive','Exclusive') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT 0.00,
  `return_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_returns`
--

INSERT INTO `sales_returns` (`return_id`, `return_no`, `invoice_no`, `invoice_date`, `customer_id`, `state`, `tax_mode`, `description`, `total_amount`, `return_date`) VALUES
(1, 'SR/25-26/0001', 'ORG/001', '2025-09-30', 2, 'Tamil Nadu', 'Inclusive', '', 0.00, '2025-10-03 04:37:12'),
(7, 'SR/25-26/0007', 'ORG/003', '2025-10-08', 2, 'Tamil Nadu', 'Inclusive', '', 0.00, '2025-10-03 06:23:48'),
(8, 'SR/25-26/0008', '987', '2025-09-30', 1, 'Tamil Nadu', 'Inclusive', '', 0.00, '2025-10-03 07:17:30'),
(9, 'SR/25-26/0009', '456', '2025-09-29', 1, 'Tamil Nadu', 'Inclusive', '', 0.00, '2025-10-03 07:18:24'),
(10, 'SR/25-26/0010', '987', '2025-10-02', 2, 'Tamil Nadu', 'Inclusive', '', 0.00, '2025-10-03 07:44:36'),
(11, 'SR/25-26/0011', '98839', '2025-10-04', 2, 'Tamil Nadu', 'Inclusive', '', 0.00, '2025-10-05 15:59:14'),
(12, 'SR/25-26/0012', '678', '2025-10-06', 1, 'Tamil Nadu', 'Inclusive', '', 0.00, '2025-10-06 06:01:52');

-- --------------------------------------------------------

--
-- Table structure for table `sales_return_items`
--

CREATE TABLE `sales_return_items` (
  `item_id` int(11) NOT NULL,
  `sr_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT NULL,
  `rate` decimal(12,2) DEFAULT NULL,
  `discount` decimal(5,2) DEFAULT NULL,
  `gst` decimal(5,2) DEFAULT NULL,
  `line_total` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_return_items`
--

INSERT INTO `sales_return_items` (`item_id`, `sr_id`, `product_id`, `quantity`, `rate`, `discount`, `gst`, `line_total`) VALUES
(1, 1, 6, 1.00, 500.00, 5.00, 10.00, 475.00),
(7, 7, 6, 1.00, 500.00, 5.00, 10.00, 475.00),
(8, 8, 5, 1.00, 50000.00, 10.00, 18.00, 45000.00),
(9, 9, 2, 1.00, 30.00, 5.00, 5.00, 28.50),
(10, 10, 4, 5.00, 5.00, 2.00, 2.00, 24.50),
(11, 11, 4, 1.00, 5.00, 2.00, 2.00, 4.90),
(12, 12, 4, 13.00, 5.00, 2.00, 2.00, 63.70);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `ledger_id` int(11) DEFAULT NULL,
  `supplier_name` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `ledger_id`, `supplier_name`, `address`, `phone`, `email`) VALUES
(1, NULL, 'Karthi', '3/10-4, Muthiahpuram', '6790856473', 'N/A'),
(2, NULL, 'Nila', 'fdoihnio59pjkp', '123456789', 'fjiuo654@123'),
(3, NULL, 'Abi', 'dyhuboih', '294629945', ''),
(4, NULL, 'Maha', '', '154166635623', '15465659@gmail.com'),
(5, NULL, 'mithra', 'kotkyeom', '6126', ''),
(6, NULL, 'sri', 'kk nagar,thoothukudi', '496595655', 'sri@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(10) NOT NULL DEFAULT 'User',
  `company` varchar(255) DEFAULT 'N/A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `role`, `company`) VALUES
(11, 'Abhi', 'abhi@gmail.com', '$2y$10$mDUU7w0.bKro.jutnjF34.nMGxEBSU7RybS8oV6tJugZEsO8Pkjt2', '2025-10-13 07:31:02', 'Admin', 'Samudhra Tech Solutions');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adminlist`
--
ALTER TABLE `adminlist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `company_settings`
--
ALTER TABLE `company_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gstin` (`gstin`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `ledgers`
--
ALTER TABLE `ledgers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments_in`
--
ALTER TABLE `payments_in`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments_out`
--
ALTER TABLE `payments_out`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_name` (`product_name`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD PRIMARY KEY (`pi_id`),
  ADD UNIQUE KEY `pi_number` (`pi_number`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `purchase_invoice_items`
--
ALTER TABLE `purchase_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pi_id` (`pi_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`po_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `po_id` (`po_id`);

--
-- Indexes for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `purchase_return_items`
--
ALTER TABLE `purchase_return_items`
  ADD PRIMARY KEY (`pri_id`),
  ADD KEY `pr_id` (`pr_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sales_invoices`
--
ALTER TABLE `sales_invoices`
  ADD PRIMARY KEY (`si_id`),
  ADD UNIQUE KEY `si_number` (`si_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `so_id` (`so_id`);

--
-- Indexes for table `sales_invoice_items`
--
ALTER TABLE `sales_invoice_items`
  ADD PRIMARY KEY (`sii_id`),
  ADD KEY `si_id` (`si_id`);

--
-- Indexes for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`so_id`),
  ADD UNIQUE KEY `so_number` (`so_number`);

--
-- Indexes for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `so_id` (`so_id`);

--
-- Indexes for table `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `sales_return_items`
--
ALTER TABLE `sales_return_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `sr_id` (`sr_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adminlist`
--
ALTER TABLE `adminlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `ledgers`
--
ALTER TABLE `ledgers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payments_in`
--
ALTER TABLE `payments_in`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments_out`
--
ALTER TABLE `payments_out`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  MODIFY `pi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `purchase_invoice_items`
--
ALTER TABLE `purchase_invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `po_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `purchase_return_items`
--
ALTER TABLE `purchase_return_items`
  MODIFY `pri_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sales_invoices`
--
ALTER TABLE `sales_invoices`
  MODIFY `si_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `sales_invoice_items`
--
ALTER TABLE `sales_invoice_items`
  MODIFY `sii_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `sales_orders`
--
ALTER TABLE `sales_orders`
  MODIFY `so_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `sales_returns`
--
ALTER TABLE `sales_returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sales_return_items`
--
ALTER TABLE `sales_return_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD CONSTRAINT `purchase_invoices_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_invoice_items`
--
ALTER TABLE `purchase_invoice_items`
  ADD CONSTRAINT `purchase_invoice_items_ibfk_1` FOREIGN KEY (`pi_id`) REFERENCES `purchase_invoices` (`pi_id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`po_id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD CONSTRAINT `purchase_returns_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `purchase_return_items`
--
ALTER TABLE `purchase_return_items`
  ADD CONSTRAINT `purchase_return_items_ibfk_1` FOREIGN KEY (`pr_id`) REFERENCES `purchase_returns` (`return_id`),
  ADD CONSTRAINT `purchase_return_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `sales_invoices`
--
ALTER TABLE `sales_invoices`
  ADD CONSTRAINT `sales_invoices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `sales_invoices_ibfk_2` FOREIGN KEY (`so_id`) REFERENCES `sales_orders` (`so_id`);

--
-- Constraints for table `sales_invoice_items`
--
ALTER TABLE `sales_invoice_items`
  ADD CONSTRAINT `sales_invoice_items_ibfk_1` FOREIGN KEY (`si_id`) REFERENCES `sales_invoices` (`si_id`);

--
-- Constraints for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  ADD CONSTRAINT `sales_order_items_ibfk_1` FOREIGN KEY (`so_id`) REFERENCES `sales_orders` (`so_id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD CONSTRAINT `sales_returns_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `sales_return_items`
--
ALTER TABLE `sales_return_items`
  ADD CONSTRAINT `sales_return_items_ibfk_1` FOREIGN KEY (`sr_id`) REFERENCES `sales_returns` (`return_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_return_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
