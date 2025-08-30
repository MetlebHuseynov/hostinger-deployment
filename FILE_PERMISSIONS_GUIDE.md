# Hostinger Fayl İcazələri Təlimatı

## 🔐 Düzgün Fayl İcazələri:

### Qovluqlar (Directories): **755**
```
public_html/                    → 755
public_html/api/                → 755
public_html/admin/              → 755
public_html/css/                → 755
public_html/js/                 → 755
public_html/images/             → 755
public_html/uploads/            → 755
public_html/config/             → 755
public_html/models/             → 755
```

### HTML Faylları: **644**
```
public_html/index.html          → 644
public_html/about.html          → 644
public_html/categories.html     → 644
public_html/products.html       → 644
public_html/marka.html          → 644
public_html/product.html        → 644
public_html/contact.html        → 644
public_html/admin/*.html        → 644
```

### PHP Faylları: **644**
```
public_html/api/index.php       → 644
public_html/api/auth.php        → 644
public_html/api/products.php    → 644
public_html/api/categories.php  → 644
public_html/api/markas.php      → 644
public_html/api/users.php       → 644
public_html/api/dashboard.php   → 644
public_html/config/config.php   → 644
public_html/config/database.php → 644
public_html/models/*.php        → 644
```

### CSS/JS Faylları: **644**
```
public_html/css/*.css           → 644
public_html/js/*.js             → 644
```

### Şəkil Faylları: **644**
```
public_html/images/*.svg        → 644
public_html/images/*.png        → 644
public_html/images/*.jpg        → 644
```

### Xüsusi Fayllar:
```
public_html/.htaccess           → 644
public_html/.env                → 600 (təhlükəsizlik üçün)
```

## 📝 Hostinger File Manager-də İcazələri Dəyişdirmək:

### 1. File Manager-ə daxil olun:
- Hostinger Control Panel → File Manager

### 2. Fayl/Qovluq seçin və sağ klik edin:
- "Permissions" və ya "Change Permissions" seçin

### 3. İcazələri təyin edin:
- **755**: Read(4) + Write(2) + Execute(1) = 7 (Owner), Read(4) + Execute(1) = 5 (Group), Read(4) + Execute(1) = 5 (Others)
- **644**: Read(4) + Write(2) = 6 (Owner), Read(4) = 4 (Group), Read(4) = 4 (Others)
- **600**: Read(4) + Write(2) = 6 (Owner), 0 (Group), 0 (Others)

## 🚨 Vacib Qeydlər:

1. **Qovluqlar həmişə 755 olmalıdır** - başqa cür fayllara giriş olmaz
2. **PHP faylları 644 olmalıdır** - 755 təhlükəsizlik riski yaradır
3. **.env faylı 600 olmalıdır** - database məlumatlarının təhlükəsizliyi üçün
4. **uploads/ qovluğu 755, lakin içindəki fayllar 644**

## ✅ İcazələri Yoxlamaq:

Hostinger File Manager-də hər faylın yanında icazələr göstərilir:
- **drwxr-xr-x** = 755 (qovluq)
- **-rw-r--r--** = 644 (fayl)
- **-rw-------** = 600 (məxfi fayl)

## 🔧 Problemli İcazələr:

❌ **777** - Çox təhlükəli, heç vaxt istifadə etməyin
❌ **666** - Fayl üçün təhlükəli
❌ **000** - Heç kim oxuya bilməz

✅ **755** - Qovluqlar üçün ideal
✅ **644** - Fayllar üçün ideal
✅ **600** - Məxfi fayllar üçün ideal