<?php
require_once '../vendor/autoload.php';
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Logo\Logo;

if (!isset($_GET['id'])) {
    die('ID haijapatikana.');
}

$id = $_GET['id'];
$url = "https://yourdomain.com/scripts/view_qr.php?id=$id"; // Badilisha kwa domain yako halisi

$qr = QrCode::create($url)->setSize(300)->setMargin(10);

// ✅ Path sahihi ya logo
$logoPath = __DIR__ . '/js/suuh.JPG'; // Hakikisha picha ipo kwenye folder js/ ndani ya scripts/

$logo = Logo::create($logoPath)->setResizeToWidth(60);

$writer = new PngWriter();
$result = $writer->write($qr, $logo);

header('Content-Type: image/png');
echo $result->getString();