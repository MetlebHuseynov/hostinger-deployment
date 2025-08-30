# Hostinger Deployment Guide

## 🚀 Avtomatik Deployment Üsulları

### 1. Batch Script (Windows CMD)
```bash
# Sadə istifadə
double-click deploy.bat
# və ya terminal-da
deploy.bat
```

### 2. PowerShell Script (Tövsiyə edilir)
```powershell
# PowerShell-da işə salın
.\deploy.ps1
```

### 3. WinSCP Script (Ən etibarlı)
1. [WinSCP](https://winscp.net/) yükləyin
2. `winscp_deploy.txt` faylında `PASSWORD` sözünü əsl şifrə ilə əvəz edin
3. WinSCP-də script import edin və ya:
```bash
winscp.exe /script=winscp_deploy.txt
```

## 📋 FTP Məlumatları
- **Server:** ftp://prolinege.com
- **IP:** 92.112.182.61
- **Username:** u482576391
- **Directory:** public_html
- **Password:** (sizin FTP şifrəniz)

## 📁 Yüklənəcək Fayllar

### ✅ HTML Faylları
- index.html, about.html, categories.html
- products.html, marka.html, product.html, contact.html

### ✅ CSS & JS
- css/ qovluğu (bütün stil faylları)
- js/ qovluğu (bütün JavaScript faylları)
- js/admin/ qovluğu (admin panel JS faylları)

### ✅ Şəkillər
- images/ qovluğu (bütün SVG faylları)

### ✅ Admin Panel
- admin/ qovluğu (bütün admin HTML faylları)
- admin/js/ qovluğu (admin JavaScript faylları)

### ✅ Backend
- api/ qovluğu (PHP API faylları)
- config/ qovluğu (konfiqurasiya faylları)
- models/ qovluğu (PHP model faylları)
- uploads/ qovluğu (fayl yükləmə üçün)

### ✅ Konfiqurasiya
- .htaccess faylı (HOSTINGER_htaccess_file.txt-dən)

## 🔧 Deployment Sonrası

### 1. Verilənlər Bazası
- Hostinger control panel-də MySQL DB yaradın
- `config/database.php` faylını yeniləyin:
```php
$host = 'localhost';
$dbname = 'u482576391_proline';
$username = 'u482576391_admin';
$password = 'YOUR_DB_PASSWORD';
```

### 2. File Permissions
```bash
# uploads qovluğuna yazma icazəsi
chmod 755 uploads/
chmod 644 config/*.php
```

### 3. Test Edin
- **Ana səhifə:** https://prolinege.com
- **Admin panel:** https://prolinege.com/admin/login.html
- **Manual login:** https://prolinege.com/admin/manual-login.html

## ⚠️ Vacib Qeydlər

1. **Şifrə təhlükəsizliyi:** Script fayllarında şifrə saxlamayın
2. **File permissions:** uploads/ qovluğuna yazma icazəsi lazımdır
3. **Database:** MySQL verilənlər bazası yaradıb konfiqurasiya edin
4. **SSL:** Hostinger-də SSL sertifikatını aktivləşdirin

## 🔄 Yenilənmə

Hər dəfə dəyişiklik etdikdə:
1. Lokal faylları yeniləyin
2. Deployment script işə salın
3. Saytı test edin

## 🆘 Problem Həlli

### FTP Əlaqə Problemi
- FTP şifrəsini yoxlayın
- Firewall ayarlarını yoxlayın
- Hostinger support ilə əlaqə saxlayın

### File Upload Problemi
- File permissions yoxlayın
- Disk sahəsini yoxlayın
- File path-ları yoxlayın

### Database Problemi
- MySQL connection məlumatlarını yoxlayın
- Database user permissions yoxlayın
- Error log-ları yoxlayın

---

**Uğurlar! 🎉**

Saytınız https://prolinege.com ünvanında canlı olacaq.