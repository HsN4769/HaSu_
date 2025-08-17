<?php
// SETTINGS
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'your_database_name'; // ← badilisha hii

// STYLING
echo "<style>
    body { font-family: 'Segoe UI', sans-serif; background: #f0f4f8; padding: 20px; }
    h2 { color: #333; text-shadow: 0 0 5px #aaa; }
    .section { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
    .ok { color: green; font-weight: bold; }
    .fail { color: red; font-weight: bold; }
    .glow { text-shadow: 0 0 8px #00f6ff; color: #0077cc; }
</style>";

echo "<h2 class='glow'>💡 Website Diagnostics Dashboard</h2>";

// 1. Database Connection
echo "<div class='section'><h3>🧩 Database Connection</h3>";
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    echo "<p class='fail'>❌ Connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p class='ok'>✅ Connected to database: <strong>$db_name</strong></p>";
}
$conn->close();
echo "</div>";

// 2. Folder Sizes
function folderSize($dir) {
    $size = 0;
    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $file) {
        $size += is_file($file) ? filesize($file) : folderSize($file);
    }
    return $size;
}
echo "<div class='section'><h3>📁 Folder Sizes</h3>";
$folders = ['.', './assets', './uploads', './qr', './admin'];
foreach ($folders as $f) {
    if (is_dir($f)) {
        $sizeMB = round(folderSize($f) / 1048576, 2);
        echo "<p>📂 <strong>$f</strong>: $sizeMB MB</p>";
    } else {
        echo "<p class='fail'>⚠️ Folder <strong>$f</strong> not found.</p>";
    }
}
echo "</div>";

// 3. PHP Error Log
echo "<div class='section'><h3>⚠️ PHP Error Log</h3>";
$log_path = ini_get('error_log');
if ($log_path && file_exists($log_path)) {
    $errors = file($log_path);
    $last = array_slice($errors, -5);
    echo "<pre style='background:#f9f9f9;padding:10px;border:1px solid #ccc;'>";
    echo implode("", $last);
    echo "</pre>";
} else {
    echo "<p>No error log found or logging disabled.</p>";
}
echo "</div>";

// 4. File Status
echo "<div class='section'><h3>📄 File Status</h3>";
$files = ['index.php', 'dashboard.php', 'qr.php', 'payment.php'];
foreach ($files as $file) {
    echo file_exists($file)
        ? "<p class='ok'>✅ <strong>$file</strong> exists.</p>"
        : "<p class='fail'>❌ <strong>$file</strong> missing!</p>";
}
echo "</div>";

// 5. QR Code Check
echo "<div class='section'><h3>🔍 QR Code Check</h3>";
$qr_folder = './qr';
$qr_files = glob($qr_folder . '/*.png');
if ($qr_files && count($qr_files) > 0) {
    echo "<p class='ok'>✅ Found " . count($qr_files) . " QR codes in <strong>$qr_folder</strong></p>";
} else {
    echo "<p class='fail'>❌ No QR codes found in <strong>$qr_folder</strong></p>";
}
echo "</div>"; 

// 6. Payment Endpoint Test
echo "<div class='section'><h3>💳 Payment Endpoint Test</h3>";
$payment_url = 'http://localhost/payment.php'; // ← badilisha kwa ngrok URL ukitumia public
$response = @file_get_contents($payment_url);
if ($response) {
    echo "<p class='ok'>✅ Payment endpoint is reachable.</p>";
} else {
    echo "<p class='fail'>❌ Cannot reach payment endpoint at <strong>$payment_url</strong></p>";
}
echo "</div>";

// Footer
echo "<p style='font-size:12px;color:#888;'>Generated on " . date('Y-m-d H:i:s') . "</p>";
?>