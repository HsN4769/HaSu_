<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jina = $_POST['jina'] ?? '';
    $namba_sim = $_POST['namba_sim'] ?? '';
    $kiasi = $_POST['kiasi'] ?? 0;
    $status = $_POST['status'] ?? 'pending';

    if ($jina && $namba_sim && $kiasi > 0) {
        $stmt = $pdo->prepare("INSERT INTO members (jina, namba_sim, kiasi, status) VALUES (?, ?, ?, ?)");
        try {
           $stmt->execute([$jina, $namba_sim, $kiasi, $status]);
$id = $pdo->lastInsertId();
header("Location: view_qr.php?id=" . $id);
exit;
        }
    } else {
        echo "⚠️ Tafadhali jaza taarifa zote.";
    }
} else {
    echo "⛔ Hakuna data iliyotumwa.";
}
?>