const sqlite3 = require('sqlite3').verbose();
const fs = require('fs');
const path = require('path');

// Database paths
const LOCAL_DB_PATH = path.join(__dirname, 'database', 'proline.db');
const HOSTINGER_SQL_FILE = path.join(__dirname, 'hostinger_data.sql');

class HostingerImporter {
    constructor() {
        this.localDb = null;
    }

    // Connect to local SQLite database
    async connectLocal() {
        return new Promise((resolve, reject) => {
            this.localDb = new sqlite3.Database(LOCAL_DB_PATH, (err) => {
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

    // Parse SQL file and extract INSERT statements
    parseSqlFile(sqlContent) {
        const lines = sqlContent.split('\n');
        const insertStatements = [];
        
        for (const line of lines) {
            const trimmedLine = line.trim();
            if (trimmedLine.startsWith('INSERT INTO')) {
                insertStatements.push(trimmedLine);
            }
        }
        
        return insertStatements;
    }

    // Convert MySQL INSERT to SQLite compatible format
    convertMySQLToSQLite(mysqlInsert) {
        // Replace MySQL specific syntax with SQLite compatible
        let sqliteInsert = mysqlInsert;
        
        // Handle AUTO_INCREMENT and other MySQL specific features
        sqliteInsert = sqliteInsert.replace(/AUTO_INCREMENT/gi, '');
        sqliteInsert = sqliteInsert.replace(/ENGINE=InnoDB/gi, '');
        sqliteInsert = sqliteInsert.replace(/DEFAULT CHARSET=utf8/gi, '');
        
        return sqliteInsert;
    }

    // Clear existing data from tables
    async clearTables() {
        const tables = ['products', 'categories', 'markas'];
        
        for (const table of tables) {
            await new Promise((resolve, reject) => {
                this.localDb.run(`DELETE FROM ${table}`, (err) => {
                    if (err) {
                        console.error(`❌ Error clearing ${table}:`, err.message);
                        reject(err);
                    } else {
                        console.log(`🗑️ Cleared ${table} table`);
                        resolve();
                    }
                });
            });
        }
    }

    // Execute INSERT statements
    async executeInserts(insertStatements) {
        let successCount = 0;
        let errorCount = 0;

        for (const statement of insertStatements) {
            const sqliteStatement = this.convertMySQLToSQLite(statement);
            
            await new Promise((resolve) => {
                this.localDb.run(sqliteStatement, (err) => {
                    if (err) {
                        console.error('❌ Insert error:', err.message);
                        console.error('Statement:', sqliteStatement);
                        errorCount++;
                    } else {
                        successCount++;
                    }
                    resolve();
                });
            });
        }

        console.log(`✅ Successfully imported ${successCount} records`);
        if (errorCount > 0) {
            console.log(`❌ Failed to import ${errorCount} records`);
        }
    }

    // Get record counts from local database
    async getRecordCounts() {
        const tables = ['categories', 'markas', 'products'];
        const counts = {};

        for (const table of tables) {
            await new Promise((resolve, reject) => {
                this.localDb.get(`SELECT COUNT(*) as count FROM ${table}`, (err, row) => {
                    if (err) {
                        console.error(`❌ Error counting ${table}:`, err.message);
                        counts[table] = 0;
                    } else {
                        counts[table] = row.count;
                    }
                    resolve();
                });
            });
        }

        return counts;
    }

    // Main import function
    async importFromHostinger() {
        try {
            console.log('🚀 HOSTINGER TO LOCAL IMPORT');
            console.log('==============================');

            // Check if Hostinger SQL file exists
            if (!fs.existsSync(HOSTINGER_SQL_FILE)) {
                console.error('❌ Hostinger SQL file not found:', HOSTINGER_SQL_FILE);
                console.log('📝 Please create hostinger_data.sql file with exported data from Hostinger phpMyAdmin');
                return;
            }

            // Connect to local database
            await this.connectLocal();

            // Read and parse SQL file
            console.log('📖 Reading Hostinger SQL file...');
            const sqlContent = fs.readFileSync(HOSTINGER_SQL_FILE, 'utf8');
            const insertStatements = this.parseSqlFile(sqlContent);
            
            console.log(`📊 Found ${insertStatements.length} INSERT statements`);

            if (insertStatements.length === 0) {
                console.log('⚠️ No INSERT statements found in SQL file');
                return;
            }

            // Clear existing data
            console.log('🗑️ Clearing existing local data...');
            await this.clearTables();

            // Import data
            console.log('📥 Importing data to local database...');
            await this.executeInserts(insertStatements);

            // Show final counts
            console.log('\n📊 IMPORT SUMMARY');
            console.log('==================');
            const counts = await this.getRecordCounts();
            
            for (const [table, count] of Object.entries(counts)) {
                console.log(`${table}: ${count} records`);
            }

            console.log('\n✅ IMPORT COMPLETED!');
            
        } catch (error) {
            console.error('❌ Import failed:', error.message);
        } finally {
            if (this.localDb) {
                this.localDb.close();
                console.log('🔒 Local database connection closed');
            }
        }
    }

    // Create sample Hostinger SQL file for testing
    createSampleHostingerFile() {
        const sampleSql = `-- Sample Hostinger Export Data
-- Generated for testing purposes

-- Categories data
INSERT INTO categories (id, name, description, image, created_at, updated_at) VALUES (1, 'Elektrik', 'Elektrik məhsulları', 'category-placeholder.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO categories (id, name, description, image, created_at, updated_at) VALUES (2, 'Mexaniki', 'Mexaniki məhsullar', 'category-placeholder.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO categories (id, name, description, image, created_at, updated_at) VALUES (3, 'Avtomatlaşdırma', 'Avtomatlaşdırma sistemləri', 'category-placeholder.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');

-- Markas data
INSERT INTO markas (id, name, description, image, created_at, updated_at) VALUES (1, 'Siemens', 'Siemens məhsulları', 'siemens-logo.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO markas (id, name, description, image, created_at, updated_at) VALUES (2, 'ABB', 'ABB məhsulları', 'abb-logo.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO markas (id, name, description, image, created_at, updated_at) VALUES (3, 'Schneider', 'Schneider məhsulları', 'schneider-logo.svg', '2025-08-30 20:27:19', '2025-08-30 20:27:19');

-- Products data
INSERT INTO products (id, name, description, price, category_id, marka_id, image, stock, status, created_at, updated_at) VALUES (1, 'Siemens S7-1200 PLC', 'Kompakt avtomatlaşdırma sistemi', 450, 3, 1, 'product-placeholder.svg', 25, 'active', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
INSERT INTO products (id, name, description, price, category_id, marka_id, image, stock, status, created_at, updated_at) VALUES (2, 'ABB Kontaktor', 'Yüksək keyfiyyətli elektrik kontaktoru', 85.5, 1, 2, 'product-placeholder.svg', 50, 'active', '2025-08-30 20:27:19', '2025-08-30 20:27:19');
`;

        fs.writeFileSync(HOSTINGER_SQL_FILE, sampleSql);
        console.log('✅ Sample Hostinger SQL file created:', HOSTINGER_SQL_FILE);
    }
}

// Command line usage
if (require.main === module) {
    const importer = new HostingerImporter();
    
    const action = process.argv[2] || 'import';
    
    switch (action) {
        case 'import':
            importer.importFromHostinger();
            break;
        case 'sample':
            importer.createSampleHostingerFile();
            break;
        default:
            console.log('Usage: node import_from_hostinger.js [import|sample]');
            console.log('  import - Import data from hostinger_data.sql to local database');
            console.log('  sample - Create sample hostinger_data.sql file');
    }
}

module.exports = HostingerImporter;