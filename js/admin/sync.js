// Sync Management System for Admin Panel
class SyncManager {
    constructor() {
        this.apiBase = '/api/sync.php';
        this.statusInterval = null;
        this.autoRefresh = true;
        this.init();
    }

    // Initialize sync manager
    init() {
        this.createSyncUI();
        this.bindEvents();
        this.startStatusMonitoring();
        this.loadSyncStatus();
    }

    // Create sync UI elements
    createSyncUI() {
        const syncContainer = document.createElement('div');
        syncContainer.id = 'sync-container';
        syncContainer.className = 'sync-container';
        
        syncContainer.innerHTML = `
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-sync-alt"></i> Databaza Sinxronizasiyası
                    </h5>
                    <div class="sync-status-indicator">
                        <span id="sync-status" class="badge badge-secondary">Yüklənir...</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Sync Controls -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <button id="manual-sync-btn" class="btn btn-primary btn-sm me-2">
                                <i class="fas fa-sync"></i> Manual Sinxronizasiya
                            </button>
                            <button id="auto-sync-toggle" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-play"></i> Avtomatik Sinxronizasiya
                            </button>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                Son sinxronizasiya: <span id="last-sync-time">-</span>
                            </small>
                        </div>
                    </div>
                    
                    <!-- Database Stats -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h6>Databaza Statistikaları:</h6>
                            <div class="row" id="db-stats">
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="h4 mb-0" id="categories-count">-</div>
                                        <small class="text-muted">Kateqoriyalar</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="h4 mb-0" id="markas-count">-</div>
                                        <small class="text-muted">Markalar</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="h4 mb-0" id="products-count">-</div>
                                        <small class="text-muted">Məhsullar</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="h4 mb-0" id="users-count">-</div>
                                        <small class="text-muted">İstifadəçilər</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sync Log -->
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Son Fəaliyyətlər:</h6>
                            <div id="sync-log" class="sync-log">
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-spinner fa-spin"></i> Yüklənir...
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sync Instructions -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Sinxronizasiya Təlimatları:</h6>
                                <ul class="mb-0">
                                    <li><strong>Manual Sinxronizasiya:</strong> Lokal dəyişiklikləri Hostinger üçün hazırlayır</li>
                                    <li><strong>Avtomatik Sinxronizasiya:</strong> Hər 30 saniyədə bir dəyişiklikləri yoxlayır</li>
                                    <li><strong>Hostinger Upload:</strong> Yaradılan SQL faylını phpMyAdmin-ə yükləyin</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Insert sync container at the top of main content
        const mainContent = document.querySelector('.container-fluid') || document.body;
        mainContent.insertBefore(syncContainer, mainContent.firstChild);
    }

    // Bind event listeners
    bindEvents() {
        // Manual sync button
        document.getElementById('manual-sync-btn').addEventListener('click', () => {
            this.triggerManualSync();
        });
        
        // Auto sync toggle
        document.getElementById('auto-sync-toggle').addEventListener('click', () => {
            this.toggleAutoSync();
        });
        
        // Listen for data changes in other admin scripts
        window.addEventListener('dataChanged', (event) => {
            this.notifyDataChange(event.detail);
        });
    }

    // Start status monitoring
    startStatusMonitoring() {
        if (this.statusInterval) {
            clearInterval(this.statusInterval);
        }
        
        this.statusInterval = setInterval(() => {
            if (this.autoRefresh) {
                this.loadSyncStatus();
            }
        }, 10000); // Check every 10 seconds
    }

    // Load sync status from API
    async loadSyncStatus() {
        try {
            const response = await fetch(`${this.apiBase}/status`);
            const data = await response.json();
            
            if (data.success) {
                this.updateUI(data);
            } else {
                this.showError('Status yüklənə bilmədi: ' + data.error);
            }
        } catch (error) {
            console.error('Sync status error:', error);
            this.showError('Sinxronizasiya statusu yüklənə bilmədi');
        }
    }

    // Update UI with sync status
    updateUI(data) {
        // Update status indicator
        const statusElement = document.getElementById('sync-status');
        if (data.files && data.files.auto_sync_running) {
            statusElement.textContent = 'Aktiv';
            statusElement.className = 'badge badge-success';
        } else {
            statusElement.textContent = 'Dayandırılıb';
            statusElement.className = 'badge badge-secondary';
        }
        
        // Update last sync time
        const lastSyncElement = document.getElementById('last-sync-time');
        if (data.last_sync) {
            lastSyncElement.textContent = new Date(data.last_sync).toLocaleString('az-AZ');
        } else {
            lastSyncElement.textContent = 'Heç vaxt';
        }
        
        // Update database stats
        if (data.database_stats) {
            document.getElementById('categories-count').textContent = data.database_stats.categories || 0;
            document.getElementById('markas-count').textContent = data.database_stats.markas || 0;
            document.getElementById('products-count').textContent = data.database_stats.products || 0;
            document.getElementById('users-count').textContent = data.database_stats.users || 0;
        }
        
        // Update sync log
        this.updateSyncLog(data.recent_logs || []);
        
        // Update auto sync button
        const autoSyncBtn = document.getElementById('auto-sync-toggle');
        if (data.files && data.files.auto_sync_running) {
            autoSyncBtn.innerHTML = '<i class="fas fa-stop"></i> Avtomatik Sinxronizasiyanı Dayandır';
            autoSyncBtn.className = 'btn btn-outline-danger btn-sm';
        } else {
            autoSyncBtn.innerHTML = '<i class="fas fa-play"></i> Avtomatik Sinxronizasiya';
            autoSyncBtn.className = 'btn btn-outline-success btn-sm';
        }
    }

    // Update sync log display
    updateSyncLog(logs) {
        const logContainer = document.getElementById('sync-log');
        
        if (logs.length === 0) {
            logContainer.innerHTML = '<div class="text-center text-muted py-2">Hələ ki log yoxdur</div>';
            return;
        }
        
        const logHtml = logs.map(log => {
            const timestamp = log.includes('[') ? log.substring(log.indexOf('[') + 1, log.indexOf(']')) : '';
            const message = log.includes(']') ? log.substring(log.lastIndexOf(']') + 1).trim() : log;
            const type = log.includes('[ERROR]') ? 'danger' : log.includes('[SUCCESS]') ? 'success' : 'info';
            
            return `
                <div class="sync-log-entry alert alert-${type} py-1 px-2 mb-1">
                    <small>
                        <strong>${timestamp}</strong> ${message}
                    </small>
                </div>
            `;
        }).join('');
        
        logContainer.innerHTML = logHtml;
        
        // Auto scroll to bottom
        logContainer.scrollTop = logContainer.scrollHeight;
    }

    // Trigger manual sync
    async triggerManualSync() {
        const btn = document.getElementById('manual-sync-btn');
        const originalText = btn.innerHTML;
        
        try {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sinxronlaşdırılır...';
            btn.disabled = true;
            
            const response = await fetch(`${this.apiBase}/trigger`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Sinxronizasiya uğurla tamamlandı!');
                this.loadSyncStatus(); // Refresh status
            } else {
                this.showError('Sinxronizasiya xətası: ' + data.error);
            }
        } catch (error) {
            console.error('Manual sync error:', error);
            this.showError('Sinxronizasiya zamanı xəta baş verdi');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    // Toggle auto sync
    async toggleAutoSync() {
        const btn = document.getElementById('auto-sync-toggle');
        const isRunning = btn.textContent.includes('Dayandır');
        const endpoint = isRunning ? '/stop' : '/start';
        
        try {
            btn.disabled = true;
            
            const response = await fetch(`${this.apiBase}${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(data.message);
                setTimeout(() => this.loadSyncStatus(), 1000); // Refresh after delay
            } else {
                this.showError('Xəta: ' + data.error);
            }
        } catch (error) {
            console.error('Auto sync toggle error:', error);
            this.showError('Avtomatik sinxronizasiya dəyişdirilə bilmədi');
        } finally {
            btn.disabled = false;
        }
    }

    // Notify data change
    async notifyDataChange(details) {
        try {
            await fetch(`${this.apiBase}/notify`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(details)
            });
        } catch (error) {
            console.error('Data change notification error:', error);
        }
    }

    // Show success message
    showSuccess(message) {
        this.showToast(message, 'success');
    }

    // Show error message
    showError(message) {
        this.showToast(message, 'error');
    }

    // Show toast notification
    showToast(message, type) {
        // Create toast if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
            `;
            document.body.appendChild(toastContainer);
        }
        
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        toastContainer.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 5000);
    }

    // Destroy sync manager
    destroy() {
        if (this.statusInterval) {
            clearInterval(this.statusInterval);
        }
        
        const syncContainer = document.getElementById('sync-container');
        if (syncContainer) {
            syncContainer.remove();
        }
    }
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.syncManager = new SyncManager();
    });
} else {
    window.syncManager = new SyncManager();
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SyncManager;
}