@echo off
echo Testing FTP with new credentials...
echo Server: ftp.prolinege.com
echo User: u482576391.prolinege
echo.

(echo open ftp.prolinege.com
echo user u482576391.prolinege 0993991019Matlab?
echo binary
echo pwd
echo dir
echo cd public_html
echo pwd
echo dir
echo put index.html
echo put about.html
echo put contact.html
echo put categories.html
echo put products.html
echo put marka.html
echo put product.html
echo quit
) | ftp -n -v

echo.
echo FTP operation completed.
pause