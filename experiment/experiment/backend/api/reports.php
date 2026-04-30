<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $transaction_id = $input['transaction_id'] ?? 0;
    $reporter_id = $input['reporter_id'] ?? 1;
    $reason = $input['reason'] ?? '';
    
    if (empty($transaction_id) || empty($reason)) {
        echo json_encode(['success' => false, 'message' => 'Transaction ID and reason are required']);
        exit;
    }
    
    try {
        // Create report
        $stmt = $pdo->prepare("INSERT INTO reports (transaction_id, reporter_id, reason) VALUES (?, ?, ?)");
        $stmt->execute([$transaction_id, $reporter_id, $reason]);
        
        // Update transaction report flag
        $stmt = $pdo->prepare("UPDATE transactions SET report_flag = 1 WHERE transaction_id = ?");
        $stmt->execute([$transaction_id]);
        
        // Log activity
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action_type, description) VALUES (?, ?, ?)");
        $stmt->execute([$reporter_id, 'report', "Reported transaction #{$transaction_id}"]);
        
        echo json_encode(['success' => true, 'message' => 'Report submitted successfully']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>