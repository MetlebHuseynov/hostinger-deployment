@echo off
echo ========================================
echo    Hostinger FTP Deployment Script
echo ========================================
echo.
echo FTP Server: ftp://ftp.prolinege.com
echo Username: u482576391.prolinege
echo Password: 0993991019Matlab?
echo Target Directory: public_html
echo.
echo Starting deployment...
echo.

:: Create FTP command file with correct credentials
echo open ftp.prolinege.com > ftp_commands.txt
echo u482576391.prolinege >> ftp_commands.txt
echo 0993991019Matlab? >> ftp_commands.txt
echo bin >> ftp_commands.txt
echo cd public_html >> ftp_commands.txt
echo prompt >> ftp_commands.txt

:: Upload HTML files
echo put index.html >> ftp_commands.txt
echo put about.html >> ftp_commands.txt
echo put categories.html >> ftp_commands.txt
echo put products.html >> ftp_commands.txt
echo put marka.html >> ftp_commands.txt
echo put product.html >> ftp_commands.txt
echo put contact.html >> ftp_commands.txt

:: Create and upload CSS files
echo mkdir css >> ftp_commands.txt
echo cd css >> ftp_commands.txt
echo put css\admin.css >> ftp_commands.txt
echo put css\bootstrap.min.css >> ftp_commands.txt
echo put css\style.css >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

:: Create and upload JS files
echo mkdir js >> ftp_commands.txt
echo cd js >> ftp_commands.txt
echo put js\bootstrap.bundle.min.js >> ftp_commands.txt
echo put js\brands.js >> ftp_commands.txt
echo put js\categories.js >> ftp_commands.txt
echo put js\config.js >> ftp_commands.txt
echo put js\contact.js >> ftp_commands.txt
echo put js\firebase-config.js >> ftp_commands.txt
echo put js\main.js >> ftp_commands.txt
echo put js\marka-detail.js >> ftp_commands.txt
echo put js\product-detail.js >> ftp_commands.txt
echo put js\products.js >> ftp_commands.txt

:: Create admin JS directory
echo mkdir admin >> ftp_commands.txt
echo cd admin >> ftp_commands.txt
echo put js\admin\auth.js >> ftp_commands.txt
echo put js\admin\brands.js >> ftp_commands.txt
echo put js\admin\categories.js >> ftp_commands.txt
echo put js\admin\dashboard.js >> ftp_commands.txt
echo put js\admin\featured-brands.js >> ftp_commands.txt
echo put js\admin\featured-categories.js >> ftp_commands.txt
echo put js\admin\featured-products.js >> ftp_commands.txt
echo put js\admin\main.js >> ftp_commands.txt
echo put js\admin\products.js >> ftp_commands.txt
echo put js\admin\settings.js >> ftp_commands.txt
echo put js\admin\users.js >> ftp_commands.txt
echo cd .. >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

:: Upload Images
echo mkdir images >> ftp_commands.txt
echo cd images >> ftp_commands.txt
echo put images\abb-logo.svg >> ftp_commands.txt
echo put images\about-image.svg >> ftp_commands.txt
echo put images\aluminum-profile.svg >> ftp_commands.txt
echo put images\bosch-logo.svg >> ftp_commands.txt
echo put images\brand-placeholder.svg >> ftp_commands.txt
echo put images\category-placeholder.svg >> ftp_commands.txt
echo put images\danfoss-logo.svg >> ftp_commands.txt
echo put images\hero-image.svg >> ftp_commands.txt
echo put images\honeywell-logo.svg >> ftp_commands.txt
echo put images\logo.svg >> ftp_commands.txt
echo put images\marka-placeholder.svg >> ftp_commands.txt
echo put images\product-placeholder.svg >> ftp_commands.txt
echo put images\rebar.svg >> ftp_commands.txt
echo put images\schneider-logo.svg >> ftp_commands.txt
echo put images\siemens-logo.svg >> ftp_commands.txt
echo put images\steel-pipe.svg >> ftp_commands.txt
echo put images\user-placeholder.svg >> ftp_commands.txt
echo put images\welding-electrode.svg >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

:: Upload Admin files
echo mkdir admin >> ftp_commands.txt
echo cd admin >> ftp_commands.txt
echo put admin\brands.html >> ftp_commands.txt
echo put admin\categories.html >> ftp_commands.txt
echo put admin\dashboard.html >> ftp_commands.txt
echo put admin\featured-brands.html >> ftp_commands.txt
echo put admin\featured-categories.html >> ftp_commands.txt
echo put admin\featured-products.html >> ftp_commands.txt
echo put admin\login.html >> ftp_commands.txt
echo put admin\manual-login.html >> ftp_commands.txt
echo put admin\products.html >> ftp_commands.txt
echo put admin\settings.html >> ftp_commands.txt
echo put admin\test-login.html >> ftp_commands.txt
echo put admin\users.html >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

:: Upload API files
echo mkdir api >> ftp_commands.txt
echo cd api >> ftp_commands.txt
echo put api\auth.php >> ftp_commands.txt
echo put api\categories.php >> ftp_commands.txt
echo put api\dashboard.php >> ftp_commands.txt
echo put api\index.php >> ftp_commands.txt
echo put api\markas.php >> ftp_commands.txt
echo put api\products.php >> ftp_commands.txt
echo put api\users.php >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

:: Upload Config files
echo mkdir config >> ftp_commands.txt
echo cd config >> ftp_commands.txt
echo put config\config.php >> ftp_commands.txt
echo put config\database.php >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

:: Upload Models
echo mkdir models >> ftp_commands.txt
echo cd models >> ftp_commands.txt
echo put models\Category.php >> ftp_commands.txt
echo put models\Marka.php >> ftp_commands.txt
echo put models\Product.php >> ftp_commands.txt
echo put models\User.php >> ftp_commands.txt
echo cd .. >> ftp_commands.txt

:: Upload .htaccess
echo put HOSTINGER_htaccess_file.txt .htaccess >> ftp_commands.txt

:: Create uploads directory
echo mkdir uploads >> ftp_commands.txt

echo quit >> ftp_commands.txt

:: Execute FTP commands
echo Connecting to FTP server...
ftp -s:ftp_commands.txt

:: Clean up
del ftp_commands.txt

echo.
echo ========================================
echo    Deployment Complete!
echo ========================================
echo.
echo Your website is now live at:
echo https://prolinege.com
echo.
echo Admin panel: https://prolinege.com/admin/login.html
echo.
pause