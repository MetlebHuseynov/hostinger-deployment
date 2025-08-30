# Hostinger FTP Deployment Script
# PowerShell script for deploying to Hostinger via FTP

param(
    [string]$FtpServer = "prolinege.com",
    [string]$Username = "u482576391.prolinege",
    [string]$Password = "0993991019Matlab?",
    [string]$RemoteDir = "/public_html"
)

Write-Host "Starting deployment to Hostinger..." -ForegroundColor Green

# Function to upload file via FTP
function Upload-File {
    param(
        [string]$LocalPath,
        [string]$RemotePath,
        [string]$Server,
        [string]$User,
        [string]$Pass
    )
    
    try {
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$Server$RemotePath")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($User, $Pass)
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        
        $fileContent = [System.IO.File]::ReadAllBytes($LocalPath)
        $ftpRequest.ContentLength = $fileContent.Length
        
        $requestStream = $ftpRequest.GetRequestStream()
        $requestStream.Write($fileContent, 0, $fileContent.Length)
        $requestStream.Close()
        
        $response = $ftpRequest.GetResponse()
        Write-Host "Uploaded: $LocalPath -> $RemotePath" -ForegroundColor Yellow
        $response.Close()
        return $true
    }
    catch {
        Write-Host "Error uploading $LocalPath : $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Function to create directory via FTP
function Create-FtpDirectory {
    param(
        [string]$RemotePath,
        [string]$Server,
        [string]$User,
        [string]$Pass
    )
    
    try {
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$Server$RemotePath")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($User, $Pass)
        
        $response = $ftpRequest.GetResponse()
        Write-Host "Created directory: $RemotePath" -ForegroundColor Cyan
        $response.Close()
        return $true
    }
    catch {
        # Directory might already exist, continue
        return $false
    }
}

# Main deployment logic
try {
    # Create main directories
    Create-FtpDirectory "$RemoteDir/css" $FtpServer $Username $Password
    Create-FtpDirectory "$RemoteDir/js" $FtpServer $Username $Password
    Create-FtpDirectory "$RemoteDir/images" $FtpServer $Username $Password
    Create-FtpDirectory "$RemoteDir/admin" $FtpServer $Username $Password
    Create-FtpDirectory "$RemoteDir/api" $FtpServer $Username $Password
    Create-FtpDirectory "$RemoteDir/config" $FtpServer $Username $Password
    Create-FtpDirectory "$RemoteDir/models" $FtpServer $Username $Password
    Create-FtpDirectory "$RemoteDir/uploads" $FtpServer $Username $Password
    
    # Upload HTML files
    $htmlFiles = @("index.html", "about.html", "categories.html", "products.html", "marka.html", "product.html", "contact.html")
    foreach ($file in $htmlFiles) {
        if (Test-Path $file) {
            Upload-File $file "$RemoteDir/$file" $FtpServer $Username $Password
        }
    }
    
    # Upload CSS files
    Get-ChildItem -Path "css" -File | ForEach-Object {
        Upload-File $_.FullName "$RemoteDir/css/$($_.Name)" $FtpServer $Username $Password
    }
    
    # Upload JS files recursively
    Get-ChildItem -Path "js" -Recurse -File | ForEach-Object {
        $relativePath = $_.FullName.Substring((Get-Location).Path.Length + 1).Replace("\", "/")
        Upload-File $_.FullName "$RemoteDir/$relativePath" $FtpServer $Username $Password
    }
    
    # Upload Images
    Get-ChildItem -Path "images" -File | ForEach-Object {
        Upload-File $_.FullName "$RemoteDir/images/$($_.Name)" $FtpServer $Username $Password
    }
    
    # Upload Admin files
    Get-ChildItem -Path "admin" -Recurse -File | ForEach-Object {
        $relativePath = $_.FullName.Substring((Get-Location).Path.Length + 1).Replace("\", "/")
        Upload-File $_.FullName "$RemoteDir/$relativePath" $FtpServer $Username $Password
    }
    
    # Upload API files
    Get-ChildItem -Path "api" -File | ForEach-Object {
        Upload-File $_.FullName "$RemoteDir/api/$($_.Name)" $FtpServer $Username $Password
    }
    
    # Upload Config files
    Get-ChildItem -Path "config" -File | ForEach-Object {
        Upload-File $_.FullName "$RemoteDir/config/$($_.Name)" $FtpServer $Username $Password
    }
    
    # Upload Models
    Get-ChildItem -Path "models" -File | ForEach-Object {
        Upload-File $_.FullName "$RemoteDir/models/$($_.Name)" $FtpServer $Username $Password
    }
    
    # Upload .htaccess
    if (Test-Path "HOSTINGER_htaccess_file.txt") {
        Upload-File "HOSTINGER_htaccess_file.txt" "$RemoteDir/.htaccess" $FtpServer $Username $Password
    }
    
    # Upload .env file
    if (Test-Path ".env") {
        Upload-File ".env" "$RemoteDir/.env" $FtpServer $Username $Password
    }
    
    Write-Host "\nDeployment completed successfully!" -ForegroundColor Green
    Write-Host "Website: https://prolinege.com" -ForegroundColor Cyan
    Write-Host "Admin Panel: https://prolinege.com/admin/login.html" -ForegroundColor Cyan
}
catch {
    Write-Host "Deployment failed: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}