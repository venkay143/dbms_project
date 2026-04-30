<?php
require 'config.php';

header('Content-Type: application/json; charset=utf-8');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Update a user (expects user_id and username)
if (!empty($data['user_id']) && isset($data['username'])) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE user_id = ?");
        $stmt->execute([$data['username'], $data['user_id']]);
        echo json_encode(['success' => true, 'rows_affected' => $stmt->rowCount()]);
    } catch (PDOException $e) {
        if (defined('API_DEBUG') && API_DEBUG) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data. Expecting user_id and username']);
}
?>