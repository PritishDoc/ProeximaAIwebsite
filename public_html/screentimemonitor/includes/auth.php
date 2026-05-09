<?php
/**
 * Authentication Helpers
 * ----------------------
 * Session management, role checking, CSRF protection
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly'  => true,
        'samesite'  => 'Strict',
        'secure'    => isset($_SERVER['HTTPS'])
    ]);
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require login — redirect to index if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // If API request, return JSON error
        if (isApiRequest()) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            exit;
        }
        header('Location: /index.php');
        exit;
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        if (isApiRequest()) {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            exit;
        }
        header('Location: /dashboard.php');
        exit;
    }
}

/**
 * Get current user data from session
 */
function getCurrentUser(): array {
    if (!isLoggedIn()) return [];
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['user_role']
    ];
}

/**
 * Check if request is an API call
 */
function isApiRequest(): bool {
    $path = $_SERVER['REQUEST_URI'] ?? '';
    return strpos($path, '/api/') !== false || 
           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
