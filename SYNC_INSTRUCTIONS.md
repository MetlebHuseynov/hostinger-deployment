# 🔄 Hostinger Database Synchronization Instructions

## 📋 Prerequisites:
1. Access to Hostinger cPanel
2. phpMyAdmin access
3. Generated sync file: hostinger_sync.sql

## 🔧 Step-by-Step Synchronization:

### Step 1: Backup Current Hostinger Data
1. Login to Hostinger cPanel
2. Open phpMyAdmin
3. Select database: u482576391_yUuh0
4. Go to Export tab
5. Select tables: categories, markas, products
6. Download backup file

### Step 2: Apply Local Data to Hostinger
1. In phpMyAdmin, go to Import tab
2. Choose file: hostinger_sync.sql
3. Click 'Go' to execute
4. Verify data was imported correctly

### Step 3: Verify Synchronization
1. Check record counts in each table
2. Verify data integrity
3. Test application functionality

## 📊 Current Local Data Summary:
- This sync file contains data from your local SQLite database
- Tables included: categories, markas, products
- Generated: 2025-08-30T20:50:50.386Z

## ⚠️ Important Notes:
- This will REPLACE all data in Hostinger tables
- Make sure to backup before applying
- Test in a staging environment first if possible
- Foreign key relationships will be maintained

## 🔄 Reverse Sync (Hostinger to Local):
To sync from Hostinger back to local:
1. Export data from Hostinger phpMyAdmin
2. Convert MySQL dump to SQLite format
3. Import to local database

## 🆘 Troubleshooting:
- If import fails, check for foreign key constraints
- Verify table structures match
- Check for special characters in data
- Ensure proper encoding (UTF-8)
