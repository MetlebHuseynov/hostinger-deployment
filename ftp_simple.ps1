# Simple PowerShell FTP Upload Script
$ftpServer = "ftp://ftp.prolinege.com"
$username = "u482576391.prolinege"
$password = "0993991019Matlab?"

# Test connection with root directory first
try {
    Write-Host "Testing FTP connection to root..." -ForegroundColor Yellow
    
    # Create FTP request for root directory listing
    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpServer)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($username, $password)
    $ftpRequest.UseBinary = $true
    $ftpRequest.UsePassive = $true
    
    # Get response
    $ftpResponse = $ftpRequest.GetResponse()
    $responseStream = $ftpResponse.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($responseStream)
    $result = $reader.ReadToEnd()
    
    Write-Host "Root connection successful!" -ForegroundColor Green
    Write-Host "Root directory contents:" -ForegroundColor Cyan
    Write-Host $result
    
    $reader.Close()
    $ftpResponse.Close()
    
    # Try public_html directory
    Write-Host "Testing public_html directory..." -ForegroundColor Yellow
    
    $ftpRequest = [System.Net.FtpWebRequest]::Create("$ftpServer/public_html")
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($username, $password)
    $ftpRequest.UseBinary = $true
    $ftpRequest.UsePassive = $true
    
    $ftpResponse = $ftpRequest.GetResponse()
    $responseStream = $ftpResponse.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($responseStream)
    $result = $reader.ReadToEnd()
    
    Write-Host "public_html directory contents:" -ForegroundColor Cyan
    Write-Host $result
    
    $reader.Close()
    $ftpResponse.Close()
    
    # Upload index.html to public_html
    Write-Host "Uploading index.html to public_html..." -ForegroundColor Yellow
    
    $localFile = "index.html"
    if (Test-Path $localFile) {
        $ftpRequest = [System.Net.FtpWebRequest]::Create("$ftpServer/public_html/index.html")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($username, $password)
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        
        $fileContent = [System.IO.File]::ReadAllBytes($localFile)
        $ftpRequest.ContentLength = $fileContent.Length
        
        $requestStream = $ftpRequest.GetRequestStream()
        $requestStream.Write($fileContent, 0, $fileContent.Length)
        $requestStream.Close()
        
        $ftpResponse = $ftpRequest.GetResponse()
        Write-Host "Upload successful! Status: $($ftpResponse.StatusDescription)" -ForegroundColor Green
        $ftpResponse.Close()
    } else {
        Write-Host "index.html not found!" -ForegroundColor Red
    }
    
} catch {
    Write-Host "FTP Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "Press Enter to continue..."
Read-Host