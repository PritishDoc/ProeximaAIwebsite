<?php
// router.php - Enables extensionless URLs for PHP Built-In Web Server
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$ext = pathinfo($path, PATHINFO_EXTENSION);

if ($ext === '') {
    $file = __DIR__ . $path . '.php';
    // If the path corresponds to a .php file, run it
    if (file_exists($file)) {
        require $file;
        return true;
    }
    
    // Also handle directory index fallback
    $indexFile = __DIR__ . $path . '/index.php';
    if (file_exists($indexFile)) {
        require $indexFile;
        return true;
    }
}

// Serve the requested resource as-is
return false;
