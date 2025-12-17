<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once 'config.php';

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Extract values

$uid = trim($data['uid'] ?? '');
$savetype = trim($data['savetype'] ?? '');

// Validate required fields

// var_dump($data);


switch ($savetype) {

    case 'formdata':

    case 'formdata':

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $company = trim($data['company'] ?? '');

        if (!$name || !$email || !$uid) {
            echo json_encode([
                'status' => 'error',
                'message' => 'UID, Name, and Email are required'
            ]);
            exit;
        }

        // Update existing user only
        $updateStmt = $conn->prepare("UPDATE users SET name = ?, email = ?, company = ? WHERE uid = ?");
        $updateStmt->bind_param("ssss", $name, $email, $company, $uid);

        if ($updateStmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'uid' => $uid,
                'message' => 'User updated'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => $updateStmt->error
            ]);
        }

        $updateStmt->close();
        break;

    case 'savedata':
        $score = $data['score'] ?? 0;
        $qa = $data['qa'] ?? [];
        $tryCount = $data['tryCount'] ?? 0;

        if ($uid == '' || empty($qa)) {
            echo json_encode(['status' => 'error', 'message' => 'UID or QA data missing']);
            exit;
        }

        $qa_json = json_encode($qa);

        $stmt = $conn->prepare("INSERT INTO user_answers (uid, score, tryCount, qa) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siis", $uid, $score, $tryCount, $qa_json);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Quiz saved']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }

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
