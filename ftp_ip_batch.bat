@echo off
echo open 88.223.84.24 > ftp_ip_commands.txt
echo u482576391.prolinege >> ftp_ip_commands.txt
echo 0993991019Matlab? >> ftp_ip_commands.txt
echo binary >> ftp_ip_commands.txt
echo dir >> ftp_ip_commands.txt
echo cd public_html >> ftp_ip_commands.txt
echo dir >> ftp_ip_commands.txt
echo put index.html >> ftp_ip_commands.txt
echo quit >> ftp_ip_commands.txt

ftp -s:ftp_ip_commands.txt
del ftp_ip_commands.txt
pause