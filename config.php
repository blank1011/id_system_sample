<?php
// config.php - Database configuration

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'id_expiration_system');

// File upload settings
define('UPLOAD_DIR', 'uploads/student_photos/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_TYPES', array('jpg', 'jpeg', 'png', 'gif'));

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Function to sanitize filename
function sanitizeFilename($filename, $student_number) {
    // Remove special characters
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    $filename = str_replace(' ', '_', $filename);
    
    // Get file extension
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    
    // Return new filename
    return $student_number . '_' . time() . '.' . $ext;
}
?>