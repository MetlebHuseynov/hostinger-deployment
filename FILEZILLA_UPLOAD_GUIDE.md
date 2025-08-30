# FileZilla ilə Hostinger-ə Upload Təlimatı

## 1. FileZilla Konfiqurasiyası

### Site Manager ilə Qoşulma:
1. FileZilla-nı açın
2. **File** > **Site Manager** (Ctrl+S)
3. **New Site** düyməsinə basın
4. Aşağıdakı məlumatları daxil edin:

```
Host: ftp.prolinege.com
Protocol: FTP - File Transfer Protocol
Encryption: Use plain FTP (insecure)
Logon Type: Normal
User: u482576391.prolinege
Password: 0993991019Matlab?
Port: 21
```

5. **Connect** düyməsinə basın

### Sürətli Qoşulma:
Yuxarıdakı toolbar-da:
- **Host**: ftp.prolinege.com
- **Username**: u482576391.prolinege
- **Password**: 0993991019Matlab?
- **Port**: 21
- **Quickconnect** düyməsinə basın

## 2. Upload Prosesi

### Əgər Qoşulma Uğurlu Olarsa:
1. Sağ tərəfdə (Remote site) `/home/u482576391/domains/prolinege.com/public_html` qovluğuna gedin
2. Sol tərəfdə (Local site) `c:\Users\ABV\Desktop\hostinger-deployment` qovluğunu açın
3. Aşağıdakı faylları seçin və sağ tərəfə sürükləyin:

**Ana fayllar:**
- index.html
- about.html
- categories.html
- products.html
- marka.html
- product.html
- contact.html

**Qovluqlar (bütün məzmunu ilə):**
- css/
- js/
- images/
- admin/
- api/
- config/
- models/
- uploads/

**Konfiqurasiya faylları:**
- HOSTINGER_htaccess_file.txt (bunu .htaccess olaraq yenidən adlandırın)

## 3. Problemlərin Həlli

### Əgər "530 Login incorrect" xətası alırsınızsa:
1. Hostinger control panel-ə daxil olun: https://hpanel.hostinger.com
2. **Files** > **FTP Accounts** bölməsinə gedin
3. Mövcud FTP hesabının statusunu yoxlayın
4. Yeni FTP hesabı yaradın və ya şifrəni sıfırlayın
5. Düzgün məlumatları FileZilla-da yenidən cəhd edin

### Əgər qoşulma uğurlu olarsa:
- Bütün fayllar yüklənəndən sonra sayt https://prolinege.com ünvanında canlı olacaq
- Admin panel: https://prolinege.com/admin/login.html

## 4. Yoxlama
Upload tamamlandıqdan sonra:
1. https://prolinege.com saytını açın
2. Səhifələrin düzgün yüklənib-yüklənmədiyini yoxlayın
3. Admin panel-ə daxil olmağı test edin

---
**Qeyd**: Əgər FileZilla ilə də qoşula bilmirsinizsə, FTP məlumatları səhvdir və Hostinger dəstəyi ilə əlaqə saxlamalısınız.