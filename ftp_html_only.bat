@echo off
echo Uploading remaining HTML files only...
echo.

(echo open ftp.prolinege.com
echo user u482576391.prolinege 0993991019Matlab?
echo binary
echo put categories.html
echo put products.html
echo put marka.html
echo put product.html
echo quit
) | ftp -n -v

echo.
echo HTML files uploaded.
pause