const sqlite3 = require('sqlite3').verbose();
const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');

// Import existing modules
const HostingerImporter = require('./import_from_hostinger.js');

class AutoSyncSystem {
    constructor() {
        this.localDbPath = path.join(__dirname, 'database', 'proline.db');
        this.syncInterval = 30000; // 30 seconds
        this.isRunning = false;
        this.lastSyncTime = null;
        this.syncLog = [];
    }

    // Log sync activities
    log(message, type = 'info') {
        const timestamp = new Date().toISOString();
        const logEntry = { timestamp, message, type };
        this.syncLog.push(logEntry);
        
        const emoji = type === 'error' ? '❌' : type === 'success' ? '✅' : '📝';
        console.log(`${emoji} [${timestamp}] ${message}`);
        
        // Keep only last 100 log entries
        if (this.syncLog.length > 100) {
            this.syncLog = this.syncLog.slice(-100);
        }
    }

    // Check if local database has been modified
    async checkLocalChanges() {
        return new Promise((resolve) => {
            const db = new sqlite3.Database(this.localDbPath);
            
            // Check last modification time of database file
            fs.stat(this.localDbPath, (err, stats) => {
                if (err) {
                    this.log('Error checking local database stats', 'error');
                    resolve(false);
                    return;
                }
                
                const lastModified = stats.mtime;
                const hasChanges = !this.lastSyncTime || lastModified > this.lastSyncTime;
                
                if (hasChanges) {
                    this.log('Local database changes detected');
                }
                
                db.close();
                resolve(hasChanges);
            });
        });
    }

    // Export local changes to Hostinger format
    async exportLocalChanges() {
        return new Promise((resolve, reject) => {
            this.log('Exporting local changes...');
            
            exec('node export_to_hostinger.js', (error, stdout, stderr) => {
                if (error) {
                    this.log(`Export error: ${error.message}`, 'error');
                    reject(error);
                    return;
                }
                
                if (stderr) {
                    this.log(`Export stderr: ${stderr}`, 'error');
                }
                
                this.log('Local changes exported successfully', 'success');
                resolve(stdout);
            });
        });
    }

    // Import changes from Hostinger
    async importHostingerChanges() {
        try {
            this.log('Checking for Hostinger updates...');
            
            // Check if hostinger_data.sql exists and is newer
            const hostingerFile = path.join(__dirname, 'hostinger_data.sql');
            
            if (!fs.existsSync(hostingerFile)) {
                this.log('No Hostinger data file found, skipping import');
                return;
            }
            
            const stats = fs.statSync(hostingerFile);
            const fileModified = stats.mtime;
            
            if (this.lastSyncTime && fileModified <= this.lastSyncTime) {
                this.log('No new Hostinger updates found');
                return;
            }
            
            // Import using existing importer
            const importer = new HostingerImporter();
            await importer.importFromHostinger();
            
            this.log('Hostinger changes imported successfully', 'success');
            
        } catch (error) {
            this.log(`Import error: ${error.message}`, 'error');
        }
    }

    // Perform bidirectional sync
    async performSync() {
        try {
            this.log('Starting sync cycle...');
            
            // Check for local changes
            const hasLocalChanges = await this.checkLocalChanges();
            
            if (hasLocalChanges) {
                // Export local changes
                await this.exportLocalChanges();
                this.log('Local changes ready for Hostinger upload', 'success');
            }
            
            // Import any Hostinger changes
            await this.importHostingerChanges();
            
            // Update last sync time
            this.lastSyncTime = new Date();
            
            this.log('Sync cycle completed', 'success');
            
        } catch (error) {
            this.log(`Sync error: ${error.message}`, 'error');
        }
    }

    // Start automatic sync
    startAutoSync() {
        if (this.isRunning) {
            this.log('Auto sync is already running');
            return;
        }
        
        this.isRunning = true;
        this.log(`Starting auto sync (interval: ${this.syncInterval/1000}s)`, 'success');
        
        // Initial sync
        this.performSync();
        
        // Set up interval
        this.syncIntervalId = setInterval(() => {
            this.performSync();
        }, this.syncInterval);
        
        // Handle graceful shutdown
        process.on('SIGINT', () => {
            this.stopAutoSync();
            process.exit(0);
        });
    }

    // Stop automatic sync
    stopAutoSync() {
        if (!this.isRunning) {
            this.log('Auto sync is not running');
            return;
        }
        
        this.isRunning = false;
        
        if (this.syncIntervalId) {
            clearInterval(this.syncIntervalId);
        }
        
        this.log('Auto sync stopped', 'success');
    }

    // Get sync status
    getStatus() {
        return {
            isRunning: this.isRunning,
            lastSyncTime: this.lastSyncTime,
            syncInterval: this.syncInterval,
            logCount: this.syncLog.length,
            recentLogs: this.syncLog.slice(-10)
        };
    }

    // Manual sync trigger
    async manualSync() {
        this.log('Manual sync triggered');
        await this.performSync();
    }

    // Create sync configuration file
    createSyncConfig() {
        const config = {
            syncInterval: this.syncInterval,
            autoStart: false,
            logLevel: 'info',
            maxLogEntries: 100,
            backupBeforeSync: true,
            conflictResolution: 'local_wins' // local_wins, remote_wins, manual
        };
        
        const configPath = path.join(__dirname, 'sync_config.json');
        fs.writeFileSync(configPath, JSON.stringify(config, null, 2));
        this.log(`Sync configuration created: ${configPath}`, 'success');
    }

    // Load sync configuration
    loadSyncConfig() {
        const configPath = path.join(__dirname, 'sync_config.json');
        
        if (fs.existsSync(configPath)) {
            try {
                const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));
                this.syncInterval = config.syncInterval || this.syncInterval;
                this.log('Sync configuration loaded', 'success');
                return config;
            } catch (error) {
                this.log(`Error loading config: ${error.message}`, 'error');
            }
        }
        
        return null;
    }

    // Generate sync report
    generateSyncReport() {
        const report = {
            generatedAt: new Date().toISOString(),
            status: this.getStatus(),
            recentActivity: this.syncLog.slice(-20),
            statistics: {
                totalSyncs: this.syncLog.filter(log => log.message.includes('completed')).length,
                errors: this.syncLog.filter(log => log.type === 'error').length,
                lastError: this.syncLog.filter(log => log.type === 'error').slice(-1)[0] || null
            }
        };
        
        const reportPath = path.join(__dirname, 'sync_report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        this.log(`Sync report generated: ${reportPath}`, 'success');
        
        return report;
    }
}

// Command line interface
if (require.main === module) {
    const syncSystem = new AutoSyncSystem();
    const action = process.argv[2] || 'help';
    
    switch (action) {
        case 'start':
            syncSystem.loadSyncConfig();
            syncSystem.startAutoSync();
            break;
            
        case 'sync':
            syncSystem.manualSync();
            break;
            
        case 'status':
            console.log('📊 Sync Status:', JSON.stringify(syncSystem.getStatus(), null, 2));
            break;
            
        case 'config':
            syncSystem.createSyncConfig();
            break;
            
        case 'report':
            const report = syncSystem.generateSyncReport();
            console.log('📋 Sync Report:', JSON.stringify(report, null, 2));
            break;
            
        case 'help':
        default:
            console.log('🔄 Auto Sync System Commands:');
            console.log('  start  - Start automatic synchronization');
            console.log('  sync   - Perform manual sync');
            console.log('  status - Show sync status');
            console.log('  config - Create sync configuration file');
            console.log('  report - Generate sync report');
            console.log('  help   - Show this help');
    }
}

module.exports = AutoSyncSystem;