<?php
/**
 * Enhanced Payment Processing Module
 * Features: Validation, security, mobile optimization, success animations
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../qr/qr_generator.php';

class PaymentProcessor {
    private $pdo;
    private $qrGenerator;
    
    public function __construct() {
        $this->pdo = getDB();
        $this->qrGenerator = new QRGenerator();
    }
    
    /**
     * Process payment with validation and security
     */
    public function processPayment($data) {
        try {
            // Validate input data
            $validation = $this->validatePaymentData($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message'],
                    'errors' => $validation['errors']
                ];
            }
            
            // Sanitize data
            $sanitizedData = $this->sanitizePaymentData($data);
            
            // Check for duplicate transactions
            if ($this->isDuplicateTransaction($sanitizedData)) {
                return [
                    'success' => false,
                    'message' => 'Duplicate transaction detected. Please try again.',
                    'errors' => ['duplicate' => 'Transaction already exists']
                ];
            }
            
            // Process payment based on method
            $paymentResult = $this->processPaymentMethod($sanitizedData);
            
            if ($paymentResult['success']) {
                // Generate QR code
                $qrCode = $this->qrGenerator->generatePaymentQR(
                    $paymentResult['payment_id'],
                    $sanitizedData['amount'],
                    $sanitizedData['payment_method']
                );
                
                // Update payment record with QR code
                $this->updatePaymentQR($paymentResult['payment_id'], $qrCode);
                
                // Log successful payment
                $this->logPaymentActivity($paymentResult['payment_id'], 'payment_completed');
                
                return [
                    'success' => true,
                    'message' => 'Payment completed successfully!',
                    'payment_id' => $paymentResult['payment_id'],
                    'qr_code' => $qrCode,
                    'qr_url' => $this->qrGenerator->getQRUrl($qrCode),
                    'transaction_id' => $paymentResult['transaction_id']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Payment processing failed: ' . $paymentResult['message'],
                    'errors' => $paymentResult['errors'] ?? []
                ];
            }
            
        } catch (Exception $e) {
            error_log("Payment processing error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.',
                'errors' => ['system' => 'Internal system error']
            ];
        }
    }
    
    /**
     * Validate payment data
     */
    private function validatePaymentData($data) {
        $errors = [];
        $required = ['name', 'email', 'amount', 'payment_method'];
        
        // Check required fields
        foreach ($required as $field) {
            if (empty(trim($data[$field] ?? ''))) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
        }
        
        // Validate email
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        // Validate amount
        if (!empty($data['amount'])) {
            $amount = floatval($data['amount']);
            if ($amount <= 0 || $amount > 1000000) { // Max 1M TZS
                $errors['amount'] = 'Amount must be between 1 and 1,000,000 TZS';
            }
        }
        
        // Validate payment method
        $validMethods = ['mpesa', 'airtel', 'tigo', 'bank', 'cash'];
        if (!empty($data['payment_method']) && !in_array($data['payment_method'], $validMethods)) {
            $errors['payment_method'] = 'Invalid payment method';
        }
        
        // Validate phone number for mobile payments
        if (in_array($data['payment_method'] ?? '', ['mpesa', 'airtel', 'tigo'])) {
            if (empty($data['phone'])) {
                $errors['phone'] = 'Phone number is required for mobile payments';
            } elseif (!preg_match('/^(\+255|0)[0-9]{9}$/', $data['phone'])) {
                $errors['phone'] = 'Invalid Tanzanian phone number format';
            }
        }
        
        return [
            'valid' => empty($errors),
            'message' => empty($errors) ? 'Validation passed' : 'Validation failed',
            'errors' => $errors
        ];
    }
    
    /**
     * Sanitize payment data
     */
    private function sanitizePaymentData($data) {
        return [
            'name' => htmlspecialchars(trim($data['name'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'email' => filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL),
            'phone' => preg_replace('/[^0-9+]/', '', $data['phone'] ?? ''),
            'amount' => floatval($data['amount'] ?? 0),
            'payment_method' => strtolower(trim($data['payment_method'] ?? '')),
            'message' => htmlspecialchars(trim($data['message'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'ip_address' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
    }
    
    /**
     * Check for duplicate transactions
     */
    private function isDuplicateTransaction($data) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM payments 
            WHERE email = ? AND amount = ? AND payment_method = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        $stmt->execute([$data['email'], $data['amount'], $data['payment_method']]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Process payment based on method
     */
    private function processPaymentMethod($data) {
        try {
            // Insert payment record
            $stmt = $this->pdo->prepare("
                INSERT INTO payments (guest_id, amount, currency, payment_method, transaction_id, status, created_at)
                VALUES (NULL, ?, 'TZS', ?, ?, NOW())
            ");
            
            $transactionId = $this->generateTransactionId($data['payment_method']);
            
            if ($stmt->execute([$data['amount'], $data['payment_method'], $transactionId])) {
                $paymentId = $this->pdo->lastInsertId();
                
                // Store guest information if provided
                if (!empty($data['name']) || !empty($data['email'])) {
                    $this->storeGuestInfo($paymentId, $data);
                }
                
                return [
                    'success' => true,
                    'payment_id' => $paymentId,
                    'transaction_id' => $transactionId,
                    'message' => 'Payment recorded successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to record payment',
                    'errors' => ['database' => 'Database error']
                ];
            }
            
        } catch (Exception $e) {
            error_log("Payment method processing error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment processing error',
                'errors' => ['system' => $e->getMessage()]
            ];
        }
    }
    
    /**
     * Generate unique transaction ID
     */
    private function generateTransactionId($method) {
        $prefix = strtoupper(substr($method, 0, 3));
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        return $prefix . $timestamp . $random;
    }
    
    /**
     * Store guest information
     */
    private function storeGuestInfo($paymentId, $data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO guests (full_name, email, phone, message, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            if ($stmt->execute([$data['name'], $data['email'], $data['phone'], $data['message']])) {
                $guestId = $this->pdo->lastInsertId();
                
                // Update payment with guest ID
                $updateStmt = $this->pdo->prepare("
                    UPDATE payments SET guest_id = ? WHERE id = ?
                ");
                $updateStmt->execute([$guestId, $paymentId]);
            }
        } catch (Exception $e) {
            error_log("Guest info storage error: " . $e->getMessage());
        }
    }
    
    /**
     * Update payment with QR code
     */
    private function updatePaymentQR($paymentId, $qrCode) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE payments SET qr_code = ? WHERE id = ?
            ");
            $stmt->execute([$qrCode, $paymentId]);
        } catch (Exception $e) {
            error_log("QR code update error: " . $e->getMessage());
        }
    }
    
    /**
     * Log payment activity
     */
    private function logPaymentActivity($paymentId, $action) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO activity_log (user_id, action, description, ip_address, user_agent)
                VALUES (NULL, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $action,
                "Payment ID: $paymentId",
                $this->getClientIP(),
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("Activity logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Get payment statistics
     */
    public function getPaymentStats() {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total_payments,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_payments,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_payments,
                    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_amount,
                    AVG(CASE WHEN status = 'completed' THEN amount ELSE NULL END) as avg_amount
                FROM payments
            ");
            
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Payment stats error: " . $e->getMessage());
            return [];
        }
    }
}

// Handle direct API calls
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
    
    $processor = new PaymentProcessor();
    $result = $processor->processPayment($_POST);
    
    echo json_encode($result);
    exit();
}
?>
