@echo off
echo open prolinege.com > temp_js_upload.txt
echo u482576391_Md1PB >> temp_js_upload.txt
echo 12345678Matlab! >> temp_js_upload.txt
echo binary >> temp_js_upload.txt
echo cd public_html >> temp_js_upload.txt
echo put js/admin/products.js js/admin/products.js >> temp_js_upload.txt
echo quit >> temp_js_upload.txt
ftp -s:temp_js_upload.txt
del temp_js_upload.txt
pause