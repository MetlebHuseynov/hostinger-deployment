# Hostinger Deployment Guide - ProLine Website

## 🚨 VACIB: Hostinger Fayl Strukturu

Hostinger-də fayllar **public_html** qovluğuna yerləşdirilməlidir:

```
public_html/
├── .htaccess                    # Əsas .htaccess fayl
├── index.html                   # Ana səhifə (public/index.html-dən)
├── about.html                   # Haqqımızda səhifəsi
├── categories.html              # Kateqoriyalar
├── products.html                # Məhsullar
├── marka.html                   # Markalar
├── product.html                 # Məhsul detalları
├── contact.html                 # Əlaqə
├── api/                         # PHP API faylları
│   ├── index.php
│   ├── auth.php
│   ├── products.php
│   ├── categories.php
│   ├── markas.php
│   ├── users.php
│   └── dashboard.php
├── admin/                       # Admin panel
│   ├── dashboard.html
│   ├── login.html
│   ├── products.html
│   ├── categories.html
│   ├── brands.html
│   ├── users.html
│   ├── settings.html
│   └── js/
├── css/                         # CSS faylları
│   ├── bootstrap.min.css
│   ├── style.css
│   └── admin.css
├── js/                          # JavaScript faylları
│   ├── config.js
│   ├── main.js
│   ├── categories.js
│   ├── products.js
│   └── admin/
├── images/                      # Şəkil faylları
├── uploads/                     # Yüklənmiş fayllar
├── config/                      # PHP konfiqurasiya
│   ├── config.php
│   └── database.php
├── models/                      # PHP model sinifləri
│   ├── Product.php
│   ├── Category.php
│   ├── Marka.php
│   └── User.php
└── .env                         # Environment variables
```

## 📋 Deployment Addımları:

### 1. Faylları Hostinger-ə Yükləyin:
- **public/** qovluğundakı bütün HTML faylları → **public_html/**
- **api/** qovluğu → **public_html/api/**
- **config/** qovluğu → **public_html/config/**
- **models/** qovluğu → **public_html/models/**
- **.env** faylı → **public_html/.env**
- **HOSTINGER_htaccess_file.txt** faylını **.htaccess** adı ilə → **public_html/.htaccess**

### 2. Fayl İcazələrini Təyin Edin:
```
Qovluqlar: 755
PHP faylları: 644
HTML faylları: 644
.htaccess: 644
.env: 600 (təhlükəsizlik üçün)
```

### 3. Database Konfiqurasiyası:
.env faylında Hostinger database məlumatları:
```
DB_HOST=localhost
DB_NAME=u482576391_yUuh0
DB_USER=u482576391_Md1PB
DB_PASSWORD=0993991019Metleb
DB_PORT=3306
```

### 4. Test Edin:
- Ana səhifə: https://prolinege.com/
- API test: https://prolinege.com/api/test
- Admin panel: https://prolinege.com/admin/dashboard.html

## ⚠️ Ümumi Problemlər və Həllər:

1. **403 Forbidden:**
   - Fayl icazələrini yoxlayın (755/644)
   - .htaccess faylının düzgün olduğunu təsdiq edin
   - public_html qovluğunda index.html olduğunu yoxlayın

2. **API işləmir:**
   - PHP versiyasının 7.4+ olduğunu yoxlayın
   - Database əlaqəsini test edin
   - .env faylının düzgün yerləşdirildiyini yoxlayın

3. **CSS/JS yüklənmir:**
   - Fayl yollarının düzgün olduğunu yoxlayın
   - .htaccess-da static fayl qaydalarını yoxlayın

## 🔧 Debugging:
1. Hostinger File Manager-də fayl strukturunu yoxlayın
2. Error logs-u yoxlayın (cPanel → Error Logs)
3. PHP error reporting-i aktiv edin