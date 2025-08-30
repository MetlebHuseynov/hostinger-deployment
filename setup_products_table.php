<?php
// Setup products table and add sample data
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Products Cədvəli Setup</h2>";

try {
    $db = getDBConnection();
    echo "✅ Database bağlantısı uğurlu<br>";
    
    // Create products table
    echo "<h3>Products cədvəli yaradılır:</h3>";
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            category VARCHAR(100),
            marka VARCHAR(100),
            image VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    
    if ($db->exec($createTableSQL) !== false) {
        echo "✅ Products cədvəli yaradıldı!<br>";
    } else {
        echo "❌ Products cədvəli yaradıla bilmədi!<br>";
    }
    
    // Check if products already exist
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM products");
    $stmt->execute();
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        echo "<h3>Nümunə məhsullar əlavə edilir:</h3>";
        
        $sampleProducts = [
            [
                'name' => 'Polad Boru 20mm',
                'description' => 'Yüksək keyfiyyətli polad boru, tikinti işləri üçün',
                'price' => 15.50,
                'category' => 'Polad Məhsullar',
                'marka' => 'ProSteel',
                'image' => 'images/steel-pipe.svg'
            ],
            [
                'name' => 'Alüminium Profil 30x30',
                'description' => 'Alüminium profil, pəncərə və qapı üçün',
                'price' => 25.00,
                'category' => 'Alüminium Məhsullar',
                'marka' => 'AluTech',
                'image' => 'images/aluminum-profile.svg'
            ],
            [
                'name' => 'Qaynaq Elektrodu E6013',
                'description' => 'Universal qaynaq elektrodu, bütün növ metallar üçün',
                'price' => 8.75,
                'category' => 'Qaynaq Materialları',
                'marka' => 'WeldPro',
                'image' => 'images/welding-electrode.svg'
            ],
            [
                'name' => 'Armatür 12mm',
                'description' => 'Beton armaturu, tikinti işləri üçün',
                'price' => 12.30,
                'category' => 'Tikinti Materialları',
                'marka' => 'BuildSteel',
                'image' => 'images/rebar.svg'
            ],
            [
                'name' => 'Polad Lövhə 2mm',
                'description' => 'Yüksək keyfiyyətli polad lövhə',
                'price' => 45.00,
                'category' => 'Polad Məhsullar',
                'marka' => 'ProSteel',
                'image' => 'images/product-placeholder.svg'
            ]
        ];
        
        $insertSQL = "INSERT INTO products (name, description, price, category, marka, image) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insertSQL);
        
        $addedCount = 0;
        foreach ($sampleProducts as $product) {
            if ($stmt->execute([
                $product['name'],
                $product['description'],
                $product['price'],
                $product['category'],
                $product['marka'],
                $product['image']
            ])) {
                $addedCount++;
                echo "✅ Məhsul əlavə edildi: " . $product['name'] . "<br>";
            } else {
                echo "❌ Məhsul əlavə edilə bilmədi: " . $product['name'] . "<br>";
            }
        }
        
        echo "<br><strong>Cəmi əlavə edilən məhsul: $addedCount</strong><br>";
    } else {
        echo "<h3>Məhsullar artıq mövcuddur:</h3>";
        echo "Cəmi məhsul sayı: $count<br>";
    }
    
    echo "<br><h3>✅ Setup tamamlandı!</h3>";
    echo "<p><a href='/api/products' target='_blank'>Products API-ni test et</a></p>";
    
} catch (Exception $e) {
    echo "❌ Xəta: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p>Setup tamamlandı: " . date('Y-m-d H:i:s') . "</p>";
?>