# FTP Test with New Credentials
# Server: ftp.prolinege.com
# Path: /home/u482576391/domains/prolinege.com/public_html
# User: u482576391.prolinege

Write-Host "Testing FTP connection with new credentials..." -ForegroundColor Green

try {
    # FTP server details
    $ftpServer = "ftp://ftp.prolinege.com"
    $ftpUser = "u482576391.prolinege"
    $ftpPassword = "0993991019Matlab?"
    $ftpPath = "/home/u482576391/domains/prolinege.com/public_html"
    
    Write-Host "Server: $ftpServer" -ForegroundColor Yellow
    Write-Host "User: $ftpUser" -ForegroundColor Yellow
    Write-Host "Path: $ftpPath" -ForegroundColor Yellow
    
    # Test 1: List root directory
    Write-Host "\n=== Test 1: Listing root directory ===" -ForegroundColor Cyan
    $request = [System.Net.FtpWebRequest]::Create($ftpServer + "/")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request.UsePassive = $true
    $request.Timeout = 30000
    
    $response = $request.GetResponse()
    $stream = $response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($stream)
    $result = $reader.ReadToEnd()
    
    Write-Host "Root directory contents:" -ForegroundColor Green
    Write-Host $result
    
    $reader.Close()
    $stream.Close()
    $response.Close()
    
    # Test 2: List target directory
    Write-Host "\n=== Test 2: Listing target directory ===" -ForegroundColor Cyan
    $request2 = [System.Net.FtpWebRequest]::Create($ftpServer + $ftpPath)
    $request2.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $request2.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
    $request2.UsePassive = $true
    $request2.Timeout = 30000
    
    $response2 = $request2.GetResponse()
    $stream2 = $response2.GetResponseStream()
    $reader2 = New-Object System.IO.StreamReader($stream2)
    $result2 = $reader2.ReadToEnd()
    
    Write-Host "Target directory contents:" -ForegroundColor Green
    Write-Host $result2
    
    $reader2.Close()
    $stream2.Close()
    $response2.Close()
    
    # Test 3: Upload index.html
    Write-Host "\n=== Test 3: Uploading index.html ===" -ForegroundColor Cyan
    $localFile = "index.html"
    
    if (Test-Path $localFile) {
        $request3 = [System.Net.FtpWebRequest]::Create($ftpServer + $ftpPath + "/index.html")
        $request3.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $request3.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $request3.UsePassive = $true
        $request3.Timeout = 60000
        
        $fileContent = [System.IO.File]::ReadAllBytes($localFile)
        $request3.ContentLength = $fileContent.Length
        
        $requestStream = $request3.GetRequestStream()
        $requestStream.Write($fileContent, 0, $fileContent.Length)
        $requestStream.Close()
        
        $response3 = $request3.GetResponse()
        Write-Host "Upload successful! Status: $($response3.StatusDescription)" -ForegroundColor Green
        $response3.Close()
    } else {
        Write-Host "index.html file not found!" -ForegroundColor Red
    }
    
    Write-Host "\n=== FTP Test Completed Successfully! ===" -ForegroundColor Green
    
} catch {
    Write-Host "\nFTP Error occurred:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    
    if ($_.Exception.InnerException) {
        Write-Host "Inner Exception: $($_.Exception.InnerException.Message)" -ForegroundColor Red
    }
    
    # Additional error details
    if ($_.Exception -is [System.Net.WebException]) {
        $webEx = $_.Exception
        if ($webEx.Response) {
            $response = $webEx.Response
            Write-Host "FTP Response Status: $($response.StatusDescription)" -ForegroundColor Red
        }
    }
}

Write-Host "\nPress any key to continue..." -ForegroundColor Yellow
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")