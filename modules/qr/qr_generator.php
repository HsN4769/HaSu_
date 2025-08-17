<?php
/**
 * Enhanced QR Code Generator
 * Features: Logo embedding, glowing effects, custom styling
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/phpqrcode/qrlib.php';

class QRGenerator {
    private $pdo;
    private $qrFolder;
    private $logoPath;
    
    public function __construct() {
        $this->pdo = getDB();
        $this->qrFolder = __DIR__ . '/../../temp/qr_codes/';
        $this->logoPath = __DIR__ . '/../../vendor/phpqrcode/logo.png';
        
        // Create QR codes directory if it doesn't exist
        if (!file_exists($this->qrFolder)) {
            mkdir($this->qrFolder, 0777, true);
        }
    }
    
    /**
     * Generate QR code with logo and styling
     */
    public function generateStyledQR($data, $filename = null, $size = 300) {
        if (!$filename) {
            $filename = uniqid('qr_') . '.png';
        }
        
        $qrPath = $this->qrFolder . $filename;
        
        // Generate basic QR code
        QRcode::png($data, $qrPath, 'H', 10, 2);
        
        // Apply styling and logo
        $this->applyStyling($qrPath, $size);
        
        return $filename;
    }
    
    /**
     * Apply custom styling to QR code
     */
    private function applyStyling($qrPath, $size) {
        // Load QR code image
        $qrImage = imagecreatefrompng($qrPath);
        
        // Create new image with desired size
        $newImage = imagecreatetruecolor($size, $size);
        
        // Enable alpha blending
        imagealphablending($newImage, true);
        imagesavealpha($newImage, true);
        
        // Create transparent background
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);
        
        // Resize QR code
        imagecopyresampled($newImage, $qrImage, 0, 0, 0, 0, $size, $size, imagesx($qrImage), imagesy($qrImage));
        
        // Add glowing border
        $this->addGlowingBorder($newImage, $size);
        
        // Add logo if exists and is valid
        if (file_exists($this->logoPath) && is_file($this->logoPath)) {
            $this->addLogo($newImage, $size);
        }
        
        // Add decorative elements
        $this->addDecorations($newImage, $size);
        
        // Save styled QR code
        imagepng($newImage, $qrPath);
        
        // Clean up
        imagedestroy($qrImage);
        imagedestroy($newImage);
    }
    
    /**
     * Add glowing border effect
     */
    private function addGlowingBorder($image, $size) {
        $glowColor = imagecolorallocate($image, 255, 105, 180); // Pink glow
        $borderWidth = 20;
        
        // Create gradient glow effect
        for ($i = 0; $i < $borderWidth; $i++) {
            $alpha = (int)(127 * (1 - $i / $borderWidth));
            $glowColor = imagecolorallocatealpha($image, 255, 105, 180, $alpha);
            
            // Draw glowing border
            imagerectangle($image, $i, $i, $size - $i - 1, $size - $i - 1, $glowColor);
        }
    }
    
    /**
     * Add logo to center of QR code
     */
    private function addLogo($image, $size) {
        $logo = imagecreatefrompng($this->logoPath);
        $logoSize = min($size * 0.2, 60); // Logo size relative to QR code
        
        // Calculate logo position (center)
        $logoX = ($size - $logoSize) / 2;
        $logoY = ($size - $logoSize) / 2;
        
        // Create white background for logo
        $whiteBg = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, $logoX - 5, $logoY - 5, $logoX + $logoSize + 5, $logoY + $logoSize + 5, $whiteBg);
        
        // Resize and place logo
        $tempLogo = imagecreatetruecolor($logoSize, $logoSize);
        imagealphablending($tempLogo, false);
        imagesavealpha($tempLogo, true);
        imagecopyresampled($tempLogo, $logo, 0, 0, 0, 0, $logoSize, $logoSize, imagesx($logo), imagesy($logo));
        
        // Copy logo to main image
        imagecopy($image, $tempLogo, $logoX, $logoY, 0, 0, $logoSize, $logoSize);
        
        // Clean up
        imagedestroy($logo);
        imagedestroy($tempLogo);
    }
    
    /**
     * Add decorative elements
     */
    private function addDecorations($image, $size) {
        // Add corner decorations
        $decorColor = imagecolorallocate($image, 255, 215, 0); // Gold color
        
        // Corner hearts
        $heartSize = 15;
        $this->drawHeart($image, $heartSize, $heartSize, $heartSize, $decorColor);
        $this->drawHeart($image, $size - $heartSize * 2, $heartSize, $heartSize, $decorColor);
        $this->drawHeart($image, $heartSize, $size - $heartSize * 2, $heartSize, $decorColor);
        $this->drawHeart($image, $size - $heartSize * 2, $size - $heartSize * 2, $heartSize, $decorColor);
    }
    
    /**
     * Draw heart shape
     */
    private function drawHeart($image, $x, $y, $size, $color) {
        // Simple heart shape using filled polygons
        $points = [
            $x + $size/2, $y + $size/4,           // Top point
            $x + $size/4, $y + $size/2,           // Left curve
            $x + $size/4, $y + $size * 3/4,      // Left bottom
            $x + $size/2, $y + $size,             // Bottom point
            $x + $size * 3/4, $y + $size * 3/4,  // Right bottom
            $x + $size * 3/4, $y + $size/2        // Right curve
        ];
        
        imagefilledpolygon($image, $points, 6, $color);
    }
    
    /**
     * Generate QR code for guest registration
     */
    public function generateGuestQR($guestId, $guestName) {
        $data = json_encode([
            'type' => 'guest',
            'id' => $guestId,
            'name' => $guestName,
            'timestamp' => time()
        ]);
        
        $filename = 'guest_' . $guestId . '_' . uniqid() . '.png';
        return $this->generateStyledQR($data, $filename);
    }
    
    /**
     * Generate QR code for payment
     */
    public function generatePaymentQR($paymentId, $amount, $method) {
        $data = json_encode([
            'type' => 'payment',
            'id' => $paymentId,
            'amount' => $amount,
            'method' => $method,
            'timestamp' => time()
        ]);
        
        $filename = 'payment_' . $paymentId . '_' . uniqid() . '.png';
        return $this->generateStyledQR($data, $filename);
    }
    
    /**
     * Get QR code URL
     */
    public function getQRUrl($filename) {
        return 'temp/qr_codes/' . $filename;
    }
    
    /**
     * Clean up old QR codes
     */
    public function cleanupOldCodes($maxAge = 86400) { // 24 hours
        $files = glob($this->qrFolder . '*.png');
        $now = time();
        
        foreach ($files as $file) {
            if ($now - filemtime($file) > $maxAge) {
                unlink($file);
            }
        }
    }
}

// Usage example:
if (isset($_GET['generate'])) {
    $qrGen = new QRGenerator();
    
    switch ($_GET['generate']) {
        case 'guest':
            $guestId = $_GET['id'] ?? 1;
            $guestName = $_GET['name'] ?? 'Guest';
            $filename = $qrGen->generateGuestQR($guestId, $guestName);
            echo "Guest QR generated: " . $qrGen->getQRUrl($filename);
            break;
            
        case 'payment':
            $paymentId = $_GET['id'] ?? 1;
            $amount = $_GET['amount'] ?? 10000;
            $method = $_GET['method'] ?? 'mpesa';
            $filename = $qrGen->generatePaymentQR($paymentId, $amount, $method);
            echo "Payment QR generated: " . $qrGen->getQRUrl($filename);
            break;
    }
}
?>
