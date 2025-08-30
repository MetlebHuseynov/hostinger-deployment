const sqlite3 = require('sqlite3').verbose();
const fs = require('fs');
const path = require('path');

class HostingerExporter {
    constructor() {
        this.localDb = null;
        this.outputFile = 'hostinger_sync.sql';
    }

    // Connect to local SQLite database
    async connectLocal() {
        return new Promise((resolve, reject) => {
            const dbPath = path.join(__dirname, 'database', 'proline.db');
            this.localDb = new sqlite3.Database(dbPath, (err) => {
                if (err) {
                    console.error('❌ Local SQLite connection error:', err.message);
                    reject(err);
                } else {
                    console.log('✅ Connected to local SQLite database');
                    resolve();
                }
            });
        });
    }

    // Get data from local SQLite
    async getLocalData(table) {
        return new Promise((resolve, reject) => {
            this.localDb.all(`SELECT * FROM ${table}`, [], (err, rows) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(rows);
                }
            });
        });
    }

    // Convert SQLite data to MySQL INSERT statements
    generateMySQLInserts(table, data) {
        if (!data || data.length === 0) {
            return `-- No data found for table ${table}\n`;
        }

        let sql = `\n-- Data for table ${table}\n`;
        sql += `DELETE FROM ${table};\n`;
        
        const columns = Object.keys(data[0]);
        
        for (const row of data) {
            const values = columns.map(col => {
                const value = row[col];
                if (value === null || value === undefined) {
                    return 'NULL';
                } else if (typeof value === 'string') {
                    return `'${value.replace(/'/g, "\\'")}'`;
                } else if (typeof value === 'number') {
                    return value;
                } else {
                    return `'${String(value).replace(/'/g, "\\'")}'`;
                }
            }).join(', ');
            
            sql += `INSERT INTO ${table} (${columns.join(', ')}) VALUES (${values});\n`;
        }
        
        return sql;
    }

    // Generate complete MySQL sync file
    async generateSyncFile() {
        console.log('\n📝 GENERATING HOSTINGER SYNC FILE');
        console.log('==================================');

        let sqlContent = '';
        
        // Header
        sqlContent += '-- =============================================\n';
        sqlContent += '-- Hostinger Database Synchronization Script\n';
        sqlContent += `-- Generated: ${new Date().toISOString()}\n`;
        sqlContent += '-- =============================================\n\n';
        
        sqlContent += 'SET FOREIGN_KEY_CHECKS = 0;\n\n';

        const tables = ['categories', 'markas', 'products'];

        for (const table of tables) {
            try {
                console.log(`📊 Processing ${table}...`);
                const data = await this.getLocalData(table);
                console.log(`📥 Found ${data.length} records in ${table}`);
                
                sqlContent += this.generateMySQLInserts(table, data);
            } catch (error) {
                console.error(`❌ Error processing ${table}:`, error.message);
                sqlContent += `-- Error processing ${table}: ${error.message}\n`;
            }
        }
        
        sqlContent += '\nSET FOREIGN_KEY_CHECKS = 1;\n';
        sqlContent += '\n-- Synchronization completed\n';

        // Write to file
        fs.writeFileSync(this.outputFile, sqlContent, 'utf8');
        console.log(`\n✅ Sync file generated: ${this.outputFile}`);
        
        return this.outputFile;
    }

    // Generate backup of current Hostinger data (template)
    generateHostingerBackupTemplate() {
        const backupContent = `-- =============================================
-- Hostinger Database Backup Template
-- Use this in phpMyAdmin to backup current data
-- =============================================

-- Backup Categories
SELECT 'Categories Backup:' as info;
SELECT * FROM categories;

-- Backup Markas
SELECT 'Markas Backup:' as info;
SELECT * FROM markas;

-- Backup Products
SELECT 'Products Backup:' as info;
SELECT p.*, c.name as category_name, m.name as marka_name 
FROM products p 
LEFT JOIN categories c ON p.category_id = c.id 
LEFT JOIN markas m ON p.marka_id = m.id;

-- Export as SQL (use phpMyAdmin Export feature)
-- 1. Go to phpMyAdmin
-- 2. Select your database
-- 3. Click Export tab
-- 4. Choose 'Custom' export method
-- 5. Select tables: categories, markas, products
-- 6. Choose SQL format
-- 7. Download the backup file
`;
        
        fs.writeFileSync('hostinger_backup_template.sql', backupContent, 'utf8');
        console.log('✅ Backup template generated: hostinger_backup_template.sql');
    }

    // Generate instructions for manual sync
    generateSyncInstructions() {
        const instructions = `# 🔄 Hostinger Database Synchronization Instructions

## 📋 Prerequisites:
1. Access to Hostinger cPanel
2. phpMyAdmin access
3. Generated sync file: ${this.outputFile}

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
2. Choose file: ${this.outputFile}
3. Click 'Go' to execute
4. Verify data was imported correctly

### Step 3: Verify Synchronization
1. Check record counts in each table
2. Verify data integrity
3. Test application functionality

## 📊 Current Local Data Summary:
- This sync file contains data from your local SQLite database
- Tables included: categories, markas, products
- Generated: ${new Date().toISOString()}

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
`;
        
        fs.writeFileSync('SYNC_INSTRUCTIONS.md', instructions, 'utf8');
        console.log('✅ Sync instructions generated: SYNC_INSTRUCTIONS.md');
    }

    // Close connection
    close() {
        if (this.localDb) {
            this.localDb.close();
            console.log('🔒 Local database connection closed');
        }
    }
}

// Main function
async function main() {
    const exporter = new HostingerExporter();

    try {
        console.log('🚀 HOSTINGER EXPORT TOOL');
        console.log('=========================\n');

        await exporter.connectLocal();
        
        // Generate sync file
        await exporter.generateSyncFile();
        
        // Generate backup template
        exporter.generateHostingerBackupTemplate();
        
        // Generate instructions
        exporter.generateSyncInstructions();
        
        console.log('\n🎯 EXPORT COMPLETED!');
        console.log('Files generated:');
        console.log('  - hostinger_sync.sql (import this to Hostinger)');
        console.log('  - hostinger_backup_template.sql (backup template)');
        console.log('  - SYNC_INSTRUCTIONS.md (detailed instructions)');
        
    } catch (error) {
        console.error('❌ Export failed:', error.message);
    } finally {
        exporter.close();
    }
}

// Run the script
if (require.main === module) {
    main();
}

module.exports = HostingerExporter;