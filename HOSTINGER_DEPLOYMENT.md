# Hostinger.com Deployment Təlimatları

## 1. Hazırlıq

### Lazımi fayllar:
- Bütün layihə faylları
- `.env.production` faylı (`.env` adı ilə yükləyin)
- `package.json` və `package-lock.json`

### MySQL Verilənlər Bazası:
- cPanel-də MySQL Database yaradın
- Database adı: `u482576391_prolinegee`
- İstifadəçi adı: `u482576391_prolinegee`
- Parol: güclü parol təyin edin

## 2. Faylların Yüklənməsi

### File Manager vasitəsilə:
1. cPanel-ə daxil olun
2. File Manager-i açın
3. `public_html` qovluğuna keçin
4. Bütün layihə fayllarını yükləyin

### Qovluq strukturu:
```
public_html/
├── server.js
├── package.json
├── .env
├── config/
├── controllers/
├── models/
├── routes/
├── middleware/
├── data/
├── public/
│   ├── css/
│   ├── js/
│   ├── images/
│   ├── uploads/
│   └── *.html
└── node_modules/ (npm install ilə yaranacaq)
```

## 3. Environment Variables Konfiqurasiyası

### .env faylını redaktə edin:
```env
# Database
DB_TYPE=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u482576391_prolinegee
DB_USER=u482576391_prolinegee
DB_PASSWORD=your_actual_mysql_password

# Server
PORT=3000
NODE_ENV=production

# Security
SESSION_SECRET=ProLine2024_Hostinger_VerySecure_SessionSecret_RandomString_MinimumThirtyTwoCharacters
JWT_SECRET=ProLine_JWT_2024_Hostinger_SuperSecure_RandomKey_ForProductionOnly_MinimumSixtyFourChars
BCRYPT_ROUNDS=12

# File Upload
UPLOAD_PATH=/public_html/public/uploads
MAX_FILE_SIZE=5242880

# CORS
CORS_ORIGIN=https://prolinegee.com

# Rate Limiting
RATE_LIMIT_WINDOW_MS=900000
RATE_LIMIT_MAX_REQUESTS=200

# Email (Hostinger)
EMAIL_HOST=smtp.hostinger.com
EMAIL_PORT=587
EMAIL_USER=noreply@prolinegee.com
EMAIL_PASS=your_email_password
EMAIL_FROM=ProLine <noreply@prolinegee.com>

# Trust Proxy
TRUST_PROXY=true
```

## 4. Node.js Modullarının Quraşdırılması

### Terminal/SSH vasitəsilə:
```bash
cd /public_html
npm install --production
```

### Əgər SSH yoxdursa:
- cPanel-də Terminal açın
- Yuxarıdakı əmrləri icra edin

## 5. MySQL Cədvəllərinin Yaradılması

### Avtomatik yaradılma:
Server ilk dəfə işə düşəndə avtomatik olaraq cədvəllər yaradılacaq.

### Manual yaradılma (əgər lazım olarsa):
```sql
-- products cədvəli
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2),
    category VARCHAR(100),
    marka VARCHAR(100),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- categories cədvəli
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- markas cədvəli
CREATE TABLE markas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    logo VARCHAR(255),
    website VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- users cədvəli
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 6. Node.js Aplikasiyanın İşə Salınması

### PM2 istifadə edərək (tövsiyə edilir):
```bash
# PM2 quraşdırın
npm install -g pm2

# Aplikasiyanı işə salın
pm2 start server.js --name "proline-app"

# Avtomatik restart üçün
pm2 startup
pm2 save
```

### Sadə işə salma:
```bash
node server.js
```

## 7. Domain Konfiqurasiyası

### Hostinger cPanel-də:
1. **Subdomain/Domain** bölməsinə keçin
2. Domain-i `/public_html` qovluğuna yönləndirin
3. SSL sertifikatını aktivləşdirin

### .htaccess faylı konfiqurasiyası:
1. `public/.htaccess.hostinger` faylını `public/.htaccess` adı ilə kopyalayın
2. Hostinger-ə yükləyərkən aşağıdakı dəyişiklikləri edin:
   - HTTPS redirection-u aktiv edin (# işarələrini silin)
   - Hotlink protection-u aktiv edin və domain-i `prolinegee.com` olaraq dəyişin
   - Node.js routing qaydalarını tənzimləyin

```apache
# Hostinger üçün minimal .htaccess
RewriteEngine On

# HTTPS Redirection
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Static files
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /index.html [L]
```

## 8. Test və Yoxlama

### Yoxlanılacaq funksiyalar:
- [ ] Ana səhifə açılır
- [ ] Admin panel işləyir
- [ ] Məhsul əlavə etmə
- [ ] Kateqoriya əlavə etmə
- [ ] Brend əlavə etmə
- [ ] Şəkil yükləmə
- [ ] Database əlaqəsi
- [ ] API endpoint-ləri

### Log fayllarını yoxlayın:
```bash
# PM2 logs
pm2 logs proline-app

