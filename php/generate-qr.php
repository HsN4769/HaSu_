<?php
// Include QR Code library
include 'phpqrcode/qrlib.php';

// Pata data kutoka URL
$name   = isset($_GET['name']) ? $_GET['name'] : '';
$email  = isset($_GET['email']) ? $_GET['email'] : '';
$amount = isset($_GET['amount']) ? $_GET['amount'] : '';
$method = isset($_GET['method']) ? $_GET['method'] : '';

// Kagua kama kuna data
if ($name || $email || $amount || $method) {
    // Unganisha data
    $qrData = "Jina: $name\nEmail: $email\nKiasi: $amount TZS\nNjia: $method";
} else {
    $qrData = "Hakuna taarifa za malipo";
}

// Header ili itoe image
header('Content-Type: image/png');

// Tengeneza QR
QRcode::png($qrData, false, QR_ECLEVEL_L, 6);
