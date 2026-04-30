<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $stored = $user['password'];
            $verified = false;
            if (function_exists('password_verify')) {
                $verified = password_verify($password, $stored);
            } else {
                $verified = ($password === $stored);
            }

            if ($verified) {
            // Log activity
            $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action_type, description) VALUES (?, ?, ?)");
            $stmt->execute([$user['user_id'], 'login', 'User logged into the system']);
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['user_id'],
                    'name' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
