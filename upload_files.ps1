# PowerShell FTP Upload Script

$ftpServer = "ftp://prolinege.com"
$username = "u482576391.prolinege"
$password = "0993991019Matlab?"
$remoteDir = "/public_html"

Write-Host "Starting FTP upload to Hostinger..." -ForegroundColor Green

# Function to upload file via FTP
function Upload-File {
    param(
        [string]$localFile,
        [string]$remoteFile
    )
    
    try {
        $uri = "$ftpServer$remoteDir/$remoteFile"
        $webclient = New-Object System.Net.WebClient
        $webclient.Credentials = New-Object System.Net.NetworkCredential($username, $password)
        $webclient.UploadFile($uri, $localFile)
        Write-Host "Uploaded: $remoteFile" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "Failed to upload: $remoteFile - $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
    finally {
        if ($webclient) { $webclient.Dispose() }
    }
}

# Upload main HTML files
$htmlFiles = @(
    "index.html",
    "about.html", 
    "categories.html",
    "products.html",
    "marka.html",
    "product.html",
    "contact.html"
)

foreach ($file in $htmlFiles) {
    if (Test-Path $file) {
        Upload-File $file $file
    }
}

# Upload API files
Write-Host "Uploading API files..." -ForegroundColor Yellow
$apiFiles = Get-ChildItem "api\*.php"
foreach ($file in $apiFiles) {
    Upload-File $file.FullName "api/$($file.Name)"
}

# Upload JS files
Write-Host "Uploading JS files..." -ForegroundColor Yellow
$jsFiles = Get-ChildItem "js\*.js"
foreach ($file in $jsFiles) {
    Upload-File $file.FullName "js/$($file.Name)"
}

# Upload admin JS files
$adminJsFiles = Get-ChildItem "js\admin\*.js"
foreach ($file in $adminJsFiles) {
    Upload-File $file.FullName "js/admin/$($file.Name)"
}

# Upload config files
Write-Host "Uploading config files..." -ForegroundColor Yellow
$configFiles = Get-ChildItem "config\*.php"
foreach ($file in $configFiles) {
    Upload-File $file.FullName "config/$($file.Name)"
}

# Upload model files
Write-Host "Uploading model files..." -ForegroundColor Yellow
$modelFiles = Get-ChildItem "models\*.php"
foreach ($file in $modelFiles) {
    Upload-File $file.FullName "models/$($file.Name)"
}

# Upload .htaccess
if (Test-Path "HOSTINGER_htaccess_file.txt") {
    Upload-File "HOSTINGER_htaccess_file.txt" ".htaccess"
}

Write-Host "Upload Complete!" -ForegroundColor Cyan
Write-Host "Website: https://prolinege.com" -ForegroundColor Green
Write-Host "Admin: https://prolinege.com/admin/login.html" -ForegroundColor Green

Pause