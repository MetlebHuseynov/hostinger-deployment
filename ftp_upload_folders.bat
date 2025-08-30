@echo off
echo Uploading CSS, JS and remaining HTML files...
echo.

(echo open ftp.prolinege.com
echo user u482576391.prolinege 0993991019Matlab?
echo binary
echo put categories.html
echo put products.html
echo put marka.html
echo put product.html
echo mkdir css
echo cd css
echo put css\style.css
echo put css\bootstrap.min.css
echo put css\admin.css
echo cd ..
echo mkdir js
echo cd js
echo put js\main.js
echo put js\config.js
echo put js\bootstrap.bundle.min.js
echo put js\brands.js
echo put js\categories.js
echo put js\contact.js
echo put js\products.js
echo put js\marka-detail.js
echo put js\product-detail.js
echo cd ..
echo mkdir images
echo cd images
echo put images\logo.svg
echo put images\hero-image.svg
echo put images\about-image.svg
echo quit
) | ftp -n -v

echo.
echo Upload completed.
pause