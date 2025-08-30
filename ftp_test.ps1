# PowerShell FTP Upload Script
$ftpServer = "ftp://ftp.prolinege.com"
$username = "u482576391.prolinege"
$password = "0993991019Matlab?"
$remotePath = "/home/u482576391/domains/prolinege.com/public_html"

# Test connection first
try {
    Write-Host "Testing FTP connection..." -ForegroundColor Yellow
    
    # Create FTP request for directory listing
    $ftpRequest = [System.Net.FtpWebRequest]::Create("$ftpServer$remotePath")
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($username, $password)
    $ftpRequest.UseBinary = $true
    $ftpRequest.UsePassive = $true
    
    # Get response
    $ftpResponse = $ftpRequest.GetResponse()
    $responseStream = $ftpResponse.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($responseStream)
    $result = $reader.ReadToEnd()
    
    Write-Host "Connection successful!" -ForegroundColor Green
    Write-Host "Directory contents:" -ForegroundColor Cyan
    Write-Host $result
    
    $reader.Close()
    $ftpResponse.Close()
    
    # Upload index.html as test
    Write-Host "Uploading index.html..." -ForegroundColor Yellow
    
    $localFile = "index.html"
    if (Test-Path $localFile) {
        $ftpRequest = [System.Net.FtpWebRequest]::Create("$ftpServer$remotePath/index.html")
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
    Write-Host "Full error: $($_.Exception)" -ForegroundColor Red
}

Write-Host "Press Enter to continue..."
Read-Host