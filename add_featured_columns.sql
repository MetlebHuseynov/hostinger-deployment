-- Add featured column to products table if it doesn't exist
ALTER TABLE products ADD COLUMN featured TINYINT(1) DEFAULT 0;

-- Add featured column to categories table if it doesn't exist
ALTER TABLE categories ADD COLUMN featured TINYINT(1) DEFAULT 0;

-- Add featured column to markas table if it doesn't exist
ALTER TABLE markas ADD COLUMN featured TINYINT(1) DEFAULT 0;

-- Update some products to be featured
UPDATE products SET featured = 1 WHERE id IN (1, 2, 3, 4) OR name LIKE '%Çelik%' OR name LIKE '%İnşaat%' OR name LIKE '%Alüminyum%' OR name LIKE '%Kaynak%';

-- Update some categories to be featured
UPDATE categories SET featured = 1 WHERE id IN (1, 2, 3, 4) OR name LIKE '%Çelik%' OR name LIKE '%İnşaat%' OR name LIKE '%Elektrik%' OR name LIKE '%Kaynak%';

-- Update some brands to be featured
UPDATE markas SET featured = 1 WHERE id IN (1, 2, 3, 4, 5, 6) OR name IN ('Bosch', 'Siemens', 'Schneider Electric', 'ABB', 'Danfoss', 'Honeywell');

-- Verify the changes
SELECT 'Products with featured=1:' as info, COUNT(*) as count FROM products WHERE featured = 1;
SELECT 'Categories with featured=1:' as info, COUNT(*) as count FROM categories WHERE featured = 1;
SELECT 'Markas with featured=1:' as info, COUNT(*) as count FROM markas WHERE featured = 1;

-- Show some featured items
SELECT 'Featured Products:' as info;
SELECT id, name, featured FROM products WHERE featured = 1 LIMIT 5;

SELECT 'Featured Categories:' as info;
SELECT id, name, featured FROM categories WHERE featured = 1 LIMIT 5;

SELECT 'Featured Markas:' as info;
SELECT id, name, featured FROM markas WHERE featured = 1 LIMIT 5;