@echo off
echo Uploading API files to server...

:: Create FTP commands file
echo open ftp.prolinege.com > ftp_commands_api.txt
echo prolinege >> ftp_commands_api.txt
echo Prolinege2024! >> ftp_commands_api.txt
echo binary >> ftp_commands_api.txt
echo cd public_html >> ftp_commands_api.txt
echo mkdir api >> ftp_commands_api.txt
echo cd api >> ftp_commands_api.txt
echo put api\index.php >> ftp_commands_api.txt
echo put api\products.php >> ftp_commands_api.txt
echo put api\auth.php >> ftp_commands_api.txt
echo quit >> ftp_commands_api.txt

:: Execute FTP commands
ftp -s:ftp_commands_api.txt

:: Clean up
del ftp_commands_api.txt

echo API files uploaded!
echo Access products API at: https://prolinege.com/api/products

pause