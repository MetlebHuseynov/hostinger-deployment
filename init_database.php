<?php
try {
    $db = new PDO('sqlite:proline.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables
    $db->exec("CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        image TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS markas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        image TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        price DECIMAL(10,2) DEFAULT 0,
        category_id INTEGER,
        marka_id INTEGER,
        image TEXT,
        stock INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id),
        FOREIGN KEY (marka_id) REFERENCES markas(id)
    )");
    
    echo "Tables created successfully!<br>";
    
    // Load categories from JSON
    $categoriesJson = file_get_contents('database/categories.json');
    $categories = json_decode($categoriesJson, true);
    
    if ($categories) {
        $stmt = $db->prepare("INSERT OR REPLACE INTO categories (id, name, description, image, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($categories as $category) {
            $stmt->execute([
                $category['id'],
                $category['name'],
                $category['description'],
                $category['image'],
                $category['created_at'],
                $category['updated_at']
            ]);
        }
        echo "Categories loaded: " . count($categories) . "<br>";
    }
    
    // Create sample brands
    $sampleBrands = [
        ['name' => 'Siemens', 'description' => 'Alman sənaye şirkəti'],
        ['name' => 'ABB', 'description' => 'İsveçrə-İsveç elektrik şirkəti'],
        ['name' => 'Schneider Electric', 'description' => 'Fransız enerji idarəetmə şirkəti'],
        ['name' => 'Mitsubishi Electric', 'description' => 'Yapon elektrik şirkəti'],
        ['name' => 'Omron', 'description' => 'Yapon avtomatlaşdırma şirkəti']
    ];
    
    $stmt = $db->prepare("INSERT INTO markas (name, description) VALUES (?, ?)");
    foreach ($sampleBrands as $brand) {
        $stmt->execute([$brand['name'], $brand['description']]);
    }
    echo "Sample brands created: " . count($sampleBrands) . "<br>";
    
    // Create sample products
    $sampleProducts = [
        ['name' => 'Siemens S7-1200 PLC', 'description' => 'Kompakt PLC sistemi', 'price' => 450.00, 'category_id' => 3, 'marka_id' => 1, 'stock' => 15],
        ['name' => 'ABB Kontaktor', 'description' => 'Elektrik kontaktoru', 'price' => 85.50, 'category_id' => 1, 'marka_id' => 2, 'stock' => 25],
        ['name' => 'Schneider Electric Rele', 'description' => 'Təhlükəsizlik reləsi', 'price' => 120.00, 'category_id' => 1, 'marka_id' => 3, 'stock' => 30],
        ['name' => 'Mitsubishi Servo Motor', 'description' => 'Yüksək dəqiqlikli servo motor', 'price' => 680.00, 'category_id' => 2, 'marka_id' => 4, 'stock' => 8],
        ['name' => 'Omron Sensor', 'description' => 'Fotoelektrik sensor', 'price' => 95.00, 'category_id' => 3, 'marka_id' => 5, 'stock' => 40]
    ];
    
    $stmt = $db->prepare("INSERT INTO products (name, description, price, category_id, marka_id, stock) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($sampleProducts as $product) {
        $stmt->execute([
            $product['name'],
            $product['description'],
            $product['price'],
            $product['category_id'],
            $product['marka_id'],
            $product['stock']
        ]);
    }
    echo "Sample products created: " . count($sampleProducts) . "<br>";
    
    echo "<br>Database initialized successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>