@echo off
echo Uploading JavaScript files to server...

echo open prolinege.com > temp_js_upload.txt
echo u1194-ixqhqhqhqh >> temp_js_upload.txt
echo Prolinege2024! >> temp_js_upload.txt
echo binary >> temp_js_upload.txt
echo cd public_html >> temp_js_upload.txt
echo cd js >> temp_js_upload.txt
echo cd admin >> temp_js_upload.txt
echo put js\admin\products.js >> temp_js_upload.txt
echo put js\admin\main.js >> temp_js_upload.txt
echo quit >> temp_js_upload.txt

ftp -s:temp_js_upload.txt

echo JavaScript files uploaded!
del temp_js_upload.txt
pause