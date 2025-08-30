# Upload debug file to server

$ftpServer = "ftp://prolinege.com"
$username = "u482576391.prolinege"
$password = "0993991019Matlab?"
$remoteDir = "/public_html"

Write-Host "Uploading debug_api.php..." -ForegroundColor Yellow

try {
    $uri = "$ftpServer$remoteDir/debug_api.php"
    $webclient = New-Object System.Net.WebClient
    $webclient.Credentials = New-Object System.Net.NetworkCredential($username, $password)
    $webclient.UploadFile($uri, "debug_api.php")
    Write-Host "✓ debug_api.php uploaded successfully" -ForegroundColor Green
    Write-Host "Access it at: https://prolinege.com/debug_api.php" -ForegroundColor Cyan
}
catch {
    Write-Host "✗ Upload failed: $($_.Exception.Message)" -ForegroundColor Red
}
finally {
    if ($webclient) { $webclient.Dispose() }
}

Pause