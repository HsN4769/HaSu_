<?php
// Path ya library ya QR
include __DIR__ . '/phpqrcode/qrlib.php';

// Pata data kutoka kwenye fomu
$name   = isset($_POST['name'])   ? trim($_POST['name'])   : '';
$email  = isset($_POST['email'])  ? trim($_POST['email'])  : '';
$amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';
$method = isset($_POST['method']) ? trim($_POST['method']) : '';

// Folder ya kuhifadhia QR
$qrFolder = __DIR__ . '/../image/';  // Hapa tunahifadhi QR kwenye folda ya 'image'
if (!file_exists($qrFolder)) {
    mkdir($qrFolder, 0777, true);  // Unda folder kama haipo
}

// Ikiwa data zote zimejazwa
if ($name && $email && $amount && $method) {
    // Faili la QR
    $qrFile = $qrFolder . uniqid('qr_') . ".png";  // Jina la QR Code linatokana na 'uniqid()'

    // Data ya QR
    $qrData = "Jina: $name\nEmail: $email\nKiasi: $amount TZS\nNjia: $method";  // Data itakayoonekana kwenye QR

    // Tengeneza QR code
    QRcode::png($qrData, $qrFile, QR_ECLEVEL_L, 6);  // Tengeneza QR Code na uhifadhi

    $message = "✅ Malipo yamekamilika! Hii hapa QR code yako ya uthibitisho:";  // Ujumbe wa mafanikio
    $qrFileWebPath = '../image/' . basename($qrFile);  // Path ya QR Code kwa upande wa mtandao
} else {
    $message = "⚠ Tafadhali jaza taarifa zote ili kuendelea.";  // Ikiwa data hazijakamilika
    $qrFileWebPath = '';  // Hakuna QR Code itakayoonekana
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<title>Payment Confirmation</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #fff0f5;
    text-align: center;
    padding: 40px;
}
h2 { color: #c71585; }
a {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 15px;
    background: #c71585;
    color: white;
    text-decoration: none;
    border-radius: 8px;
}
a:hover { background: #a0136e; }
</style>
</head>
<body>
    <h2><?php echo $message; ?></h2>

    <!-- Onyesha QR Code -->
    <?php if ($qrFileWebPath): ?>
        <div>
            <img src="<?php echo $qrFileWebPath; ?>" alt="QR Code" style="width:200px;height:200px;">
        </div>
        <p><strong>Data ya Malipo:</strong></p>
        <ul>
            <li><strong>Jina:</strong> <?php echo $name; ?></li>
            <li><strong>Email:</strong> <?php echo $email; ?></li>
            <li><strong>Kiasi:</strong> <?php echo $amount; ?> TZS</li>
            <li><strong>Njia ya Malipo:</strong> <?php echo $method; ?></li>
            <li><strong>Muda wa Malipo:</strong> <?php echo date('Y-m-d H:i:s'); ?></li>
        </ul>

        <!-- Viungo vya kuendelea -->
        <a href="index.html">Rudi Nyumbani</a> | <a href="payment.html">Rudi kwenye ukurasa wa malipo</a>
    <?php else: ?>
        <a href="payment.html">Rudi nyuma</a>
    <?php endif; ?>
</body>
</html>
