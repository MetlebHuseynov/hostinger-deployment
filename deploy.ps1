# Hostinger FTP Deployment Script (PowerShell)
# ========================================

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "    Hostinger FTP Deployment Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "FTP Server: ftp://prolinege.com" -ForegroundColor Yellow
Write-Host "Username: u482576391" -ForegroundColor Yellow
Write-Host "Target Directory: public_html" -ForegroundColor Yellow
Write-Host ""

# Get FTP password securely
$password = Read-Host "Enter FTP Password" -AsSecureString
$plainPassword = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($password))

Write-Host "Starting deployment..." -ForegroundColor Green
Write-Host ""

# Create FTP command file with password
@"
open prolinege.com
u482576391
$plainPassword
bin
cd public_html
prompt

# Upload HTML files
put index.html
put about.html
put categories.html
put products.html
put marka.html
put product.html
put contact.html

# Upload CSS files
mkdir css
cd css
mput css\*.css
cd ..

# Upload JS files
mkdir js
cd js
mput js\*.js
mkdir admin
cd admin
mput js\admin\*.js
cd ..
cd ..

# Upload Images
mkdir images
cd images
mput images\*.svg
cd ..

# Upload Admin files
mkdir admin
cd admin
mput admin\*.html
mkdir js
cd js
mput admin\js\*.js
cd ..
cd ..

# Upload API files
mkdir api
cd api
mput api\*.php
cd ..

# Upload Config files
mkdir config
cd config
mput config\*.php
cd ..

# Upload Models
mkdir models
cd models
mput models\*.php
cd ..

# Upload .htaccess
put HOSTINGER_htaccess_file.txt .htaccess

# Create uploads directory
mkdir uploads

quit
"@ | Out-File -FilePath "ftp_commands.txt" -Encoding ASCII

try {
    Write-Host "Connecting to FTP server..." -ForegroundColor Yellow
    ftp -s:ftp_commands.txt
    
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "    Deployment Complete!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Your website is now live at:" -ForegroundColor Cyan
    Write-Host "https://prolinege.com" -ForegroundColor White
    Write-Host ""
    Write-Host "Admin panel: https://prolinege.com/admin/login.html" -ForegroundColor White
    Write-Host ""
}
catch {
    Write-Host "Error during deployment: $_" -ForegroundColor Red
}
finally {
    # Clean up
    if (Test-Path "ftp_commands.txt") {
        Remove-Item "ftp_commands.txt"
    }
}

Read-Host "Press Enter to continue"