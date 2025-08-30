@echo off
echo Uploading CSS files...

echo user u482576391.prolinege 0993991019Matlab? > ftp_css.txt
echo mkdir css >> ftp_css.txt
echo cd css >> ftp_css.txt
echo put css\admin.css >> ftp_css.txt
echo put css\style.css >> ftp_css.txt
echo quit >> ftp_css.txt

ftp -n -s:ftp_css.txt prolinege.com

del ftp_css.txt

echo.
echo CSS files uploaded!
echo.
pause