# Server logs
tail -f /public_html/logs/app.log
```

## 9. Təhlükəsizlik

### Mühüm qeydlər:
- `.env` faylında həqiqi parolları istifadə edin
- Database istifadəçisinin güclü parolunu təyin edin
- SSL sertifikatını aktivləşdirin
- Firewall qaydalarını yoxlayın
- Müntəzəm backup alın

## 10. Problemlərin Həlli

### Ümumi problemlər:

**Server işə düşmür:**
- Node.js versiyasını yoxlayın (>=18.0.0)
- npm install düzgün icra olunubmu?
- .env faylı düzgün konfiqurasiya olunubmu?

**Database bağlantı xətası:**
- MySQL məlumatları düzgündür?
- Database yaradılıb?
- İstifadəçi icazələri verilibmi?

**File upload işləmir:**
- uploads qovluğu mövcuddur?
- Qovluq icazələri düzgündür? (755)
- UPLOAD_PATH düzgün təyin olunub?

**Rate limiting çox sərt:**
- .env faylında RATE_LIMIT_MAX_REQUESTS artırın
- Development mühitində rate limiting deaktiv edin

**403 Forbidden xətası:**
- .htaccess faylında hotlink protection deaktiv edin
- HTTPS redirection qaydalarını yoxlayın
- Fayl icazələrini yoxlayın (644 fayllar üçün, 755 qovluqlar üçün)
- cPanel File Manager-də fayl sahibliyini yoxlayın
- public_html qovluğunda index.html faylı olduğundan əmin olun
- .htaccess faylında Node.js routing qaydalarını düzgün konfiqurasiya edin:
  ```apache
  # Ana .htaccess (public_html/.htaccess)
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_URI} !^/public/
  RewriteRule ^(.*)$ /public/$1 [L]
  
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^(.*)$ /public/index.html [L]
  ```
- public/.htaccess faylında SPA routing aktiv edin:
  ```apache
  # public/.htaccess
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_URI} !\.(css|js|png|jpg|jpeg|gif|svg|webp|woff|woff2|ttf|eot|ico|pdf)$
  RewriteRule ^(.*)$ /index.html [L]
  ```
- Server.js faylına birbaşa müraciəti məhdudlaşdırın
- Hostinger Node.js hosting üçün düzgün qovluq strukturunu təmin edin

**CSS və Digər Səhifələr Görünmür:**
- .htaccess faylında Node.js routing qaydalarının düzgün konfiqurasiya olunduğunu yoxlayın
- Static fayllar üçün istisna qaydalarının aktiv olduğunu təsdiq edin:
  ```apache
  RewriteCond %{REQUEST_URI} !^/css/
  RewriteCond %{REQUEST_URI} !^/js/
  RewriteCond %{REQUEST_URI} !^/images/
  ```
- server.js-də express.static middleware-in düzgün konfiqurasiya olunduğunu yoxlayın
- CSS və JS fayllarının /public_html/public/ qovluğunda olduğunu təsdiq edin

## 11. Hostinger-də 403 Xətasının Həlli

### Addım-addım həll yolu:

1. **Fayl icazələrini yoxlayın:**
   - Bütün fayllar: 644
   - Bütün qovluqlar: 755
   - cPanel File Manager-də sağ klik → Permissions

2. **.htaccess fayllarını düzgün konfiqurasiya edin:**
   - Ana .htaccess faylını yeniləyin (public_html/.htaccess)
   - public/.htaccess faylını SPA routing üçün hazırlayın
   - .htaccess.hostinger faylını ehtiyat kimi saxlayın

3. **Qovluq strukturunu yoxlayın:**
   ```
   public_html/
   ├── .htaccess (yenilənmiş)
   ├── server.js
   ├── public/
   │   ├── .htaccess (SPA routing)
   │   ├── index.html
   │   ├── css/
   │   ├── js/
   │   └── images/
   ```

4. **Index.html faylının mövcudluğunu təsdiq edin:**
   - public/index.html faylı olmalıdır
   - Fayl boş olmamalıdır
   - HTML strukturu düzgün olmalıdır

5. **Server.js faylına müraciəti məhdudlaşdırın:**
   - .htaccess faylında JavaScript fayllarına müraciət qadağan edilib
   - Yalnız public qovluğundakı JS fayllarına icazə verilir

### Yoxlama addımları:
- [ ] Sayt açılır (403 xətası yoxdur)
- [ ] CSS faylları yüklənir
- [ ] JavaScript faylları işləyir
- [ ] Şəkillər görünür
- [ ] SPA routing işləyir

## 12. Dəstək

Problem yaşadığınız halda:
1. Server logs-unu yoxlayın
2. Browser console-unu yoxlayın
3. Network tab-ında API sorğularını yoxlayın
4. Database bağlantısını test edin
5. .htaccess fayllarını yenidən yoxlayın

---

**Uğurlar! 🚀**

Layihəniz Hostinger-də uğurla işə salındıqdan sonra https://prolinegee.com ünvanından əlçatan olacaq.