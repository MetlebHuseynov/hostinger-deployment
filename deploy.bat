@echo off
echo ========================================
echo    Hostinger FTP Deployment Script
echo ========================================
echo.
echo FTP Server: ftp://prolinege.com
echo Username: u482576391
echo Target Directory: public_html
echo.
echo Starting deployment...
echo.

:: Create FTP command file
echo open ftp.prolinege.com > ftp_commands.txt
echo u482576391 >> ftp_commands.txt
echo 0993991019Metleb >> ftp_commands.txt
echo bin >> ftp_commands.txt
echo cd public_html >> ftp_commands.txt
echo prompt >> ftp_commands.txt

:: Upload HTML files
echo put index.html >> ftp_commands.txt
echo put about.html >> ftp_commands.txt
echo put categories.html >> ftp_commands.txt
echo put products.html >> ftp_commands.txt
echo put marka.html >> ftp_commands.txt
echo put product.html >> ftp_commands.txt
echo put contact.html >> ftp_commands.txt

:: Upload CSS files
echo mkdir css >> ftp_commands.txt
echo cd css >> ftp_commands.txt
echo mput css\*.css >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

:: Upload JS files
echo mkdir js >> ftp_commands.txt
echo cd js >> ftp_commands.txt
echo mput js\*.js >> ftp_commands.txt
echo mkdir admin >> ftp_commands.txt
echo cd admin >> ftp_commands.txt
echo mput js\admin\*.js >> ftp_commands.txt
echo cd .. >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

:: Upload Images
echo mkdir images >> ftp_commands.txt
echo cd images >> ftp_commands.txt
echo mput images\*.svg >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

:: Upload Admin files
echo mkdir admin >> ftp_commands.txt
echo cd admin >> ftp_commands.txt
echo mput admin\*.html >> ftp_commands.txt
echo mkdir js >> ftp_commands.txt
echo cd js >> ftp_commands.txt
echo mput admin\js\*.js >> ftp_commands.txt
echo cd .. >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

:: Upload API files
echo mkdir api >> ftp_commands.txt
echo cd api >> ftp_commands.txt
echo mput api\*.php >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

:: Upload Config files
echo mkdir config >> ftp_commands.txt
echo cd config >> ftp_commands.txt
echo mput config\*.php >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

:: Upload Models
echo mkdir models >> ftp_commands.txt
echo cd models >> ftp_commands.txt
echo mput models\*.php >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

:: Upload .htaccess and .env
echo put .htaccess >> ftp_commands.txt
echo put .env >> ftp_commands.txt

:: Create uploads directory
echo mkdir uploads >> ftp_commands.txt

echo quit >> ftp_commands.txt

:: Execute FTP commands
echo Connecting to FTP server...
ftp -s:ftp_commands.txt

:: Clean up
del ftp_commands.txt

echo.
echo ========================================
echo    Deployment Complete!
echo ========================================
echo.
echo Your website is now live at:
echo https://prolinege.com
echo.
echo Admin panel: https://prolinege.com/admin/login.html
echo.
pause