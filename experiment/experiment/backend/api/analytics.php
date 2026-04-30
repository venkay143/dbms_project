<?php
include 'config.php';

try {
    // Get total transactions count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM transactions");
    $total_transactions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get successful transactions count
    $stmt = $pdo->query("SELECT COUNT(*) as success FROM transactions WHERE status = 'success'");
    $success_transactions = $stmt->fetch(PDO::FETCH_ASSOC)['success'];
    
    // Get fraud transactions count
    $stmt = $pdo->query("SELECT COUNT(*) as fraud FROM transactions WHERE status = 'fraud'");
    $fraud_transactions = $stmt->fetch(PDO::FETCH_ASSOC)['fraud'];
    
    // Get pending refunds count
    $stmt = $pdo->query("SELECT COUNT(*) as refunds FROM transactions WHERE status = 'refund_pending'");
    $refund_transactions = $stmt->fetch(PDO::FETCH_ASSOC)['refunds'];
    
    // Get total users count
    $stmt = $pdo->query("SELECT COUNT(*) as users FROM users");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['users'];
    
    // Get pending reports count
    $stmt = $pdo->query("SELECT COUNT(*) as reports FROM reports WHERE status = 'new'");
    $pending_reports = $stmt->fetch(PDO::FETCH_ASSOC)['reports'];
    
    echo json_encode([
        'success' => true,
        'analytics' => [
            'total_transactions' => $total_transactions,
            'success_transactions' => $success_transactions,
            'fraud_transactions' => $fraud_transactions,
            'refund_transactions' => $refund_transactions,
            'total_users' => $total_users,
            'pending_reports' => $pending_reports
        ]
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>