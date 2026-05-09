<?php
/**
 * Helper Functions
 * ----------------
 * Reusable utilities for the application
 */

/**
 * Send a JSON response with status code
 */
function jsonResponse(array $data, int $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Validate that required fields are present and non-empty
 */
function validateRequired(array $fields, array $data): array {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $missing[] = $field;
        }
    }
    return $missing;
}

/**
 * Sanitize input string
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Get setting value from database
 */
function getSetting(string $key, string $default = ''): string {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1");
        $stmt->execute([':key' => $key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Update setting value
 */
function updateSetting(string $key, string $value): bool {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = :key");
        return $stmt->execute([':value' => $value, ':key' => $key]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Create directory if it doesn't exist
 */
function ensureDirectory(string $path): bool {
    if (!is_dir($path)) {
        return mkdir($path, 0755, true);
    }
    return true;
}

/**
 * Format seconds into human-readable duration
 */
function formatDuration(int $seconds): string {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
}

/**
 * Get the base URL of the application
 */
function getBaseUrl(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['SCRIPT_NAME']);
    return rtrim($protocol . '://' . $host . $path, '/');
}
