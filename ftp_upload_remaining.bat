@echo off
echo Uploading remaining HTML files...
echo.

(echo open ftp.prolinege.com
echo user u482576391.prolinege 0993991019Matlab?
echo binary
echo cd public_html
echo put categories.html
echo put products.html
echo put marka.html
echo put product.html
echo quit
) | ftp -n -v

echo.
echo Remaining files uploaded.
pause