-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 27, 2026 at 08:10 AM
-- Server version: 10.11.14-MariaDB-cll-lve
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ssotoght_db_biss`
--

-- --------------------------------------------------------

--
-- Table structure for table `budget_items`
--

CREATE TABLE `budget_items` (
  `id` int(11) NOT NULL,
  `parent_item_id` int(11) DEFAULT NULL,
  `item_code` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `plan_id` int(11) NOT NULL,
  `io_id` varchar(50) DEFAULT NULL,
  `cc_id` varchar(50) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `brand_spec` varchar(255) DEFAULT NULL,
  `fiscal_year` int(4) NOT NULL DEFAULT 2025,
  `process` varchar(100) NOT NULL DEFAULT 'Preparation',
  `application_process` varchar(255) DEFAULT NULL,
  `condition_status` enum('Ready','Not Ready') DEFAULT NULL,
  `condition_notes` text DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `uom` varchar(20) NOT NULL DEFAULT 'Unit',
  `currency` varchar(10) NOT NULL DEFAULT 'IDR',
  `estimated_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `target_schedule` varchar(50) DEFAULT NULL,
  `evaluation_obstacle` text DEFAULT NULL,
  `evaluation_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_items`
--

INSERT INTO `budget_items` (`id`, `parent_item_id`, `item_code`, `category`, `plan_id`, `io_id`, `cc_id`, `item_name`, `brand_spec`, `fiscal_year`, `process`, `application_process`, `condition_status`, `condition_notes`, `qty`, `uom`, `currency`, `estimated_price`, `total_amount`, `target_schedule`, `evaluation_obstacle`, `evaluation_reason`) VALUES
(97, NULL, 'A', 'Machine', 12, NULL, NULL, 'Incoming Quality Check', NULL, 2026, 'Preparation', NULL, 'Not Ready', NULL, 1, 'Unit', 'IDR', 20000000.00, 20000000.00, NULL, NULL, NULL),
(98, NULL, 'B', 'Tooling And Equipment', 12, NULL, NULL, 'Silver Box Assembly Car', NULL, 2026, 'Preparation', NULL, 'Not Ready', NULL, 1, 'Unit', 'IDR', 10000000.00, 10000000.00, NULL, NULL, NULL),
(99, NULL, 'C', 'Testing And Equipment', 12, NULL, NULL, 'SDPPI', NULL, 2026, 'Preparation', NULL, 'Not Ready', NULL, 1, 'Unit', 'IDR', 60000000.00, 60000000.00, NULL, NULL, NULL),
(100, NULL, 'C', 'Testing And Equipment', 12, NULL, NULL, 'SNI', NULL, 2026, 'Preparation', NULL, 'Not Ready', NULL, 1, 'Unit', 'IDR', 8000000.00, 8000000.00, NULL, NULL, NULL),
(101, NULL, 'D', 'Facility Equipment Investment Plan', 12, NULL, NULL, 'Box Packaging & Wip', NULL, 2026, 'Preparation', NULL, 'Not Ready', NULL, 1, 'Unit', 'IDR', 7000000.00, 7000000.00, NULL, NULL, NULL),
(102, NULL, 'D', 'Facility Equipment Investment Plan', 12, NULL, NULL, 'Storage Raw Material & Finish Good', NULL, 2026, 'Preparation', NULL, 'Not Ready', NULL, 1, 'Unit', 'IDR', 15000000.00, 15000000.00, NULL, NULL, NULL),
(103, NULL, 'D', 'Facility Equipment Investment Plan', 12, NULL, NULL, 'Mechanical Electrical Supporting', NULL, 2026, 'Preparation', NULL, 'Not Ready', NULL, 1, 'Unit', 'IDR', 5000000.00, 5000000.00, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `budget_plans`
--

CREATE TABLE `budget_plans` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `io_id` int(11) DEFAULT NULL,
  `cost_center_id` int(11) DEFAULT NULL,
  `start_year` int(4) DEFAULT NULL,
  `end_year` int(11) DEFAULT NULL,
  `fiscal_year` int(4) NOT NULL,
  `description` text DEFAULT NULL,
  `purpose` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `io_number` varchar(50) DEFAULT NULL,
  `cc_code` varchar(50) DEFAULT NULL,
  `investment_type` varchar(50) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `status` enum('Draft','Submitted','Approved','Rejected') NOT NULL DEFAULT 'Draft',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `current_approver_role` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `dept_head_id` bigint(20) UNSIGNED DEFAULT NULL,
  `dept_head_approved_at` timestamp NULL DEFAULT NULL,
  `div_head_id` bigint(20) UNSIGNED DEFAULT NULL,
  `div_head_approved_at` timestamp NULL DEFAULT NULL,
  `finance_id` bigint(20) UNSIGNED DEFAULT NULL,
  `finance_approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_plans`
--

INSERT INTO `budget_plans` (`id`, `project_id`, `io_id`, `cost_center_id`, `start_year`, `end_year`, `fiscal_year`, `description`, `purpose`, `department`, `io_number`, `cc_code`, `investment_type`, `customer`, `model`, `created_by`, `status`, `submitted_at`, `current_approver_role`, `created_at`, `dept_head_id`, `dept_head_approved_at`, `div_head_id`, `div_head_approved_at`, `finance_id`, `finance_approved_at`) VALUES
(12, 6, 7, NULL, NULL, NULL, 2026, 'Budget Plan 2026', 'Production', 'PMA', '1501101277', 'cc-tr', 'Capex', 'Korean International Automotive', 'QY, SP2/SP3, KY', 6, 'Approved', '2026-02-18 08:56:19', NULL, '2026-02-16 09:52:48', 12, '2026-02-18 02:50:24', 13, '2026-02-18 03:21:04', 1, '2026-02-18 09:05:59');

-- --------------------------------------------------------

--
-- Table structure for table `budget_transfers`
--

CREATE TABLE `budget_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `budget_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `source_plan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `source_io_number` varchar(255) DEFAULT NULL,
  `source_project_name` varchar(255) DEFAULT NULL,
  `target_plan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `target_io_number` varchar(255) DEFAULT NULL,
  `target_project_name` varchar(255) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `business_category` varchar(255) DEFAULT NULL,
  `fiscal_year` varchar(4) DEFAULT NULL,
  `reason` text NOT NULL,
  `berita_acara_path` varchar(255) NOT NULL,
  `berita_acara_filename` varchar(255) NOT NULL,
  `transferred_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_assets`
--

CREATE TABLE `master_assets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset_no` varchar(50) NOT NULL,
  `asset_name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_categories`
--

CREATE TABLE `master_categories` (
  `id` int(11) NOT NULL,
  `category_code` varchar(50) DEFAULT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_categories`
--

INSERT INTO `master_categories` (`id`, `category_code`, `category_name`, `description`, `type`, `created_at`) VALUES
(1, 'WH', 'Wiring Harness', '', NULL, '2025-12-12 06:38:34'),
(2, 'AEP', 'Automotive Electronics Part', '', NULL, '2025-12-12 06:38:50'),
(3, 'PES', 'Power & Energy Solution', '', NULL, '2025-12-12 06:39:08'),
(4, 'AMR', 'AMR System', '', NULL, '2025-12-12 06:39:22');

-- --------------------------------------------------------

--
-- Table structure for table `master_cost_center`
--

CREATE TABLE `master_cost_center` (
  `id` int(11) NOT NULL,
  `cc_code` varchar(50) NOT NULL,
  `cc_name` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_cost_center`
--

INSERT INTO `master_cost_center` (`id`, `cc_code`, `cc_name`, `department`) VALUES
(1, 'cc-tr', 'const center', 'Finance');

-- --------------------------------------------------------

--
-- Table structure for table `master_currencies`
--

CREATE TABLE `master_currencies` (
  `id` int(11) NOT NULL,
  `currency_code` varchar(10) NOT NULL,
  `currency_name` varchar(100) NOT NULL,
  `symbol` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_customers`
--

CREATE TABLE `master_customers` (
  `id` int(11) NOT NULL,
  `customer_code` varchar(50) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_customers`
--

INSERT INTO `master_customers` (`id`, `customer_code`, `customer_name`, `contact`, `created_at`) VALUES
(1, 'AHM', 'Astra Honda Motor', '', '2025-12-12 07:49:43'),
(2, 'TAM', 'Toyota Astra Motor', '', '2025-12-12 07:49:56'),
(3, 'KIA', 'Korean International Automotive', NULL, '2026-02-16 08:32:38'),
(4, 'TDII', 'Toyodenso Indonesia', NULL, '2026-02-18 03:49:08');

-- --------------------------------------------------------

--
-- Table structure for table `master_departments`
--

CREATE TABLE `master_departments` (
  `id` int(11) NOT NULL,
  `dept_code` varchar(50) NOT NULL,
  `dept_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_departments`
--

INSERT INTO `master_departments` (`id`, `dept_code`, `dept_name`, `created_at`) VALUES
(1, 'CA', 'Cost Analyst', '2025-12-12 05:59:53'),
(2, 'PMWH', 'Project Management Wiring Harness', '2025-12-12 06:00:21'),
(3, 'PMA', 'Project Management Accessories', '2025-12-12 06:13:13'),
(4, 'PID', 'Product Innovation Development', '2025-12-12 06:19:28'),
(5, 'PMPEIS', 'Project Management Power & Energy Industrial Solution', '2025-12-12 06:40:15'),
(6, 'FA', 'Finance Accounting', '2025-12-16 01:46:37'),
(7, 'PNP', 'Purchasing & Procurement', '2025-12-16 01:47:49'),
(8, 'DH', 'Division Head', '2025-12-16 02:00:44');

-- --------------------------------------------------------

--
-- Table structure for table `master_gl`
--

CREATE TABLE `master_gl` (
  `id` int(11) NOT NULL,
  `kode_gl` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_io`
--

CREATE TABLE `master_io` (
  `id` int(11) NOT NULL,
  `io_number` varchar(50) NOT NULL,
  `project` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_io`
--

INSERT INTO `master_io` (`id`, `io_number`, `project`, `description`, `category`) VALUES
(2, '1501100938', 'P-2025-1', 'WIRING HARNESS 629D (INNOVA ZENIX)', 'Wiring Harness'),
(3, '1501100939', 'P-2025-2', 'WIRELESS CHARGER 629D', 'Automotive Electronics Part'),
(4, '1501100940', 'P-2025-2', 'tes', 'Automotive Electronics Part'),
(7, '1501101277', 'P-2026-1', 'Silver Box - KY FL', 'AEP'),
(9, '1502100514', 'P-2026-2', 'WIRING HARNESS MLLF, MLHH, 37400-30L00, MLPH, K0WW', 'WH');

-- --------------------------------------------------------

--
-- Table structure for table `master_items`
--

CREATE TABLE `master_items` (
  `id` int(11) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_items`
--

INSERT INTO `master_items` (`id`, `item_code`, `item_name`, `category`, `created_at`) VALUES
(1, 'ITEM001', 'MESIN CHECKER', NULL, '2025-12-15 16:52:42');

-- --------------------------------------------------------

--
-- Table structure for table `master_plants`
--

CREATE TABLE `master_plants` (
  `id` int(11) NOT NULL,
  `plant_code` varchar(50) NOT NULL,
  `plant_name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_plants`
--

INSERT INTO `master_plants` (`id`, `plant_code`, `plant_name`, `location`, `created_at`) VALUES
(1, '1501', 'Cikarang', 'Cikarang, Jawa Barat', '2025-12-12 06:25:02'),
(2, '1502', 'Cirebon', 'Cirebon, Jawa Barat', '2025-12-12 06:40:36');

-- --------------------------------------------------------

--
-- Table structure for table `master_roles`
--

CREATE TABLE `master_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_storage_locations`
--

CREATE TABLE `master_storage_locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sloc` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_storage_locations`
--

INSERT INTO `master_storage_locations` (`id`, `sloc`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, '1110', 'WH RM INDUK CKR', 'Active', NULL, NULL),
(2, '1513', 'WH ENG PRJCT CKR', 'Active', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `master_suppliers`
--

CREATE TABLE `master_suppliers` (
  `id` int(11) NOT NULL,
  `supplier_code` varchar(50) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_vendors`
--

CREATE TABLE `master_vendors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vendor_code` varchar(255) NOT NULL,
  `vendor_name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `terms_of_payment` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
(1, '2025_12_15_142956_add_missing_budget_plan_columns', 1),
(2, '2025_12_15_150152_change_budget_items_cc_id_to_string', 2),
(3, '2025_12_16_000000_create_pr_workflow_history_table', 3),
(4, '2025_12_16_000001_add_approval_workflow_columns', 4),
(5, '2025_12_16_000002_increase_role_column_length', 5),
(6, '2025_12_16_100000_create_master_assets_table', 6),
(7, '2025_12_16_101000_add_gl_sloc_duedate_to_pr', 7),
(8, '2025_12_16_103100_create_master_storage_locations_table', 8),
(9, '2025_12_16_063217_add_purpose_to_purchase_requests_table', 9),
(10, '2025_12_16_071707_add_last_notification_read_at_to_users_table', 10),
(11, '2026_02_16_083851_make_description_nullable_in_master_io_table', 11),
(12, '2026_02_16_085803_add_item_code_to_budget_items_table', 12),
(13, '2026_02_16_091120_add_category_to_budget_items_table', 13),
(14, '2026_02_18_023559_modify_status_in_budget_plans_table', 14),
(15, '2026_02_18_025804_add_columns_to_budget_tables', 15),
(16, '2026_02_18_103735_add_submitted_at_to_budget_plans_table', 16),
(17, '2026_02_18_105710_make_dates_nullable_in_projects_table', 17),
(18, '2026_02_18_114801_add_parent_item_id_to_budget_items_table', 18),
(19, '2026_02_18_151056_create_notifications_table', 19),
(20, '2026_02_18_151502_add_model_to_budget_items_table', 20),
(21, '2026_02_18_154126_move_model_column_to_budget_plans_table', 21),
(22, '2026_02_19_211208_create_role_permissions_table', 22),
(23, '2026_02_19_221806_create_user_customers_table', 23),
(24, '2026_02_20_105537_make_budget_item_id_nullable_in_purchase_requests', 24),
(25, '2026_02_20_114338_add_excel_fields_to_purchase_requests_table', 25),
(26, '2026_02_20_151200_add_import_mapping_fields_to_purchase_requests', 26),
(27, '2026_02_21_231014_create_master_vendors_table', 27),
(28, '2026_02_21_231014_create_purchase_orders_table', 28),
(29, '2026_02_21_231015_create_po_items_table', 28),
(30, '2026_02_25_105504_add_analysis_permission_to_roles', 28),
(31, '2026_02_25_140901_create_budget_transfers_table', 28);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `project_code` varchar(50) NOT NULL,
  `project_name` varchar(200) NOT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `budget_allocation` decimal(15,2) DEFAULT 0.00,
  `model` varchar(255) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `pic_user_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `die_go` date DEFAULT NULL,
  `to` date DEFAULT NULL,
  `pp1` date DEFAULT NULL,
  `pp2` date DEFAULT NULL,
  `pp3` date DEFAULT NULL,
  `mass_pro` date DEFAULT NULL,
  `status` enum('Active','Hold','Completed','Cancelled') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `project_code`, `project_name`, `customer`, `category`, `budget_allocation`, `model`, `year`, `description`, `pic_user_id`, `start_date`, `end_date`, `die_go`, `to`, `pp1`, `pp2`, `pp3`, `mass_pro`, `status`, `created_at`) VALUES
(3, 'P-2025-2', 'WIRELESS CHARGER 629D', 'TAM', 'Automotive Electronics Part', 0.00, '629D', 2025, NULL, 9, '2025-12-16', '2026-07-01', '2025-12-16', NULL, NULL, NULL, NULL, '2026-02-01', 'Active', '2025-12-12 11:51:45'),
(6, 'P-2026-1', 'SILVER BOX - KY FL', 'KIA', 'Automotive Electronics Part', 0.00, 'QY, SP2/SP3, KY', 2026, NULL, NULL, '2026-01-01', '2026-12-31', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', '2026-02-16 08:34:34'),
(7, 'P-2026-2', 'WIRING HARNESS MLLF, MLHH, 37400-30L00, MLPH, K0WW', 'TDII', 'Wiring Harness', 0.00, 'MLLF, MLHH, 37400-30L00, MLPH, K0WW', 2026, NULL, 9, '2026-01-30', '2026-12-31', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', '2026-02-18 03:58:34');

-- --------------------------------------------------------

--
-- Table structure for table `project_milestones`
--

CREATE TABLE `project_milestones` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `event_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pr_workflow_history`
--

CREATE TABLE `pr_workflow_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pr_number` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `actor_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pr_workflow_history`
--

INSERT INTO `pr_workflow_history` (`id`, `pr_number`, `action`, `notes`, `actor_id`, `created_at`, `updated_at`) VALUES
(8, 'PR/2025/12/001', 'Created', 'PR Created by PMWH (User).', 9, '2025-12-15 22:47:50', '2025-12-15 22:47:50'),
(9, 'PR/2025/12/001', 'Approved by Dept Head', 'Approved by Dept Head (Dept Head). Forwarded to: Finance', 12, '2025-12-15 22:53:21', '2025-12-15 22:53:21'),
(10, 'PR/2025/12/001', 'Approved by Finance', 'Approved by FA (Finance). Forwarded to: Division Head', 10, '2025-12-15 22:54:12', '2025-12-15 22:54:12'),
(11, 'PR/2025/12/001', 'Approved by Division Head', 'Approved by Division Head (Division Head). Forwarded to: Purchasing', 13, '2025-12-15 22:55:49', '2025-12-15 22:55:49'),
(12, 'PR/2025/12/001', 'Final Approval', 'Approved by PNP (Purchasing). PR is now fully approved.', 8, '2025-12-15 22:56:17', '2025-12-15 22:56:17'),
(13, 'PR/2025/12/002', 'Created', 'PR Created by PMWH (User).', 9, '2025-12-15 23:06:45', '2025-12-15 23:06:45'),
(14, 'PR/2025/12/002', 'Approved by Dept Head', 'Approved by Dept Head (Dept Head). Forwarded to: Finance', 12, '2025-12-15 23:07:26', '2025-12-15 23:07:26'),
(15, 'PR/2025/12/002', 'Edited', 'PR details updated by Tes1 (Super Admin).', 6, '2025-12-15 23:45:50', '2025-12-15 23:45:50'),
(16, 'PR/2025/12/001', 'Created', 'PR Created by Tes1 (Super Admin).', 6, '2025-12-16 01:49:12', '2025-12-16 01:49:12'),
(17, 'PR/2025/12/001', 'Edited', 'PR details updated by Tes1 (Super Admin).', 6, '2025-12-16 01:49:47', '2025-12-16 01:49:47'),
(18, 'PR/2025/12/001', 'Approved by Dept Head', 'Approved by Dept Head (Dept Head). Forwarded to: Finance', 12, '2025-12-16 01:51:05', '2025-12-16 01:51:05'),
(19, 'PR/2025/12/001', 'Edited', 'PR details updated by Tes1 (Super Admin).', 6, '2025-12-16 02:02:21', '2025-12-16 02:02:21'),
(36, '5920000939', 'Approved by Dept Head', 'Approved by Tes1 (Super Admin). Forwarded to: Finance', 6, '2026-02-23 01:49:39', '2026-02-23 01:49:39'),
(37, '5920000939', 'Approved by Finance', 'Approved by Tes1 (Super Admin). Forwarded to: Division Head', 6, '2026-02-23 01:49:43', '2026-02-23 01:49:43'),
(38, '5920000939', 'Approved by Division Head', 'Approved by Tes1 (Super Admin). Forwarded to: Purchasing', 6, '2026-02-23 01:49:45', '2026-02-23 01:49:45'),
(39, '5920000939', 'Final Approval', 'Approved by Tes1 (Super Admin). PR is now fully approved.', 6, '2026-02-23 01:49:50', '2026-02-23 01:49:50'),
(40, '5910001770', 'Approved by Dept Head', 'Approved by Tes1 (Super Admin). Forwarded to: Finance', 6, '2026-02-23 06:14:20', '2026-02-23 06:14:20'),
(41, '5910001770', 'Approved by Finance', 'Approved by Tes1 (Super Admin). Forwarded to: Division Head', 6, '2026-02-23 06:14:20', '2026-02-23 06:14:20'),
(42, '5910001770', 'Approved by Division Head', 'Approved by Tes1 (Super Admin). Forwarded to: Purchasing', 6, '2026-02-23 06:14:23', '2026-02-23 06:14:23'),
(43, '5910001770', 'Final Approval', 'Approved by Tes1 (Super Admin). PR is now fully approved.', 6, '2026-02-23 06:14:27', '2026-02-23 06:14:27'),
(44, '5910001772', 'Approved by Dept Head', 'Approved by Tes1 (Super Admin). Forwarded to: Finance', 6, '2026-02-23 06:17:49', '2026-02-23 06:17:49'),
(45, '5910001772', 'Approved by Finance', 'Approved by Tes1 (Super Admin). Forwarded to: Division Head', 6, '2026-02-23 06:17:50', '2026-02-23 06:17:50'),
(46, '5910001772', 'Approved by Division Head', 'Approved by Tes1 (Super Admin). Forwarded to: Purchasing', 6, '2026-02-23 06:17:52', '2026-02-23 06:17:52'),
(47, '5910001772', 'Final Approval', 'Approved by Tes1 (Super Admin). PR is now fully approved.', 6, '2026-02-23 06:17:55', '2026-02-23 06:17:55');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `po_number` varchar(255) NOT NULL,
  `po_date` date NOT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Draft',
  `notes` text DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `current_approver_role` varchar(255) DEFAULT NULL,
  `plant` varchar(255) DEFAULT NULL,
  `payment_terms` varchar(255) DEFAULT NULL,
  `delivery_terms` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` int(11) NOT NULL,
  `pr_number` varchar(50) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `business_category` varchar(100) DEFAULT NULL,
  `periode` varchar(50) DEFAULT NULL,
  `io_number` varchar(100) DEFAULT NULL,
  `cost_center` varchar(100) DEFAULT NULL,
  `budget_item_id` int(11) DEFAULT NULL,
  `budget_link` varchar(255) DEFAULT NULL,
  `item_code` varchar(100) DEFAULT NULL,
  `request_date` date NOT NULL,
  `requester_id` int(11) NOT NULL,
  `qty_req` int(11) NOT NULL DEFAULT 1,
  `uom` varchar(50) DEFAULT NULL,
  `estimated_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(15,2) DEFAULT NULL,
  `status` enum('Submitted','Approved','Rejected','On Process','Closed') NOT NULL DEFAULT 'Submitted',
  `current_approver_role` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `asset_no` varchar(50) DEFAULT NULL,
  `gl_account` varchar(50) DEFAULT NULL,
  `storage_location` varchar(50) DEFAULT NULL,
  `plant` varchar(100) DEFAULT NULL,
  `pic` varchar(255) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `dept_head_id` bigint(20) UNSIGNED DEFAULT NULL,
  `dept_head_approved_at` timestamp NULL DEFAULT NULL,
  `finance_id` bigint(20) UNSIGNED DEFAULT NULL,
  `finance_approved_at` timestamp NULL DEFAULT NULL,
  `div_head_id` bigint(20) UNSIGNED DEFAULT NULL,
  `div_head_approved_at` timestamp NULL DEFAULT NULL,
  `purchasing_id` bigint(20) UNSIGNED DEFAULT NULL,
  `purchasing_executed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_requests`
--

INSERT INTO `purchase_requests` (`id`, `pr_number`, `department`, `business_category`, `periode`, `io_number`, `cost_center`, `budget_item_id`, `budget_link`, `item_code`, `request_date`, `requester_id`, `qty_req`, `uom`, `estimated_price`, `total_price`, `status`, `current_approver_role`, `notes`, `purpose`, `asset_no`, `gl_account`, `storage_location`, `plant`, `pic`, `due_date`, `created_at`, `dept_head_id`, `dept_head_approved_at`, `finance_id`, `finance_approved_at`, `div_head_id`, `div_head_approved_at`, `purchasing_id`, `purchasing_executed_at`) VALUES
(22, '5920000939', 'Project Management Accessories', 'Automotive Electronics Part', '2026', '1501101277', '1501101000', 98, '1501101277 - Poin B (Silver Box Assembly Car)', NULL, '2026-02-16', 6, 1, 'PCE', 1500000.00, 1500000.00, 'Approved', NULL, 'Bosch GSB 120Li Gen 3 Assembly KY', 'Instalasi di mobil', NULL, NULL, '1515', '1512', 'ALAM W', NULL, '2026-02-23 01:30:28', 6, '2026-02-23 01:49:39', 6, '2026-02-23 01:49:43', 6, '2026-02-23 01:49:45', 6, '2026-02-23 01:49:50'),
(23, '5910001770', 'Project Management Accessories', 'Automotive Electronics Part', '2026', '1501101277', '1501101000', 101, '1501101277 - Poin D (Mechanical Electrical Supporting)', '9100-06058', '2026-02-23', 6, 2, 'PCE', 170000.00, 340000.00, 'Approved', NULL, 'ESD SMOCK GD', 'ESD Equipment', NULL, '62180899', '1512', '1501', 'Alam', NULL, '2026-02-23 06:13:27', 6, '2026-02-23 06:14:20', 6, '2026-02-23 06:14:20', 6, '2026-02-23 06:14:23', 6, '2026-02-23 06:14:27'),
(24, '5910001770', 'Project Management Accessories', 'Automotive Electronics Part', '2026', '1501101277', '1501101000', 101, '1501101277 - Poin D (Mechanical Electrical Supporting)', '9100-06053', '2026-02-23', 6, 2, 'PCE', 20000.00, 40000.00, 'Approved', NULL, 'ESD WRIST STRAP', 'ESD Equipment', NULL, '62180899', '1512', '1501', 'Alam', NULL, '2026-02-23 06:13:27', 6, '2026-02-23 06:14:20', 6, '2026-02-23 06:14:20', 6, '2026-02-23 06:14:23', 6, '2026-02-23 06:14:27'),
(25, '5910001770', 'Project Management Accessories', 'Automotive Electronics Part', '2026', '1501101277', '1501101000', 101, '1501101277 - Poin D (Mechanical Electrical Supporting)', '9100-06059', '2026-02-23', 6, 2, 'PCE', 110000.00, 220000.00, 'Approved', NULL, 'ESD SANDALS', 'ESD Equipment', NULL, '62180899', '1512', '1501', 'Alam', NULL, '2026-02-23 06:13:27', 6, '2026-02-23 06:14:20', 6, '2026-02-23 06:14:20', 6, '2026-02-23 06:14:23', 6, '2026-02-23 06:14:27'),
(26, '5910001770', 'Project Management Accessories', 'Automotive Electronics Part', '2026', '1501101277', '1501101000', 101, '1501101277 - Poin D (Mechanical Electrical Supporting)', '9100-00871', '2026-02-23', 6, 2, 'PCE', 65000.00, 130000.00, 'Approved', NULL, 'ESD CAP', 'ESD Equipment', NULL, '62180899', '1512', '1501', 'Alam', NULL, '2026-02-23 06:13:27', 6, '2026-02-23 06:14:20', 6, '2026-02-23 06:14:20', 6, '2026-02-23 06:14:23', 6, '2026-02-23 06:14:27'),
(36, '5910001772', 'Project Management Accessories', 'Automotive Electronics Part', '2026', '1501101277', '1501101000', 98, '1501101277 - Poin B (Silver Box Assembly Car)', '5500-23018', '2026-02-23', 6, 1, 'PCE', 70000.00, 70000.00, 'Approved', NULL, 'SENTER LAMPU KEPALA 15 W LED', 'SENTER LAMPU KEPALA 15 W LED', NULL, NULL, '1512', '1501', 'Alam', NULL, '2026-02-23 06:17:43', 6, '2026-02-23 06:17:48', 6, '2026-02-23 06:17:50', 6, '2026-02-23 06:17:52', 6, '2026-02-23 06:17:55'),
(37, '5910001772', 'Project Management Accessories', 'Automotive Electronics Part', '2026', '1501101277', '1501101000', 98, '1501101277 - Poin B (Silver Box Assembly Car)', '5500-02114', '2026-02-23', 6, 2, 'Set', 280000.00, 560000.00, 'Approved', NULL, 'HANDY REMOVER KTC', 'HANDY REMOVER KTC', NULL, NULL, '1512', '1501', 'Alam', NULL, '2026-02-23 06:17:43', 6, '2026-02-23 06:17:48', 6, '2026-02-23 06:17:50', 6, '2026-02-23 06:17:52', 6, '2026-02-23 06:17:55'),
(38, '5910001772', 'Project Management Accessories', 'Automotive Electronics Part', '2026', '1501101277', '1501101000', 98, '1501101277 - Poin B (Silver Box Assembly Car)', '5500-23297', '2026-02-23', 6, 1, 'PCE', 250000.00, 250000.00, 'Approved', NULL, 'Toolbox Besar 20 Inch', 'Toolbox Besar 20 Inch', NULL, NULL, '1512', '1501', 'Alam', NULL, '2026-02-23 06:17:43', 6, '2026-02-23 06:17:48', 6, '2026-02-23 06:17:50', 6, '2026-02-23 06:17:52', 6, '2026-02-23 06:17:55'),
(39, '5910001772', 'Project Management Accessories', 'Automotive Electronics Part', '2026', '1501101277', '1501101000', 98, '1501101277 - Poin B (Silver Box Assembly Car)', '5500-23298', '2026-02-23', 6, 1, 'Set', 75000.00, 75000.00, 'Approved', NULL, 'Ratchet Screwdriver', 'Ratchet Screwdriver', NULL, NULL, '1512', '1501', 'Alam', NULL, '2026-02-23 06:17:43', 6, '2026-02-23 06:17:48', 6, '2026-02-23 06:17:50', 6, '2026-02-23 06:17:52', 6, '2026-02-23 06:17:55'),
(40, '5910001772', 'Project Management Accessories', 'Automotive Electronics Part', '2026', '1501101277', '1501101000', 98, '1501101277 - Poin B (Silver Box Assembly Car)', '5500-22861', '2026-02-23', 6, 1, 'PCE', 30000.00, 30000.00, 'Approved', NULL, 'GUNTING BISON LD-2', 'GUNTING BISON LD-2', NULL, NULL, '1512', '1501', 'Alam', NULL, '2026-02-23 06:17:43', 6, '2026-02-23 06:17:48', 6, '2026-02-23 06:17:50', 6, '2026-02-23 06:17:52', 6, '2026-02-23 06:17:55'),
(41, '5910001772', 'Project Management Accessories', 'Automotive Electronics Part', '2026', '1501101277', '1501101000', 98, '1501101277 - Poin B (Silver Box Assembly Car)', '9100-00167', '2026-02-23', 6, 1, 'PCE', 25000.00, 25000.00, 'Approved', NULL, 'CUTTER BESAR', 'CUTTER BESAR', NULL, NULL, '1512', '1501', 'Alam', NULL, '2026-02-23 06:17:43', 6, '2026-02-23 06:17:48', 6, '2026-02-23 06:17:50', 6, '2026-02-23 06:17:52', 6, '2026-02-23 06:17:55'),
(42, '5910001772', 'Project Management Accessories', 'Automotive Electronics Part', '2026', '1501101277', '1501101000', 98, '1501101277 - Poin B (Silver Box Assembly Car)', '5500-23299', '2026-02-23', 6, 1, 'PCE', 600000.00, 600000.00, 'Approved', NULL, 'Hioki 3244-60', 'Hioki 3244-60', NULL, NULL, '1512', '1501', 'Alam', NULL, '2026-02-23 06:17:43', 6, '2026-02-23 06:17:49', 6, '2026-02-23 06:17:50', 6, '2026-02-23 06:17:52', 6, '2026-02-23 06:17:55'),
(43, '5910001772', 'Project Management Accessories', 'Automotive Electronics Part', '2026', '1501101277', '1501101000', 98, '1501101277 - Poin B (Silver Box Assembly Car)', '5500-23170', '2026-02-23', 6, 1, 'Set', 50000.00, 50000.00, 'Approved', NULL, 'BIT SCREWDRIVER PHILIPS SET', 'BIT SCREWDRIVER PHILIPS SET', NULL, NULL, '1512', '1501', 'Alam', NULL, '2026-02-23 06:17:43', 6, '2026-02-23 06:17:49', 6, '2026-02-23 06:17:50', 6, '2026-02-23 06:17:52', 6, '2026-02-23 06:17:55'),
(44, '5910001772', 'Project Management Accessories', 'Automotive Electronics Part', '2026', '1501101277', '1501101000', 98, '1501101277 - Poin B (Silver Box Assembly Car)', '5500-23484', '2026-02-23', 6, 1, 'PCE', 50000.00, 50000.00, 'Approved', NULL, 'RACHET RING PASS 10 MM', 'RACHET RING PASS 10 MM', NULL, NULL, '1512', '1501', 'Alam', NULL, '2026-02-23 06:17:43', 6, '2026-02-23 06:17:49', 6, '2026-02-23 06:17:50', 6, '2026-02-23 06:17:52', 6, '2026-02-23 06:17:55');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(255) NOT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', '[\"Full Access\",\"Manage Users\",\"Manage Master Data\",\"Approve All\"]', '2026-02-19 14:16:12', '2026-02-19 14:16:12'),
(2, 'Admin', '[\"Approve Budget\",\"Manage Master Data (Partial)\",\"View All Reports\",\"Menu: Dashboard\",\"Menu: Budget Plan\",\"Menu: Purchase Request\",\"Menu: Purchase Order\",\"Menu: Projects\"]', '2026-02-19 14:16:12', '2026-02-22 05:37:52'),
(3, 'Dept Head', '[\"Approve PR (Dept Level)\",\"Create Budget Plan\",\"View Own Status\",\"Menu: Dashboard\",\"Menu: Budget Plan\",\"Menu: Purchase Request\",\"Menu: Purchase Order\",\"Menu: Projects\"]', '2026-02-19 14:16:12', '2026-02-22 05:38:35'),
(4, 'Division Head', '[\"Approve PR (Div Level)\",\"View Division Reports\",\"View Own Status\",\"Menu: Dashboard\",\"Menu: Budget Plan\",\"Menu: Purchase Request\",\"Menu: Purchase Order\",\"Menu: Projects\"]', '2026-02-19 14:16:12', '2026-02-22 05:38:47'),
(5, 'Finance', '[\"Approve Payments\",\"View Financial Reports\",\"Menu: Dashboard\",\"Menu: Budget Plan\",\"Menu: Purchase Request\",\"Menu: Purchase Order\",\"Menu: Projects\"]', '2026-02-19 14:16:12', '2026-02-22 05:38:08'),
(6, 'Purchasing', '[\"Process PR\",\"Manage Suppliers\",\"View Own Status\",\"Menu: Dashboard\",\"Menu: Budget Plan\",\"Menu: Purchase Request\",\"Menu: Purchase Order\",\"Menu: Projects\"]', '2026-02-19 14:16:12', '2026-02-22 05:38:21'),
(7, 'User', '[\"Create PR\",\"View Own Status\",\"Menu: Dashboard\",\"Menu: Budget Plan\",\"Menu: Purchase Request\",\"Menu: Projects\"]', '2026-02-19 14:16:12', '2026-02-22 05:37:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL,
  `last_notification_read_at` timestamp NULL DEFAULT NULL,
  `department` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `last_notification_read_at`, `department`, `email`, `created_at`) VALUES
(5, 'Tes', '$2y$10$iju/xqfAoAchFCanhgrwc..8xcAH4xtptZnKgFSiVGTpNU1r8.0AK', 'Tes', 'Super Admin', NULL, 'IT', NULL, '2025-12-11 11:35:58'),
(6, 'Tes1', '$2y$10$mtJvOpXW68jYLiVepcWEkO85l3WLXB07nO64IBy14uagrn3F4QtZ6', 'Tes1', 'Super Admin', '2026-02-16 01:35:56', 'IT', NULL, '2025-12-12 02:53:28'),
(7, 'CA01', '$2y$12$f/R5uOVF8NVWE81f3OY3.OGu9rd9GOCKLKgW13R5SA6n6sKvPyjOS', 'CA', 'Admin', NULL, 'Cost Analyst', NULL, '2025-12-16 01:46:11'),
(8, 'PNP01', '$2y$12$qrxdhefdgtuJk/4SjPBKQevsK1MpqlXDtPNW6gcHpYRolDVZltv2i', 'PNP', 'Purchasing', '2026-02-21 08:50:51', 'Finance Accounting', NULL, '2025-12-16 01:50:32'),
(9, 'PMWH01', '$2y$12$.JRc9y/tz5qOV0qCs7WYuOeW3gMOqt7fs7M2YHN0wuejyCdEUgQkG', 'PMWH', 'User', NULL, 'Project Management Wiring Harness', NULL, '2025-12-16 01:56:05'),
(10, 'FA01', '$2y$12$sxvytX8HgIrrEhGIyDkGj.Unxs80s/Mr9iD1.MwoeSojYymrFKuV6', 'FA', 'Finance', NULL, 'Finance Accounting', NULL, '2025-12-16 01:59:38'),
(12, 'Depthead01', '$2y$12$/koNhv4yBn4PfJDlFxEUj.hVLChYYvomM0h5FjwRXiUkiy4YHAWXa', 'Dept Head', 'Dept Head', NULL, 'Project Management Accessories', NULL, '2025-12-16 02:11:18'),
(13, 'Divhead01', '$2y$12$Ia9aPAc2nM.4nlofROPRweDO133o/mdUbioSMS2LxRSyxJFPQ0Y3m', 'Division Head', 'Division Head', NULL, 'Division Head', NULL, '2025-12-16 02:12:03'),
(14, 'PMA01', '$2y$12$SEchXbQ0OzRuh6.ba5wxP.cohjC9YMbnh4xIn4Y.DKm5UoNrgEuyy', 'PMA01', 'User', NULL, 'Project Management Accessories', NULL, '2026-02-19 15:43:34');

-- --------------------------------------------------------

--
-- Table structure for table `user_customers`
--

CREATE TABLE `user_customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `master_customer_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_customers`
--

INSERT INTO `user_customers` (`id`, `user_id`, `master_customer_id`, `created_at`, `updated_at`) VALUES
(1, 9, 4, NULL, NULL),
(2, 14, 3, NULL, NULL),
(3, 14, 2, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budget_items`
--
ALTER TABLE `budget_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `io_id` (`io_id`),
  ADD KEY `cc_id` (`cc_id`),
  ADD KEY `budget_items_parent_item_id_foreign` (`parent_item_id`);

--
-- Indexes for table `budget_plans`
--
ALTER TABLE `budget_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `budget_transfers`
--
ALTER TABLE `budget_transfers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_assets`
--
ALTER TABLE `master_assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `master_assets_asset_no_unique` (`asset_no`);

--
-- Indexes for table `master_categories`
--
ALTER TABLE `master_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_cost_center`
--
ALTER TABLE `master_cost_center`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cc_code` (`cc_code`);

--
-- Indexes for table `master_currencies`
--
ALTER TABLE `master_currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `currency_code` (`currency_code`);

--
-- Indexes for table `master_customers`
--
ALTER TABLE `master_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_code` (`customer_code`);

--
-- Indexes for table `master_departments`
--
ALTER TABLE `master_departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dept_code` (`dept_code`);

--
-- Indexes for table `master_gl`
--
ALTER TABLE `master_gl`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_io`
--
ALTER TABLE `master_io`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `io_number` (`io_number`);

--
-- Indexes for table `master_items`
--
ALTER TABLE `master_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_code` (`item_code`);

--
-- Indexes for table `master_plants`
--
ALTER TABLE `master_plants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plant_code` (`plant_code`);

--
-- Indexes for table `master_roles`
--
ALTER TABLE `master_roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_storage_locations`
--
ALTER TABLE `master_storage_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `master_storage_locations_sloc_unique` (`sloc`);

--
-- Indexes for table `master_suppliers`
--
ALTER TABLE `master_suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplier_code` (`supplier_code`);

--
-- Indexes for table `master_vendors`
--
ALTER TABLE `master_vendors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `master_vendors_vendor_code_unique` (`vendor_code`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_code` (`project_code`),
  ADD KEY `pic_user_id` (`pic_user_id`);

--
-- Indexes for table `project_milestones`
--
ALTER TABLE `project_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `pr_workflow_history`
--
ALTER TABLE `pr_workflow_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_orders_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `budget_item_id` (`budget_item_id`),
  ADD KEY `requester_id` (`requester_id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permissions_role_unique` (`role`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_customers`
--
ALTER TABLE `user_customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_customers_user_id_foreign` (`user_id`),
  ADD KEY `user_customers_master_customer_id_foreign` (`master_customer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `budget_items`
--
ALTER TABLE `budget_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `budget_plans`
--
ALTER TABLE `budget_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `budget_transfers`
--
ALTER TABLE `budget_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_assets`
--
ALTER TABLE `master_assets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_categories`
--
ALTER TABLE `master_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `master_cost_center`
--
ALTER TABLE `master_cost_center`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `master_currencies`
--
ALTER TABLE `master_currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_customers`
--
ALTER TABLE `master_customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `master_departments`
--
ALTER TABLE `master_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `master_gl`
--
ALTER TABLE `master_gl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_io`
--
ALTER TABLE `master_io`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `master_items`
--
ALTER TABLE `master_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `master_plants`
--
ALTER TABLE `master_plants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `master_roles`
--
ALTER TABLE `master_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_storage_locations`
--
ALTER TABLE `master_storage_locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `master_suppliers`
--
ALTER TABLE `master_suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_vendors`
--
ALTER TABLE `master_vendors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `project_milestones`
--
ALTER TABLE `project_milestones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pr_workflow_history`
--
ALTER TABLE `pr_workflow_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_customers`
--
ALTER TABLE `user_customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budget_items`
--
ALTER TABLE `budget_items`
  ADD CONSTRAINT `budget_items_parent_item_id_foreign` FOREIGN KEY (`parent_item_id`) REFERENCES `budget_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_item_plan` FOREIGN KEY (`plan_id`) REFERENCES `budget_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `budget_plans`
--
ALTER TABLE `budget_plans`
  ADD CONSTRAINT `fk_budget_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_budget_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_project_pic` FOREIGN KEY (`pic_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_milestones`
--
ALTER TABLE `project_milestones`
  ADD CONSTRAINT `project_milestones_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `master_vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `fk_pr_item` FOREIGN KEY (`budget_item_id`) REFERENCES `budget_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pr_user` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_customers`
--
ALTER TABLE `user_customers`
  ADD CONSTRAINT `user_customers_master_customer_id_foreign` FOREIGN KEY (`master_customer_id`) REFERENCES `master_customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
