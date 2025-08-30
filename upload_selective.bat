@echo off
echo Selective file upload - only modified files...

REM Create FTP commands file
echo user u482576391.prolinege 0993991019Matlab? > ftp_commands.txt

REM Upload only the critical modified files
echo put fix_products_js.php >> ftp_commands.txt

REM Upload the corrected JavaScript file
echo mkdir js >> ftp_commands.txt
echo cd js >> ftp_commands.txt
echo mkdir admin >> ftp_commands.txt
echo cd admin >> ftp_commands.txt
echo put js\admin\products.js >> ftp_commands.txt
echo cd .. >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

REM Upload any other modified files if needed
echo put js\admin\main.js >> ftp_commands.txt

echo quit >> ftp_commands.txt

REM Execute FTP upload
ftp -n -s:ftp_commands.txt prolinege.com

REM Clean up
del ftp_commands.txt

echo.
echo Selective upload completed!
echo Modified files uploaded:
echo - fix_products_js.php
echo - js/admin/products.js
echo - js/admin/main.js
echo.
echo Access fix script at: https://prolinege.com/fix_products_js.php
echo.
pause