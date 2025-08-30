# PowerShell FTP Upload Script with IP Address - Active Mode
$ftpServer = "ftp://88.223.84.24"
$username = "u482576391.prolinege"
$password = "0993991019Matlab?"

# Test connection with IP address using Active Mode
try {
    Write-Host "Testing FTP connection to IP: 88.223.84.24 (Active Mode)..." -ForegroundColor Yellow
    
    # Create FTP request for root directory listing
    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpServer)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($username, $password)
    $ftpRequest.UseBinary = $true
    $ftpRequest.UsePassive = $false  # Active mode
    $ftpRequest.Timeout = 15000
    
    # Get response
    $ftpResponse = $ftpRequest.GetResponse()
    $responseStream = $ftpResponse.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($responseStream)
    $result = $reader.ReadToEnd()
    
    Write-Host "Connection successful with Active Mode!" -ForegroundColor Green
    Write-Host "Root directory contents:" -ForegroundColor Cyan
    Write-Host $result
    
    $reader.Close()
    $ftpResponse.Close()
    
} catch {
    Write-Host "Active Mode Failed: $($_.Exception.Message)" -ForegroundColor Red
    
    # Try with different port
    try {
        Write-Host "Trying port 2121..." -ForegroundColor Yellow
        
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://88.223.84.24:2121")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($username, $password)
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 15000
        
        $ftpResponse = $ftpRequest.GetResponse()
        $responseStream = $ftpResponse.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($responseStream)
        $result = $reader.ReadToEnd()
        
        Write-Host "Connection successful on port 2121!" -ForegroundColor Green
        Write-Host "Directory contents:" -ForegroundColor Cyan
        Write-Host $result
        
        $reader.Close()
        $ftpResponse.Close()
        
    } catch {
        Write-Host "Port 2121 Failed: $($_.Exception.Message)" -ForegroundColor Red
        
        # Try basic FTP command line
        Write-Host "Trying basic FTP command..." -ForegroundColor Yellow
        
        $ftpCommands = @"
open 88.223.84.24
$username
$password
dir
quit
"@
        
        $ftpCommands | ftp -n
    }
}

Write-Host "Press Enter to continue..."
Read-Host