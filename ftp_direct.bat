@echo off
echo open ftp.prolinege.com > ftp_commands.txt
echo u482576391.prolinege >> ftp_commands.txt
echo 0993991019Matlab? >> ftp_commands.txt
echo binary >> ftp_commands.txt
echo cd /home/u482576391/domains/prolinege.com/public_html >> ftp_commands.txt
echo put index.html >> ftp_commands.txt
echo put about.html >> ftp_commands.txt
echo put categories.html >> ftp_commands.txt
echo put products.html >> ftp_commands.txt
echo put marka.html >> ftp_commands.txt
echo put product.html >> ftp_commands.txt
echo put contact.html >> ftp_commands.txt
echo quit >> ftp_commands.txt

ftp -s:ftp_commands.txt
del ftp_commands.txt
pause