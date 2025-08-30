# Hostinger Deployment Təlimatları

## 🚀 Layihə Hazırdır!

CRUD sistemi tam olaraq yenidən yazılıb və test edilib. Bütün əməliyyatlar (Create, Read, Update, Delete) düzgün işləyir.

## 📦 Deployment Faylları

✅ **prolinege-deployment.zip** - Tam layihə faylı (4.4 MB)

## 🔧 Deployment Addımları

### Metod 1: Manual Upload (Tövsiyə edilir)

1. **Hostinger cPanel-ə daxil olun:**
   - https://hpanel.hostinger.com
   - Login məlumatlarınızla daxil olun

2. **File Manager açın:**
   - File Manager seçimini tapın və açın
   - `public_html` qovluğuna daxil olun

3. **ZIP faylını yükləyin:**
   - `prolinege-deployment.zip` faylını yükləyin
   - Extract/Unzip edin
   - Bütün fayllar `public_html` qovluğunda olmalıdır

4. **Konfiqurasiya fayllarını yoxlayın:**
   - `.env` faylının mövcud olduğunu təsdiq edin
   - `HOSTINGER_htaccess_file.txt`-ni `.htaccess` olaraq adlandırın

### Metod 2: FileZilla ilə Upload

**FTP Məlumatları:**
```
Host: ftp.prolinege.com
Username: u482576391.prolinege
Password: 0993991019Matlab?
Port: 21
```

1. FileZilla-da Site Manager açın
2. Yuxarıdakı məlumatları daxil edin
3. Qoşulun və bütün faylları `public_html`-ə yükləyin

## 🗄️ Database Konfiqurasiyası

### MySQL Database Yaradın:
- Database adı: `u482576391_prolinegee`
- İstifadəçi adı: `u482576391_prolinegee`
- Güclü parol təyin edin

### .env faylını yeniləyin:
```env
DB_HOST=localhost
DB_NAME=u482576391_prolinegee
DB_USER=u482576391_prolinegee
DB_PASSWORD=your_actual_mysql_password
```

## ✅ Test Edilmiş Funksiyalar

### Categories CRUD:
- ✅ CREATE - Yeni kateqoriya yaratma
- ✅ READ - Kateqoriya oxuma
- ✅ UPDATE - Kateqoriya yeniləmə
- ✅ DELETE - Kateqoriya silmə

### Products CRUD:
- ✅ CREATE - Yeni məhsul yaratma
- ✅ READ - Məhsul oxuma
- ✅ UPDATE - Məhsul yeniləmə
- ✅ DELETE - Məhsul silmə

### Brands CRUD:
- ✅ CREATE - Yeni marka yaratma
- ✅ READ - Marka oxuma
- ✅ UPDATE - Marka yeniləmə
- ✅ DELETE - Marka silmə

## 🌐 Canlı Sayt

**Ana səhifə:** https://prolinege.com
**Admin panel:** https://prolinege.com/admin/login.html

## 🔒 Admin Giriş Məlumatları

Database-də admin istifadəçi yaratmaq üçün `create_admin_user.php` faylını istifadə edin.

## 📞 Dəstək

Hər hansı problem yaşasanız, deployment guide-larına baxın:
- `HOSTINGER_DEPLOYMENT.md`
- `FILEZILLA_UPLOAD_GUIDE.md`
- `MANUAL_UPLOAD_GUIDE.md`

---

**🎉 Layihə tam hazırdır və deployment üçün optimizasiya edilib!**