<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once 'config.php'; // your DB connection

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);

$savetype = trim($data['savetype'] ?? '');


switch ($savetype) {

    case 'validateAdmin':
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');

        if (!$username || !$password) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Username and password required'
            ]);
            exit;
        }

        $stmt = $conn->prepare("SELECT username, password, quizStatus FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid username']);
            exit;
        }

        $row = $result->fetch_assoc();

        // If password is hashed, use password_verify()
        if ($password === $row['password']) {
            echo json_encode([
                'status' => 'success',
                'username' => $row['username'],
                'quizStatus' => $row['quizStatus'],
                'message' => 'Login successful'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
        }

        $stmt->close();
        break;
    case 'getAllUsers':
        $stmt = $conn->prepare("
    SELECT u.uid, u.name, u.email, u.company, ua.score
    FROM users u
    LEFT JOIN user_answers ua ON u.uid = ua.uid
");
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        echo json_encode(['status' => 'success', 'users' => $users]);

        $stmt->close();
        break;
    case 'getQuizData':

        $stmt = $conn->prepare("
        SELECT 
            ua.uid,
            u.name,
            u.email,
            u.company,
            ua.score,
            ua.tryCount
        FROM user_answers ua
        LEFT JOIN users u ON u.uid = ua.uid
        ORDER BY ua.score DESC
    ");

        $stmt->execute();
        $result = $stmt->get_result();

        $quizData = [];
        while ($row = $result->fetch_assoc()) {
            $quizData[] = $row;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $quizData
        ]);

        $stmt->close();
        break;


    case 'updateAnnouncementStatus':
        $status = intval($data['status'] ?? 0);

        $stmt = $conn->prepare("UPDATE admin SET announcementStatus = ?");
        $stmt->bind_param("i", $status);
        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => 'Announcement status updated'
        ]);

        $stmt->close();
        break;

    case 'getAnnouncementStatus':
        $stmt = $conn->prepare("SELECT announcementStatus FROM admin LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        echo json_encode([
            'status' => 'success',
            'announcementStatus' => intval($row['announcementStatus'] ?? 0)
        ]);

        $stmt->close();
        break;

    case 'getQuizStatus':
        $res = $conn->query("SELECT quizstatus FROM admin LIMIT 1");
        echo json_encode([
            'status' => 'success',
            'quizstatus' => $res->fetch_assoc()['quizstatus']
        ]);
        break;
    case 'updateQuizStatus':
        $status = intval($data['quizstatus']);
        $conn->query("UPDATE admin SET quizstatus = $status");
        echo json_encode(['status' => 'success']);
        break;
    case 'getWinners':

        $sql = "
     SELECT
  ua.uid,
  u.name,
  u.email,
  u.company,
  MAX(ua.score) AS score
FROM user_answers ua
INNER JOIN users u ON u.uid = ua.uid
GROUP BY ua.uid, u.name, u.email, u.company
ORDER BY score DESC
LIMIT 10;

    ";

        $res = $conn->query($sql);

        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
        exit;


    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid savetype'
        ]);
}

$conn->close();
