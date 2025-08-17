<?php
require_once '../config/db.php';

// Pokea data kutoka M-Pesa (JSON)
$data = json_decode(file_get_contents('php://input'), true);

// Extract info
$jina = $data['FirstName'] . ' ' . $data['MiddleName'] . ' ' . $data['LastName'];
$namba_sim = $data['MSISDN'];
$kiasi = $data['Amount'];
$status = 'paid';

// Ingiza kwenye database
$stmt = $pdo->prepare("INSERT INTO members (jina, namba_sim, kiasi, status) VALUES (?, ?, ?, ?)");
try {
    $stmt->execute([$jina, $namba_sim, $kiasi, $status]);
    http_response_code(200);
    echo json_encode(['message' => '✅ Malipo yamepokelewa']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>