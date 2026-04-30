<?php
// config.php - Database connection
// Shared settings for backend API endpoints
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Toggle verbose errors in development. Set to false in production.
if (!defined('API_DEBUG')) define('API_DEBUG', true);

$host = '127.0.0.1';
$dbname = 'transaction_db';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    if (API_DEBUG) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed', 'error' => $e->getMessage()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    }
    exit;
}
?>