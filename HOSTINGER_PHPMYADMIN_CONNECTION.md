# 🗄️ Hostinger phpMyAdmin Databaza Qoşulması

## 📋 Databaza Məlumatları:

### Hostinger Database Konfiqurasiyası:
```
Database Host: localhost
Database Name: u482576391_yUuh0
Database User: u482576391_Md1PB
Database Password: 0993991019Metleb
Database Port: 3306
Database Type: MySQL
```

## 🔗 phpMyAdmin-ə Qoşulma:

### 1. Hostinger cPanel vasitəsilə:
1. Hostinger hesabınıza daxil olun
2. cPanel-ə keçin
3. "Databases" bölməsində "phpMyAdmin" seçin
4. Avtomatik olaraq databaza qoşulacaq

### 2. Birbaşa phpMyAdmin URL:
- URL: https://cpanel.hostinger.com/phpmyadmin/
- Və ya: https://[your-domain]/phpmyadmin/

### 3. Qoşulma məlumatları:
```
Server: localhost
Username: u482576391_Md1PB
Password: 0993991019Metleb
Database: u482576391_yUuh0
```

## 📊 Databaza Strukturu:

### Mövcud Cədvəllər:
1. **categories** - Məhsul kateqoriyaları
2. **markas** - Məhsul markaları
3. **products** - Məhsullar
4. **users** - İstifadəçilər (admin panel üçün)

### Categories Cədvəli:
```sql
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Markas Cədvəli:
```sql
CREATE TABLE markas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Products Cədvəli:
```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    marka_id INT,
    image_url VARCHAR(500),
    stock_quantity INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (marka_id) REFERENCES markas(id)
);
```

## 🔧 Databaza Əməliyyatları:

### Məlumat Əlavə Etmək:
```sql
-- Kateqoriya əlavə etmək
INSERT INTO categories (name, description) VALUES ('Elektronika', 'Elektron məhsullar');

-- Marka əlavə etmək
INSERT INTO markas (name, description) VALUES ('Samsung', 'Samsung məhsulları');

-- Məhsul əlavə etmək
INSERT INTO products (name, description, price, category_id, marka_id, stock_quantity) 
VALUES ('Galaxy S24', 'Samsung Galaxy S24 smartphone', 1200.00, 1, 1, 50);
```

### Məlumat Yoxlamaq:
```sql
-- Bütün məhsulları görmək
SELECT p.*, c.name as category_name, m.name as marka_name 
FROM products p 
LEFT JOIN categories c ON p.category_id = c.id 
LEFT JOIN markas m ON p.marka_id = m.id;

-- Kateqoriyalar
SELECT * FROM categories;

-- Markalar
SELECT * FROM markas;
```

## 🚀 API Endpoints:

### Məhsullar üçün:
- GET `/api/products.php` - Bütün məhsullar
- POST `/api/products.php` - Yeni məhsul əlavə et
- PUT `/api/products.php?id=1` - Məhsulu yenilə
- DELETE `/api/products.php?id=1` - Məhsulu sil

### Kateqoriyalar üçün:
- GET `/api/categories.php` - Bütün kateqoriyalar
- POST `/api/categories.php` - Yeni kateqoriya əlavə et

### Markalar üçün:
- GET `/api/markas.php` - Bütün markalar
- POST `/api/markas.php` - Yeni marka əlavə et

## 🔐 Təhlükəsizlik:

1. **Database parolunu dəyişdirin** (production üçün)
2. **SSL sertifikatı** istifadə edin
3. **SQL injection** qarşısını alın (prepared statements)
4. **Input validation** tətbiq edin
5. **Rate limiting** quraşdırın

## 📝 Qeydlər:

- Hostinger-də databaza adı və istifadəçi adı eynidir
- phpMyAdmin-ə cPanel vasitəsilə daxil olmaq daha təhlükəsizdir
- Backup-ları mütəmadi olaraq götürün
- Production-da debug məlumatlarını söndürün

## 🆘 Problem Həlli:

### Qoşulma problemi:
1. Databaza məlumatlarını yoxlayın
2. Hostinger server statusunu yoxlayın
3. IP ünvanının icazə siyahısında olduğunu təsdiq edin

### Performans problemi:
1. İndekslər əlavə edin
2. Query-ləri optimallaşdırın
3. Cache istifadə edin