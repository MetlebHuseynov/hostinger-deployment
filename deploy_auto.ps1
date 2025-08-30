# Hostinger Automatic FTP Deployment Script
# ========================================

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "    Hostinger Automatic Deployment" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "FTP Server: ftp.prolinege.com" -ForegroundColor Yellow
Write-Host "Username: u482576391.prolinege" -ForegroundColor Yellow
Write-Host "Target Directory: public_html" -ForegroundColor Yellow
Write-Host ""

# FTP credentials from FileZilla guide
$ftpServer = "ftp.prolinege.com"
$ftpUser = "u482576391.prolinege"
$ftpPassword = "0993991019Matlab?"
$ftpPort = 21

Write-Host "Starting deployment..." -ForegroundColor Green
Write-Host ""

# Create FTP command file
$ftpCommands = @"
open $ftpServer $ftpPort
$ftpUser
$ftpPassword
bin
cd public_html
prompt

# Upload main HTML files
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

# Upload .env file
put .env

# Create uploads directory
mkdir uploads

quit
"@

# Save FTP commands to temporary file
$ftpCommandFile = "ftp_commands.txt"
$ftpCommands | Out-File -FilePath $ftpCommandFile -Encoding ASCII

Write-Host "Executing FTP upload..." -ForegroundColor Yellow

# Execute FTP commands
try {
    ftp -s:$ftpCommandFile
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
} catch {
    Write-Host "Error during FTP upload: $_" -ForegroundColor Red
} finally {
    # Clean up temporary file
    if (Test-Path $ftpCommandFile) {
        Remove-Item $ftpCommandFile
    }
}

Write-Host "Press Enter to continue: " -NoNewline -ForegroundColor Yellow
Read-Host