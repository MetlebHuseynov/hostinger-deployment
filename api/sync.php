<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

class SyncAPI {
    private $db;
    private $logFile;
    
    public function __construct() {
        $this->db = new Database();
        $this->logFile = '../sync_log.txt';
    }
    
    // Log sync activities
    private function log($message, $type = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$type}] {$message}" . PHP_EOL;
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    // Trigger export to Hostinger
    private function triggerExport() {
        try {
            $command = 'cd .. && node export_to_hostinger.js';
            $output = shell_exec($command . ' 2>&1');
            
            $this->log('Export triggered: ' . $output);
            return true;
        } catch (Exception $e) {
            $this->log('Export error: ' . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    // Get sync status
    public function getSyncStatus() {
        try {
            // Check if sync files exist
            $syncFiles = [
                'hostinger_sync.sql' => file_exists('../hostinger_sync.sql'),
                'sync_log.txt' => file_exists('../sync_log.txt'),
                'auto_sync_running' => $this->isAutoSyncRunning()
            ];
            
            // Get recent log entries
            $recentLogs = [];
            if (file_exists($this->logFile)) {
                $logs = file($this->logFile, FILE_IGNORE_NEW_LINES);
                $recentLogs = array_slice($logs, -10); // Last 10 entries
            }
            
            // Get database stats
            $stats = $this->getDatabaseStats();
            
            return [
                'success' => true,
                'status' => 'active',
                'files' => $syncFiles,
                'recent_logs' => $recentLogs,
                'database_stats' => $stats,
                'last_sync' => $this->getLastSyncTime()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Check if auto sync is running
    private function isAutoSyncRunning() {
        // Check for Node.js process
        $output = shell_exec('tasklist /FI "IMAGENAME eq node.exe" 2>NUL');
        return strpos($output, 'auto_sync_system.js') !== false;
    }
    
    // Get database statistics
    private function getDatabaseStats() {
        try {
            $conn = $this->db->getConnection();
            
            $stats = [];
            $tables = ['categories', 'markas', 'products', 'users'];
            
            foreach ($tables as $table) {
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM {$table}");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $stats[$table] = (int)$result['count'];
            }
            
            return $stats;
            
        } catch (Exception $e) {
            $this->log('Database stats error: ' . $e->getMessage(), 'ERROR');
            return [];
        }
    }
    
    // Get last sync time
    private function getLastSyncTime() {
        if (file_exists('../hostinger_sync.sql')) {
            return date('Y-m-d H:i:s', filemtime('../hostinger_sync.sql'));
        }
        return null;
    }
    
    // Trigger manual sync
    public function triggerSync() {
        try {
            $this->log('Manual sync triggered via API');
            
            // Export local changes
            $exportSuccess = $this->triggerExport();
            
            if (!$exportSuccess) {
                throw new Exception('Export failed');
            }
            
            // Update sync timestamp
            touch('../last_sync.txt');
            
            return [
                'success' => true,
                'message' => 'Sync completed successfully',
                'timestamp' => date('Y-m-d H:i:s'),
                'files_generated' => [
                    'hostinger_sync.sql',
                    'hostinger_backup_template.sql',
                    'SYNC_INSTRUCTIONS.md'
                ]
            ];
            
        } catch (Exception $e) {
            $this->log('Manual sync error: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Start auto sync
    public function startAutoSync() {
        try {
            if ($this->isAutoSyncRunning()) {
                return [
                    'success' => false,
                    'message' => 'Auto sync is already running'
                ];
            }
            
            // Start auto sync in background
            $command = 'cd .. && start /B node auto_sync_system.js start';
            pclose(popen($command, 'r'));
            
            $this->log('Auto sync started via API');
            
            return [
                'success' => true,
                'message' => 'Auto sync started successfully'
            ];
            
        } catch (Exception $e) {
            $this->log('Auto sync start error: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Stop auto sync
    public function stopAutoSync() {
        try {
            // Kill Node.js auto sync process
            $command = 'taskkill /F /IM node.exe /FI "WINDOWTITLE eq auto_sync_system.js*"';
            shell_exec($command);
            
            $this->log('Auto sync stopped via API');
            
            return [
                'success' => true,
                'message' => 'Auto sync stopped successfully'
            ];
            
        } catch (Exception $e) {
            $this->log('Auto sync stop error: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Handle data change notifications
    public function notifyDataChange($table, $action, $data = null) {
        try {
            $this->log("Data change: {$action} in {$table} table");
            
            // Trigger immediate sync for critical changes
            if (in_array($action, ['INSERT', 'UPDATE', 'DELETE'])) {
                $this->triggerExport();
            }
            
            return [
                'success' => true,
                'message' => 'Data change notification processed',
                'sync_triggered' => true
            ];
            
        } catch (Exception $e) {
            $this->log('Data change notification error: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

// Handle API requests
$syncAPI = new SyncAPI();
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($path === '/status') {
                echo json_encode($syncAPI->getSyncStatus());
            } else {
                echo json_encode($syncAPI->getSyncStatus());
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($path === '/trigger') {
                echo json_encode($syncAPI->triggerSync());
            } elseif ($path === '/start') {
                echo json_encode($syncAPI->startAutoSync());
            } elseif ($path === '/stop') {
                echo json_encode($syncAPI->stopAutoSync());
            } elseif ($path === '/notify') {
                $table = $input['table'] ?? '';
                $action = $input['action'] ?? '';
                $data = $input['data'] ?? null;
                echo json_encode($syncAPI->notifyDataChange($table, $action, $data));
            } else {
                throw new Exception('Invalid endpoint');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>