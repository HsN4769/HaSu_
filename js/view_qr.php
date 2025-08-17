<?php
require_once '../config/db.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    echo "⚠️ Mwanachama hajapatikana.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>QR ya Mwanachama</title>
    <style>
        body {
            background: #0f2027;
            background: linear-gradient(to right, #2c5364, #203a43, #0f2027);
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            text-align: center;
            padding: 40px;
        }

        .qr-container {
            display: inline-block;
            padding: 20px;
            background: #111;
            border-radius: 20px;
            box-shadow: 0 0 25px rgba(0, 255, 255, 0.6);
            transition: transform 0.3s ease;
        }

        .qr-container:hover {
            transform: scale(1.05);
            box-shadow: 0 0 35px rgba(0, 255, 255, 0.9);
        }

        img {
            width: 300px;
            height: auto;
            border-radius: 10px;
        }

        .details {
            margin-top: 20px;
            font-size: 18px;
        }

        .details span {
            display: block;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <h2>QR Code ya <?php echo htmlspecialchars($member['jina']); ?></h2>

    <div class="qr-container">
        <img src="generate_qr.php?id=<?php echo $member['id']; ?>" alt="QR Code">
    </div>

    <div class="details">
        <span><strong>Namba ya Simu:</strong> <?php echo $member['namba_sim']; ?></span>
        <span><strong>Status:</strong> <?php echo ucfirst($member['status']); ?></span>
        <span><strong>Tarehe:</strong> <?php echo $member['tarehe']; ?></span>
    </div>
</body>
</html>