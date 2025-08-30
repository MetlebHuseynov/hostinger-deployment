<?php
// This script will help export data from Hostinger database
// Since we can't connect directly, we'll create SQL statements to populate local database

// Sample data structure based on what we know from the live site
$categories = [
    ['id' => 1, 'name' => 'Elektrik', 'description' => 'Elektrik məhsulları', 'image' => 'category-placeholder.svg'],
    ['id' => 2, 'name' => 'Mexaniki', 'description' => 'Mexaniki məhsullar', 'image' => 'category-placeholder.svg'],
    ['id' => 3, 'name' => 'Avtomatlaşdırma', 'description' => 'Avtomatlaşdırma sistemləri', 'image' => 'category-placeholder.svg']
];

$markas = [
    ['id' => 1, 'name' => 'Siemens', 'description' => 'Siemens məhsulları', 'image' => 'siemens-logo.svg'],
    ['id' => 2, 'name' => 'ABB', 'description' => 'ABB məhsulları', 'image' => 'abb-logo.svg'],
    ['id' => 3, 'name' => 'Schneider', 'description' => 'Schneider məhsulları', 'image' => 'schneider-logo.svg'],
    ['id' => 4, 'name' => 'Bosch', 'description' => 'Bosch məhsulları', 'image' => 'bosch-logo.svg'],
    ['id' => 5, 'name' => 'Danfoss', 'description' => 'Danfoss məhsulları', 'image' => 'danfoss-logo.svg'],
    ['id' => 6, 'name' => 'Honeywell', 'description' => 'Honeywell məhsulları', 'image' => 'honeywell-logo.svg']
];

$products = [
    [
        'id' => 1,
        'name' => 'Siemens S7-1200 PLC',
        'description' => 'Kompakt avtomatlaşdırma sistemi',
        'price' => 450.00,
        'category_id' => 3,
        'marka_id' => 1,
        'image' => 'product-placeholder.svg',
        'stock' => 25,
        'status' => 'active'
    ],
    [
        'id' => 2,
        'name' => 'ABB Kontaktor',
        'description' => 'Yüksək keyfiyyətli elektrik kontaktoru',
        'price' => 85.50,
        'category_id' => 1,
        'marka_id' => 2,
        'image' => 'product-placeholder.svg',
        'stock' => 50,
        'status' => 'active'
    ],
    [
        'id' => 3,
        'name' => 'Schneider Çərçivə',
        'description' => 'Elektrik paneli üçün çərçivə',
        'price' => 125.00,
        'category_id' => 1,
        'marka_id' => 3,
        'image' => 'product-placeholder.svg',
        'stock' => 30,
        'status' => 'active'
    ],
    [
        'id' => 4,
        'name' => 'Bosch Sensor',
        'description' => 'Yüksək dəqiqlikli sensor',
        'price' => 75.25,
        'category_id' => 2,
        'marka_id' => 4,
        'image' => 'product-placeholder.svg',
        'stock' => 40,
        'status' => 'active'
    ],
    [
        'id' => 5,
        'name' => 'Danfoss Frekans Çeviricisi',
        'description' => 'Motor sürücü sistemi',
        'price' => 320.00,
        'category_id' => 3,
        'marka_id' => 5,
        'image' => 'product-placeholder.svg',
        'stock' => 15,
        'status' => 'active'
    ]
];

echo "-- SQL statements to populate local database with Hostinger-like data\n\n";

// Categories
echo "-- Insert Categories\n";
foreach($categories as $cat) {
    echo "INSERT OR REPLACE INTO categories (id, name, description, image, created_at, updated_at) VALUES (";
    echo "{$cat['id']}, '{$cat['name']}', '{$cat['description']}', '{$cat['image']}', datetime('now'), datetime('now'));\n";
}

echo "\n-- Insert Markas\n";
foreach($markas as $marka) {
    echo "INSERT OR REPLACE INTO markas (id, name, description, image, created_at, updated_at) VALUES (";
    echo "{$marka['id']}, '{$marka['name']}', '{$marka['description']}', '{$marka['image']}', datetime('now'), datetime('now'));\n";
}

echo "\n-- Insert Products\n";
foreach($products as $product) {
    echo "INSERT OR REPLACE INTO products (id, name, description, price, category_id, marka_id, image, stock, status, created_at, updated_at) VALUES (";
    echo "{$product['id']}, '{$product['name']}', '{$product['description']}', {$product['price']}, {$product['category_id']}, {$product['marka_id']}, '{$product['image']}', {$product['stock']}, '{$product['status']}', datetime('now'), datetime('now'));\n";
}

echo "\n-- Update sequences\n";
echo "UPDATE sqlite_sequence SET seq = 5 WHERE name = 'categories';\n";
echo "UPDATE sqlite_sequence SET seq = 6 WHERE name = 'markas';\n";
echo "UPDATE sqlite_sequence SET seq = 5 WHERE name = 'products';\n";

echo "\n-- Data export completed!\n";
?>