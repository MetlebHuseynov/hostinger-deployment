-- =============================================
-- Hostinger Database Backup Template
-- Use this in phpMyAdmin to backup current data
-- =============================================

-- Backup Categories
SELECT 'Categories Backup:' as info;
SELECT * FROM categories;

-- Backup Markas
SELECT 'Markas Backup:' as info;
SELECT * FROM markas;

-- Backup Products
SELECT 'Products Backup:' as info;
SELECT p.*, c.name as category_name, m.name as marka_name 
FROM products p 
LEFT JOIN categories c ON p.category_id = c.id 
LEFT JOIN markas m ON p.marka_id = m.id;

-- Export as SQL (use phpMyAdmin Export feature)
-- 1. Go to phpMyAdmin
-- 2. Select your database
-- 3. Click Export tab
-- 4. Choose 'Custom' export method
-- 5. Select tables: categories, markas, products
-- 6. Choose SQL format
-- 7. Download the backup file
