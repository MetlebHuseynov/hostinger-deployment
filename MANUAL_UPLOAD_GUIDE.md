# Hostinger Manual Upload Təlimatı

## FTP Probleminin Həlli

FTP əlaqəsi uğursuz olduğu üçün manual upload etmək lazımdır.

## 1. Hostinger cPanel-ə Daxil Olma

1. https://hpanel.hostinger.com saytına daxil olun
2. Login məlumatlarınızla daxil olun
3. **File Manager** seçimini tapın və açın

## 2. File Manager-də Upload

### Addımlar:
1. `public_html` qovluğuna daxil olun
2. **Upload** düyməsinə basın
3. Aşağıdakı faylları seçin və yükləyin:

### Ana HTML faylları:
- index.html
- about.html
- categories.html
- products.html
- marka.html
- product.html
- contact.html

### Qovluqlar (ZIP olaraq yükləyin və extract edin):
- css/ (bütün CSS faylları)
- js/ (bütün JavaScript faylları)
- images/ (bütün şəkillər)
- admin/ (admin panel faylları)
- api/ (PHP API faylları)
- config/ (konfiqurasiya faylları)
- models/ (PHP model faylları)

### Konfiqurasiya faylları:
- .env (environment variables)
- .htaccess (HOSTINGER_htaccess_file.txt-ni .htaccess olaraq yükləyin)

## 3. Qovluq Strukturu

Yükləmədən sonra `public_html` qovluğunda belə struktur olmalıdır:

```
public_html/
├── index.html
├── about.html
├── categories.html
├── products.html
├── marka.html
├── product.html
├── contact.html
├── .env
├── .htaccess
├── css/
│   ├── style.css
│   └── admin.css
├── js/
│   ├── main.js
│   ├── products.js
│   └── admin/
│       ├── login.js
│       ├── dashboard.js
│       └── products.js
├── images/
│   └── *.svg
├── admin/
│   ├── login.html
│   ├── dashboard.html
│   └── js/
├── api/
│   ├── auth.php
│   ├── products.php
│   └── database.php
├── config/
│   └── database.php
├── models/
│   └── Product.php
└── uploads/ (boş qovluq yaradın)
```

## 4. Test Etmək

Yükləmədən sonra:
1. https://prolinege.com - ana sayt
2. https://prolinege.com/admin/login.html - admin panel

## 5. Database Konfiqurasiyası

cPanel-də MySQL Database yaradın:
- Database adı: `u482576391_prolinegee`
- İstifadəçi: `u482576391_prolinegee`
- Parol: güclü parol

.env faylında database məlumatlarını yeniləyin.

## 6. Permissions

File Manager-də:
- .env faylı üçün: 644
- PHP faylları üçün: 644
- Qovluqlar üçün: 755
- uploads/ qovluğu üçün: 777

---

**Qeyd:** FTP problemi həll olunana qədər bu manual metodu istifadə edin.