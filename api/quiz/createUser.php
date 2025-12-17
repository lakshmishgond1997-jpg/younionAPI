<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once 'config.php';


function generateNumericUid()
{
    return (string) round(microtime(true) * 1000) . random_int(100, 999);
}

$uid = generateNumericUid();

$stmt = $conn->prepare("INSERT INTO users (uid) VALUES (?)");
$stmt->bind_param("s", $uid);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'uid' => $uid
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
