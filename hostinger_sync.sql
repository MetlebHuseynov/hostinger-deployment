-- =============================================
-- Hostinger Database Synchronization Script
-- Generated: 2025-08-30T20:50:50.349Z
-- =============================================

SET FOREIGN_KEY_CHECKS = 0;


-- Data for table categories
DELETE FROM categories;
INSERT INTO categories (id, name, description, image, created_at, updated_at) VALUES (1, 'Elektrik', 'Elektrik məhsulları', 'category-placeholder.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO categories (id, name, description, image, created_at, updated_at) VALUES (2, 'Mexaniki', 'Mexaniki məhsullar', 'category-placeholder.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO categories (id, name, description, image, created_at, updated_at) VALUES (3, 'Avtomatlaşdırma', 'Avtomatlaşdırma sistemləri', 'category-placeholder.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');

-- Data for table markas
DELETE FROM markas;
INSERT INTO markas (id, name, description, image, created_at, updated_at) VALUES (1, 'Siemens', 'Siemens məhsulları', 'siemens-logo.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO markas (id, name, description, image, created_at, updated_at) VALUES (2, 'ABB', 'ABB məhsulları', 'abb-logo.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO markas (id, name, description, image, created_at, updated_at) VALUES (3, 'Schneider', 'Schneider məhsulları', 'schneider-logo.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO markas (id, name, description, image, created_at, updated_at) VALUES (4, 'Bosch', 'Bosch məhsulları', 'bosch-logo.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO markas (id, name, description, image, created_at, updated_at) VALUES (5, 'Danfoss', 'Danfoss məhsulları', 'danfoss-logo.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO markas (id, name, description, image, created_at, updated_at) VALUES (6, 'Honeywell', 'Honeywell məhsulları', 'honeywell-logo.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');

-- Data for table products
DELETE FROM products;
INSERT INTO products (id, name, description, price, category_id, marka_id, image, stock, status, created_at, updated_at) VALUES (1, 'Siemens S7-1200 PLC', 'Kompakt avtomatlaşdırma sistemi', 450, 3, 1, 'product-placeholder.svg', 25, 'active', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO products (id, name, description, price, category_id, marka_id, image, stock, status, created_at, updated_at) VALUES (2, 'ABB Kontaktor', 'Yüksək keyfiyyətli elektrik kontaktoru', 85.5, 1, 2, 'product-placeholder.svg', 50, 'active', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO products (id, name, description, price, category_id, marka_id, image, stock, status, created_at, updated_at) VALUES (3, 'Schneider Çərçivə', 'Elektrik paneli üçün çərçivə', 125, 1, 3, 'product-placeholder.svg', 30, 'active', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO products (id, name, description, price, category_id, marka_id, image, stock, status, created_at, updated_at) VALUES (4, 'Bosch Sensor', 'Yüksək dəqiqlikli sensor', 75.25, 2, 4, 'product-placeholder.svg', 40, 'active', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO products (id, name, description, price, category_id, marka_id, image, stock, status, created_at, updated_at) VALUES (5, 'Danfoss Frekans Çeviricisi', 'Motor sürücü sistemi', 320, 3, 5, 'product-placeholder.svg', 15, 'active', '2025-08-30 20:27:19', '2025-08-30 20:27:19');

SET FOREIGN_KEY_CHECKS = 1;

-- Synchronization completed
