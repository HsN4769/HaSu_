<?php
/**
 * Dashboard Data API
 * Provides real-time data for admin dashboard
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modules/payment/payment_processor.php';

try {
    $pdo = getDB();
    $paymentProcessor = new PaymentProcessor();
    
    // Get guest statistics
    $guestStats = $pdo->query("
        SELECT 
            COUNT(*) as total_guests,
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_guests,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_guests,
            SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) as declined_guests,
            SUM(guest_count) as total_guest_count
        FROM guests
    ")->fetch();
    
    // Get payment statistics
    $paymentStats = $paymentProcessor->getPaymentStats();
    
    // Get recent guests
    $recentGuests = $pdo->query("
        SELECT * FROM guests ORDER BY created_at DESC LIMIT 10
    ")->fetchAll();
    
    // Get recent payments
    $recentPayments = $pdo->query("
        SELECT p.*, g.full_name, g.email 
        FROM payments p 
        LEFT JOIN guests g ON p.guest_id = g.id 
        ORDER BY p.created_at DESC LIMIT 10
    ")->fetchAll();
    
    // Get payment method distribution
    $paymentMethods = $pdo->query("
        SELECT 
            payment_method,
            COUNT(*) as count,
            SUM(amount) as total_amount
        FROM payments 
        WHERE status = 'completed'
        GROUP BY payment_method
        ORDER BY count DESC
    ")->fetchAll();
    
    // Get daily registration trend (last 7 days)
    $dailyTrend = $pdo->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as guest_count,
            SUM(guest_count) as total_guests
        FROM guests 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ")->fetchAll();
    
    $response = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'guestStats' => $guestStats,
            'paymentStats' => $paymentStats,
            'recentGuests' => $recentGuests,
            'recentPayments' => $recentPayments,
            'paymentMethods' => $paymentMethods,
            'dailyTrend' => $dailyTrend
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Dashboard data error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch dashboard data',
        'error' => 'Internal server error'
    ]);
}
?>
