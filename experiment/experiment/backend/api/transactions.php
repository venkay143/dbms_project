<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    if ($action === 'create') {
        // Create new transaction
        $sender_id = $input['sender_id'] ?? 1;
        $receiver_email = $input['recipient_email'] ?? '';
        $amount = $input['amount'] ?? 0;
        $description = $input['description'] ?? '';
        
        if (empty($receiver_email) || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid transaction data']);
            exit;
        }
        
        try {
            // Simulate random status
            $statuses = ['success', 'fraud', 'refund_pending'];
            $status = $statuses[array_rand($statuses)];
            
            $stmt = $pdo->prepare("INSERT INTO transactions (sender_id, receiver_email, amount, status, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$sender_id, $receiver_email, $amount, $status, $description]);
            
            $transaction_id = $pdo->lastInsertId();
            
            // Log activity
            $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action_type, description) VALUES (?, ?, ?)");
            $stmt->execute([$sender_id, 'transaction', "Sent $${amount} to {$receiver_email}"]);
            
            // If refund, add to refund queue
            if ($status === 'refund_pending') {
                $due_date = date('Y-m-d', strtotime('+30 days'));
                $stmt = $pdo->prepare("INSERT INTO refund_queue (transaction_id, refund_due_date) VALUES (?, ?)");
                $stmt->execute([$transaction_id, $due_date]);
            }
            
            echo json_encode([
                'success' => true,
                'transaction' => [
                    'id' => $transaction_id,
                    'status' => $status,
                    'amount' => $amount,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        
    } elseif ($action === 'get_user') {
        // Get user transactions
        $user_id = $input['user_id'] ?? 1;
        
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM transactions 
                WHERE sender_id = ? 
                ORDER BY timestamp DESC
            ");
            $stmt->execute([$user_id]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'transactions' => $transactions]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        
    } elseif ($action === 'get_all') {
        // Get all transactions (for admin/auditor)
        try {
            $stmt = $pdo->prepare("
                SELECT t.*, u.username as sender_name 
                FROM transactions t 
                JOIN users u ON t.sender_id = u.user_id 
                ORDER BY t.timestamp DESC
            ");
            $stmt->execute();
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'transactions' => $transactions]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
