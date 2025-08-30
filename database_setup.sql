-- ProLinege Database Setup
-- Bu SQL faylını Hostinger phpMyAdmin-də işə salın

-- Database yaradın (əgər yoxdursa)
CREATE DATABASE IF NOT EXISTS `u482576391_yUuh0` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `u482576391_yUuh0`;

-- Users cədvəli
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories cədvəli
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Markas (Brands) cədvəli
CREATE TABLE IF NOT EXISTS `markas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products cədvəli
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text,
  `short_description` varchar(500) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `marka_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `sku` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL, -- JSON format for multiple images
  `specifications` text DEFAULT NULL, -- JSON format for technical specs
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_category` (`category_id`),
  KEY `idx_marka` (`marka_id`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_status` (`status`),
  KEY `idx_sku` (`sku`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`marka_id`) REFERENCES `markas`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin user əlavə et (password: admin123)
-- Password hash for 'admin123' using PHP password_hash()
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`, `status`) VALUES
('admin', 'admin@prolinege.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 'active');

-- Alternative admin user with simpler password (password: 123456)
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`, `status`) VALUES
('prolinege', 'info@prolinege.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'ProLinege Admin', 'admin', 'active');

-- Sample categories
INSERT INTO `categories` (`name`, `description`, `is_featured`, `sort_order`, `status`) VALUES
('Polad Məhsullar', 'Müxtəlif polad məhsulları və konstruksiya materialları', 1, 1, 'active'),
('Alüminium Profillər', 'Alüminium profillər və aksesuarlar', 1, 2, 'active'),
('Qaynaq Materialları', 'Qaynaq elektrodları və digər qaynaq materialları', 1, 3, 'active'),
('Armatur', 'İnşaat armaturu və bağlayıcı elementlər', 0, 4, 'active');

-- Sample brands
INSERT INTO `markas` (`name`, `description`, `is_featured`, `sort_order`, `status`) VALUES
('Siemens', 'Alman texnologiya şirkəti', 1, 1, 'active'),
('ABB', 'İsveçrə-İsveç elektrik şirkəti', 1, 2, 'active'),
('Schneider Electric', 'Fransız enerji idarəetmə şirkəti', 1, 3, 'active'),
('Bosch', 'Alman mühəndislik və texnologiya şirkəti', 1, 4, 'active'),
('Danfoss', 'Danimarka enerji səmərəli həlləri', 0, 5, 'active'),
('Honeywell', 'Amerika texnologiya şirkəti', 0, 6, 'active');

-- Sample products
INSERT INTO `products` (`name`, `description`, `short_description`, `category_id`, `marka_id`, `price`, `stock_quantity`, `sku`, `is_featured`, `sort_order`, `status`) VALUES
('Polad Boru Ø20mm', 'Yüksək keyfiyyətli polad boru, inşaat və sənaye üçün', 'Ø20mm polad boru, 6m uzunluq', 1, 1, 25.50, 100, 'PB-20-6M', 1, 1, 'active'),
('Alüminium Profil 40x40', 'Kvadrat alüminium profil, çərçivə və konstruksiya üçün', '40x40mm alüminium profil', 2, 2, 15.75, 200, 'AP-40X40', 1, 2, 'active'),
('Qaynaq Elektrodu E6013', 'Universal qaynaq elektrodu, bütün vəziyyətlər üçün', 'E6013 elektrodu, 3.2mm', 3, 3, 8.90, 500, 'QE-6013-32', 1, 3, 'active'),
('İnşaat Armaturu Ø12mm', 'A500C sinif inşaat armaturu', 'Ø12mm armatur, 12m uzunluq', 4, 4, 18.25, 150, 'AR-12-12M', 0, 4, 'active');

-- Indexes və optimizasiya
OPTIMIZE TABLE `users`;
OPTIMIZE TABLE `categories`;
OPTIMIZE TABLE `markas`;
OPTIMIZE TABLE `products`;

-- Statistika
SELECT 'Database setup completed successfully!' as message;
SELECT 
    'users' as table_name, COUNT(*) as record_count FROM `users`
UNION ALL
SELECT 
    'categories' as table_name, COUNT(*) as record_count FROM `categories`
UNION ALL
SELECT 
    'markas' as table_name, COUNT(*) as record_count FROM `markas`
UNION ALL
SELECT 
    'products' as table_name, COUNT(*) as record_count FROM `products`;