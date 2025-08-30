const sqlite3 = require('sqlite3').verbose();
const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');

// Configuration
const config = {
    // Local SQLite database
    local: {
        database: path.join(__dirname, 'database', 'proline.db')
    },
    // Hostinger MySQL database
    hostinger: {
        host: 'localhost',
        user: 'u482576391_Md1PB',
        password: '0993991019Metleb',
        database: 'u482576391_yUuh0',
        port: 3306
    }
};

class DatabaseSync {
    constructor() {
        this.localDb = null;
        this.hostingerDb = null;
    }

    // Connect to local SQLite database
    async connectLocal() {
        return new Promise((resolve, reject) => {
            this.localDb = new sqlite3.Database(config.local.database, (err) => {
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

    // Connect to Hostinger MySQL database
    async connectHostinger() {
        try {
            this.hostingerDb = await mysql.createConnection(config.hostinger);
            console.log('✅ Connected to Hostinger MySQL database');
        } catch (error) {
            console.error('❌ Hostinger MySQL connection error:', error.message);
            throw error;
        }
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

    // Get data from Hostinger MySQL
    async getHostingerData(table) {
        try {
            const [rows] = await this.hostingerDb.execute(`SELECT * FROM ${table}`);
            return rows;
        } catch (error) {
            console.error(`❌ Error getting data from ${table}:`, error.message);
            return [];
        }
    }

    // Clear table in local database
    async clearLocalTable(table) {
        return new Promise((resolve, reject) => {
            this.localDb.run(`DELETE FROM ${table}`, [], (err) => {
                if (err) {
                    reject(err);
                } else {
                    console.log(`🧹 Cleared local ${table} table`);
                    resolve();
                }
            });
        });
    }

    // Insert data into local database
    async insertLocalData(table, data) {
        if (!data || data.length === 0) {
            console.log(`ℹ️ No data to insert into ${table}`);
            return;
        }

        const columns = Object.keys(data[0]);
        const placeholders = columns.map(() => '?').join(', ');
        const sql = `INSERT INTO ${table} (${columns.join(', ')}) VALUES (${placeholders})`;

        return new Promise((resolve, reject) => {
            const stmt = this.localDb.prepare(sql);
            let inserted = 0;

            data.forEach((row) => {
                const values = columns.map(col => row[col]);
                stmt.run(values, (err) => {
                    if (err) {
                        console.error(`❌ Error inserting into ${table}:`, err.message);
                    } else {
                        inserted++;
                    }
                });
            });

            stmt.finalize((err) => {
                if (err) {
                    reject(err);
                } else {
                    console.log(`✅ Inserted ${inserted} records into local ${table}`);
                    resolve();
                }
            });
        });
    }

    // Clear table in Hostinger database
    async clearHostingerTable(table) {
        try {
            await this.hostingerDb.execute(`DELETE FROM ${table}`);
            console.log(`🧹 Cleared Hostinger ${table} table`);
        } catch (error) {
            console.error(`❌ Error clearing Hostinger ${table}:`, error.message);
        }
    }

    // Insert data into Hostinger database
    async insertHostingerData(table, data) {
        if (!data || data.length === 0) {
            console.log(`ℹ️ No data to insert into Hostinger ${table}`);
            return;
        }

        const columns = Object.keys(data[0]);
        const placeholders = columns.map(() => '?').join(', ');
        const sql = `INSERT INTO ${table} (${columns.join(', ')}) VALUES (${placeholders})`;

        try {
            let inserted = 0;
            for (const row of data) {
                const values = columns.map(col => row[col]);
                await this.hostingerDb.execute(sql, values);
                inserted++;
            }
            console.log(`✅ Inserted ${inserted} records into Hostinger ${table}`);
        } catch (error) {
            console.error(`❌ Error inserting into Hostinger ${table}:`, error.message);
        }
    }

    // Sync from Hostinger to Local
    async syncFromHostinger() {
        console.log('\n🔄 SYNCING FROM HOSTINGER TO LOCAL');
        console.log('==================================');

        const tables = ['categories', 'markas', 'products'];

        for (const table of tables) {
            try {
                console.log(`\n📊 Syncing ${table}...`);
                const hostingerData = await this.getHostingerData(table);
                console.log(`📥 Found ${hostingerData.length} records in Hostinger ${table}`);

                await this.clearLocalTable(table);
                await this.insertLocalData(table, hostingerData);
            } catch (error) {
                console.error(`❌ Error syncing ${table}:`, error.message);
            }
        }
    }

    // Sync from Local to Hostinger
    async syncToHostinger() {
        console.log('\n🔄 SYNCING FROM LOCAL TO HOSTINGER');
        console.log('==================================');

        const tables = ['categories', 'markas', 'products'];

        for (const table of tables) {
            try {
                console.log(`\n📊 Syncing ${table}...`);
                const localData = await this.getLocalData(table);
                console.log(`📤 Found ${localData.length} records in local ${table}`);

                await this.clearHostingerTable(table);
                await this.insertHostingerData(table, localData);
            } catch (error) {
                console.error(`❌ Error syncing ${table}:`, error.message);
            }
        }
    }

    // Compare databases
    async compareDatabases() {
        console.log('\n🔍 COMPARING DATABASES');
        console.log('======================');

        const tables = ['categories', 'markas', 'products'];

        for (const table of tables) {
            try {
                const localData = await this.getLocalData(table);
                const hostingerData = await this.getHostingerData(table);

                console.log(`\n📊 ${table.toUpperCase()}:`);
                console.log(`  Local: ${localData.length} records`);
                console.log(`  Hostinger: ${hostingerData.length} records`);

                if (localData.length === hostingerData.length) {
                    console.log(`  ✅ Record counts match`);
                } else {
                    console.log(`  ⚠️ Record counts differ`);
                }
            } catch (error) {
                console.error(`❌ Error comparing ${table}:`, error.message);
            }
        }
    }

    // Close connections
    async close() {
        if (this.localDb) {
            this.localDb.close();
            console.log('🔒 Local database connection closed');
        }
        if (this.hostingerDb) {
            await this.hostingerDb.end();
            console.log('🔒 Hostinger database connection closed');
        }
    }
}

// Main function
async function main() {
    const sync = new DatabaseSync();

    try {
        console.log('🚀 DATABASE SYNCHRONIZATION TOOL');
        console.log('=================================\n');

        // Connect to databases
        await sync.connectLocal();
        await sync.connectHostinger();

        // Get command line argument
        const action = process.argv[2] || 'compare';

        switch (action) {
            case 'from-hostinger':
                await sync.syncFromHostinger();
                break;
            case 'to-hostinger':
                await sync.syncToHostinger();
                break;
            case 'compare':
                await sync.compareDatabases();
                break;
            default:
                console.log('\n📋 Available commands:');
                console.log('  node sync_databases.js compare          - Compare databases');
                console.log('  node sync_databases.js from-hostinger   - Sync from Hostinger to Local');
                console.log('  node sync_databases.js to-hostinger     - Sync from Local to Hostinger');
        }

        console.log('\n✅ Synchronization completed!');

    } catch (error) {
        console.error('❌ Synchronization failed:', error.message);
    } finally {
        await sync.close();
    }
}

// Run the script
if (require.main === module) {
    main();
}

module.exports = DatabaseSync;