<?php
/**
 * Cron: Cleanup Old Screenshots
 * ------------------------------
 * Deletes screenshots older than retention_days setting.
 * Set up as a cron job: 0 2 * * * php /path/to/cron_cleanup.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$retentionDays = intval(getSetting('retention_days', '30'));
$cutoffDate = date('Y-m-d H:i:s', strtotime("-{$retentionDays} days"));

echo "Cleanup started at " . date('Y-m-d H:i:s') . "\n";
echo "Deleting screenshots older than {$cutoffDate} ({$retentionDays} days)\n";

try {
    $db = getDB();
    
    // Get old screenshots
    $stmt = $db->prepare("SELECT id, image_path FROM screenshots WHERE captured_at < :cutoff");
    $stmt->execute([':cutoff' => $cutoffDate]);
    $oldScreenshots = $stmt->fetchAll();
    
    $deletedFiles = 0;
    $deletedRecords = 0;
    $errors = 0;
    
    foreach ($oldScreenshots as $screenshot) {
        $filePath = __DIR__ . '/' . $screenshot['image_path'];
        
        // Delete file
        if (file_exists($filePath)) {
            if (@unlink($filePath)) {
                $deletedFiles++;
            } else {
                $errors++;
                echo "ERROR: Could not delete file: {$filePath}\n";
            }
        }
        
        // Delete DB record
        $stmt = $db->prepare("DELETE FROM screenshots WHERE id = :id");
        $stmt->execute([':id' => $screenshot['id']]);
        $deletedRecords++;
    }
    
    // Clean up empty directories
    $screenshotDir = __DIR__ . '/uploads/screenshots/';
    if (is_dir($screenshotDir)) {
        $userDirs = glob($screenshotDir . '*', GLOB_ONLYDIR);
        foreach ($userDirs as $userDir) {
            $dateDirs = glob($userDir . '/*', GLOB_ONLYDIR);
            foreach ($dateDirs as $dateDir) {
                // Remove empty date directories
                $files = glob($dateDir . '/*');
                if (empty($files)) {
                    @rmdir($dateDir);
                }
            }
        }
    }
    
    // Also clean old activity logs (keep 90 days)
    $activityCutoff = date('Y-m-d H:i:s', strtotime('-90 days'));
    $stmt = $db->prepare("DELETE FROM activity_logs WHERE period_end < :cutoff");
    $stmt->execute([':cutoff' => $activityCutoff]);
    $deletedLogs = $stmt->rowCount();
    
    echo "\nCleanup complete:\n";
    echo "- Files deleted: {$deletedFiles}\n";
    echo "- DB records deleted: {$deletedRecords}\n";
    echo "- Activity logs purged: {$deletedLogs}\n";
    echo "- Errors: {$errors}\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
