<?php
// Front controller for Lapor Pak Wali
// This file handles all requests and routes them to the appropriate files

// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'];
$request_path = parse_url($request_uri, PHP_URL_PATH);

// Remove query string from path
$request_path = explode('?', $request_path)[0];

// Remove base directory from path
$base_dir = '/lapor-pak-wali/';
if (strpos($request_path, $base_dir) === 0) {
    $request_path = substr($request_path, strlen($base_dir));
}

// Default file
if (empty($request_path) || $request_path === '/') {
    $request_path = 'pages/login.html';
}

// Route to appropriate file
if (strpos($request_path, 'api/') === 0) {
    // API routes - serve PHP files directly
    $file_path = __DIR__ . '/' . $request_path;
    
    if (file_exists($file_path)) {
        // Set headers for API
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
        
        // Include and execute the API file
        include $file_path;
    } else {
        // Try to map to the main application API
        if ($request_path === 'api/auth') {
            // Redirect to simple_auth.php
            include __DIR__ . '/api/simple_auth.php';
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'API endpoint not found']);
        }
    }
} else {
    // Static files - serve directly
    $file_path = __DIR__ . '/' . $request_path;
    
    if (file_exists($file_path)) {
        // Set appropriate content type based on file extension
        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
        switch ($extension) {
            case 'html':
                header('Content-Type: text/html; charset=utf-8');
                break;
            case 'css':
                header('Content-Type: text/css; charset=utf-8');
                break;
            case 'js':
                header('Content-Type: application/javascript; charset=utf-8');
                break;
            case 'json':
                header('Content-Type: application/json; charset=utf-8');
                break;
            case 'png':
                header('Content-Type: image/png');
                break;
            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                break;
            case 'gif':
                header('Content-Type: image/gif');
                break;
            case 'svg':
                header('Content-Type: image/svg+xml');
                break;
            case 'ico':
                header('Content-Type: image/x-icon');
                break;
        }
        
        // Output file content
        readfile($file_path);
    } else {
        // 404 - File not found
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>404 - Page Not Found</title></head><body><h1>404 - Page Not Found</h1><p>The requested page could not be found.</p></body></html>';
    }
}
?>