<?php
/**
 * Database Connection Test
 * Verilənlər bazası bağlantısını test edir
 */

// .env faylını yüklə
function loadEnv() {
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                if (!array_key_exists($name, $_ENV)) {
                    $_ENV[$name] = $value;
                }
            }
        }
    }
}

// .env məlumatlarını yüklə
loadEnv();

// Database məlumatları
$host = $_ENV['DB_HOST'] ?? 'localhost';
$db_name = $_ENV['DB_NAME'] ?? 'u482576391_yUuh0';
$username = $_ENV['DB_USER'] ?? 'u482576391_Md1PB';
$password = $_ENV['DB_PASSWORD'] ?? '0993991019Metleb';
$port = $_ENV['DB_PORT'] ?? '3306';

echo "=== DATABASE CONNECTION TEST ===\n";
echo "Host: $host\n";
echo "Database: $db_name\n";
echo "Username: $username\n";
echo "Password: " . str_repeat('*', strlen($password)) . "\n";
echo "Port: $port\n\n";

try {
    // PDO bağlantısı
    $dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ DATABASE CONNECTION: SUCCESS\n";
    
    // Database versiyasını yoxla
    $stmt = $pdo->query('SELECT VERSION() as version');
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 MySQL Version: " . $version['version'] . "\n";
    
    // Cədvəlləri yoxla
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n📋 TABLES IN DATABASE ($db_name):\n";
    if (empty($tables)) {
        echo "❌ NO TABLES FOUND - Database boşdur!\n";
        echo "\n🔧 SOLUTION: Database-ə cədvəllər əlavə etməlisiniz:\n";
        echo "   - users cədvəli\n";
        echo "   - categories cədvəli\n";
        echo "   - products cədvəli\n";
        echo "   - markas cədvəli\n";
    } else {
        foreach ($tables as $table) {
            echo "   ✅ $table\n";
        }
        
        // Hər cədvəldə neçə sətir var?
        echo "\n📊 RECORD COUNTS:\n";
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
                $count = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "   $table: " . $count['count'] . " records\n";
            } catch (Exception $e) {
                echo "   $table: Error reading - " . $e->getMessage() . "\n";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "❌ DATABASE CONNECTION FAILED!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "🔧 POSSIBLE SOLUTIONS:\n";
    echo "1. Hostinger control panelində database məlumatlarını yoxlayın\n";
    echo "2. Database parolunu sıfırlayın\n";
    echo "3. Database istifadəçisinin icazələrini yoxlayın\n";
    echo "4. Database serverinin aktiv olduğunu təsdiq edin\n";
}

echo "\n=== TEST COMPLETED ===\n";
?>