@echo off
echo Uploading admin JS files...

echo user u482576391.prolinege 0993991019Matlab? > ftp_js.txt
echo cd admin >> ftp_js.txt
echo cd js >> ftp_js.txt
echo put js\admin\auth.js >> ftp_js.txt
echo put js\admin\main.js >> ftp_js.txt
echo put js\admin\dashboard.js >> ftp_js.txt
echo put js\admin\products.js >> ftp_js.txt
echo put js\admin\categories.js >> ftp_js.txt
echo put js\admin\brands.js >> ftp_js.txt
echo put js\admin\users.js >> ftp_js.txt
echo put js\admin\settings.js >> ftp_js.txt
echo put js\admin\featured-products.js >> ftp_js.txt
echo put js\admin\featured-categories.js >> ftp_js.txt
echo put js\admin\featured-brands.js >> ftp_js.txt
echo quit >> ftp_js.txt

ftp -n -s:ftp_js.txt prolinege.com

del ftp_js.txt

echo.
echo Admin JS files uploaded!
echo.
pause