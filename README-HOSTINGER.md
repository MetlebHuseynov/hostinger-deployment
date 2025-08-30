# ProLine E-commerce - Hostinger Deployment

Bu qovluq Hostinger serverin? yukl?m?k ucun haz?rlanm?sd?r.

## ?? Yukl?m? Add?mlar?

### 1. Fayllar? Yukl?yin
- Butun fayllar? Hostinger cPanel File Manager vasit?sil? /public_html qovluguna yukl?yin
- V? ya FTP/SFTP istifad? ed?r?k yukl?yin

### 2. MySQL Veril?nl?r Bazas? Yarad?n
- cPanel-d? MySQL Databases bolm?sin? gedin
- Yeni veril?nl?r bazas? yarad?n: u482576391_prolinegee
- Yeni istifad?ci yarad?n: u482576391_prolinegee
- Istifad?cini veril?nl?r bazas?na ?lav? edin (butun icaz?l?r)

### 3. Node.js Modullar?n? Qurasd?r?n
Terminal/SSH vasit?sil?:
cd /public_html
npm install --production

### 4. ?lav? Konfiqurasiya
- .env fayl?nda MySQL m?lumatlar?n? yoxlay?n
- File permissions: 644 (.env fayl ucun)
- Folder permissions: 755

### 5. T?tbiqi Baslad?n
node server.js
# v? ya PM2 istifad? edin:
pm2 start server.js --name proline

## ?? Domain: prolinegee.com
## ?? D?st?k: HOSTINGER_DEPLOYMENT.md fayl?na bax?n
