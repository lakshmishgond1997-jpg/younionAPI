<?php
// api/config.php

header('Content-Type: application/json');

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');      // EMPTY password
define('DB_NAME', 'quiz');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        'status' => 'error',
        'message' => $conn->connect_error
    ]);
    exit;
}
