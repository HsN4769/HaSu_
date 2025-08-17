<?php
/**
 * Enhanced Guest Registration Module
 * Features: Validation, CORS support, mobile optimization, ngrok compatibility
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../qr/qr_generator.php';

class GuestRegistration {
    private $pdo;
    private $qrGenerator;
    
    public function __construct() {
        $this->pdo = getDB();
        $this->qrGenerator = new QRGenerator();
    }
    
    /**
     * Process guest registration
     */
    public function registerGuest($data) {
        try {
            // Validate input data
            $validation = $this->validateGuestData($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message'],
                    'errors' => $validation['errors']
                ];
            }
            
            // Sanitize data
            $sanitizedData = $this->sanitizeGuestData($data);
            
            // Check for duplicate registration
            if ($this->isDuplicateRegistration($sanitizedData)) {
                return [
                    'success' => false,
                    'message' => 'You have already registered for this wedding.',
                    'errors' => ['duplicate' => 'Registration already exists']
                ];
            }
            
            // Store guest information
            $guestId = $this->storeGuestInfo($sanitizedData);
            
            if ($guestId) {
                // Generate QR code
                $qrCode = $this->qrGenerator->generateGuestQR($guestId, $sanitizedData['full_name']);
                
                // Update guest with QR code
                $this->updateGuestQR($guestId, $qrCode);
                
                // Log registration activity
                $this->logRegistrationActivity($guestId, 'guest_registered');
                
                return [
                    'success' => true,
                    'message' => 'Registration completed successfully! Welcome to our wedding!',
                    'guest_id' => $guestId,
                    'qr_code' => $qrCode,
                    'qr_url' => $this->qrGenerator->getQRUrl($qrCode),
                    'registration_number' => 'REG-' . str_pad($guestId, 6, '0', STR_PAD_LEFT)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to complete registration. Please try again.',
                    'errors' => ['database' => 'Database error']
                ];
            }
            
        } catch (Exception $e) {
            error_log("Guest registration error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.',
                'errors' => ['system' => 'Internal system error']
            ];
        }
    }
    
    /**
     * Validate guest data
     */
    private function validateGuestData($data) {
        $errors = [];
        $required = ['full_name', 'email'];
        
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
        
        // Validate phone number
        if (!empty($data['phone'])) {
            if (!preg_match('/^(\+255|0)[0-9]{9}$/', $data['phone'])) {
                $errors['phone'] = 'Invalid Tanzanian phone number format';
            }
        }
        
        // Validate guest count
        if (!empty($data['guest_count'])) {
            $guestCount = intval($data['guest_count']);
            if ($guestCount < 1 || $guestCount > 10) {
                $errors['guest_count'] = 'Guest count must be between 1 and 10';
            }
        }
        
        // Validate message length
        if (!empty($data['message']) && strlen($data['message']) > 500) {
            $errors['message'] = 'Message must be less than 500 characters';
        }
        
        return [
            'valid' => empty($errors),
            'message' => empty($errors) ? 'Validation passed' : 'Validation failed',
            'errors' => $errors
        ];
    }
    
    /**
     * Sanitize guest data
     */
    private function sanitizeGuestData($data) {
        return [
            'full_name' => htmlspecialchars(trim($data['full_name'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'email' => filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL),
            'phone' => preg_replace('/[^0-9+]/', '', $data['phone'] ?? ''),
            'guest_count' => intval($data['guest_count'] ?? 1),
            'message' => htmlspecialchars(trim($data['message'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'ip_address' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'source' => $data['source'] ?? 'web'
        ];
    }
    
    /**
     * Check for duplicate registration
     */
    private function isDuplicateRegistration($data) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM guests 
            WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
        ");
        $stmt->execute([$data['email']]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Store guest information
     */
    private function storeGuestInfo($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO guests (full_name, email, phone, guest_count, message, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ");
            
            if ($stmt->execute([
                $data['full_name'],
                $data['email'],
                $data['phone'],
                $data['guest_count'],
                $data['message']
            ])) {
                return $this->pdo->lastInsertId();
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Guest storage error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update guest with QR code
     */
    private function updateGuestQR($guestId, $qrCode) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE guests SET qr_code = ? WHERE id = ?
            ");
            $stmt->execute([$qrCode, $guestId]);
        } catch (Exception $e) {
            error_log("QR code update error: " . $e->getMessage());
        }
    }
    
    /**
     * Log registration activity
     */
    private function logRegistrationActivity($guestId, $action) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO activity_log (user_id, action, description, ip_address, user_agent)
                VALUES (NULL, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $action,
                "Guest ID: $guestId registered",
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
     * Get registration statistics
     */
    public function getRegistrationStats() {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total_registrations,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_registrations,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_registrations,
                    SUM(guest_count) as total_guest_count,
                    AVG(guest_count) as avg_guest_count
                FROM guests
            ");
            
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Registration stats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search guests
     */
    public function searchGuests($query, $limit = 20) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM guests 
                WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ?
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Guest search error: " . $e->getMessage());
            return [];
        }
    }
}

// Handle direct API calls
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set CORS headers for ngrok compatibility
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
    header('Access-Control-Max-Age: 86400');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
    
    $registration = new GuestRegistration();
    $result = $registration->registerGuest($_POST);
    
    echo json_encode($result);
    exit();
}
?>
