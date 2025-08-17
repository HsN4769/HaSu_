<?php
// Include QR Code library
include 'phpqrcode/qrlib.php';

// Pata data kutoka kwenye form
$name    = isset($_POST['name']) ? trim($_POST['name']) : '';
$method  = isset($_POST['method']) ? trim($_POST['method']) : '';
$amount  = isset($_POST['amount']) ? trim($_POST['amount']) : '';

// Path ya kuhifadhi QR code
$qrFolder = "public/images/qr/";
if (!file_exists($qrFolder)) {
    mkdir($qrFolder, 0777, true);
}

if ($name && $method && $amount) {
    $qrFile = $qrFolder . uniqid("qr_") . ".png";
    $qrData = "Jina: $name\nNjia ya Malipo: $method\nKiasi: $amount";
    QRcode::png($qrData, $qrFile, QR_ECLEVEL_L, 6);
    $message = "Malipo yamekamilika! Hii hapa QR code yako ya uthibitisho:";
} else {
    $message = "Tafadhali jaza taarifa zote ili kuendelea.";
    $qrFile = "";
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Uthibitisho wa Malipo - Harusi ya HAMISI na SUBIRA</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
/* BACKGROUND */
html, body {
  height: 100%;
  margin: 0;
  font-family: 'Poppins', sans-serif;
  color: white;
  background: url('public/images/Love.jpg') no-repeat center center/cover;
  overflow-x: hidden;
}
body::before {
  content: "";
  position: fixed;
  top:0; left:0; right:0; bottom:0;
  background: linear-gradient(270deg, rgba(255,105,180,0.35), rgba(255,215,0,0.25));
  backdrop-filter: brightness(0.8);
  animation: bgMove 10s infinite alternate;
  z-index: -1;
}
@keyframes bgMove {
  0% { transform: scale(1);}
  100% { transform: scale(1.08);}
}

/* HEARTS */
.heart {
  position: fixed;
  top: -10px;
  font-size: 20px;
  color: rgba(255,255,255,0.7);
  animation: fall 8s linear infinite;
  z-index: 0;
}
@keyframes fall {
  0% {transform: translateY(-10px);}
  100% {transform: translateY(110vh);}
}

/* SIDEBAR */
.sidebar {
  height: 100%;
  width: 70px;
  background: rgba(173, 20, 87, 0.95);
  position: fixed;
  left: 0;
  top: 0;
  box-shadow: 2px 0 12px rgba(0,0,0,0.3);
  transition: width 0.3s ease;
  display: flex;
  flex-direction: column;
  z-index: 1000;
  overflow-x: hidden;
}
.sidebar.open { width: 220px; }
.sidebar .toggle-btn {
  font-size: 1.6rem;
  padding: 15px;
  cursor: pointer;
  text-align: center;
  color: white;
}
.sidebar a {
  padding: 12px 15px;
  text-decoration: none;
  color: white;
  display: flex;
  align-items: center;
  gap: 15px;
  white-space: nowrap;
}
.sidebar a:hover { background: rgba(255,255,255,0.15); }
.sidebar span { opacity: 0; transition: opacity 0.2s ease; }
.sidebar.open span { opacity: 1; }

/* DARK MODE */
body.dark {
  background: #1e1e1e;
  color: #f1f1f1;
}
body.dark .sidebar { background: rgba(30, 30, 30, 0.95); }
body.dark header { background: rgba(50,50,50,0.85); }
body.dark .payment-card { background: rgba(50,50,50,0.85); color: white; }

/* TOP CONTROLS */
.top-controls {
  position: fixed;
  top: 80px;
  left: 10px;
  z-index: 1100;
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: left 0.3s ease;
}
.sidebar.open ~ .top-controls { left: 160px; }
.control-btn {
  background: rgba(255,255,255,0.15);
  border: none;
  padding: 12px;
  border-radius: 50%;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.3s ease;
}
.control-btn:hover {
  background: rgba(255,255,255,0.3);
  transform: scale(1.15);
}

/* HEADER */
header {
  padding: 20px;
  background: rgba(255, 192, 203, 0.85);
  border-radius: 0 0 20px 20px;
  position: sticky;
  top: 0;
  margin-left: 70px;
  text-align: center;
  box-shadow: 0 6px 20px rgba(255,105,180,0.6);
}
.sidebar.open ~ header { margin-left: 220px; }
header h1 {
  margin: 0;
  font-size: 2.2rem;
  color: #c71585;
}

/* PAYMENT CARD */
.payment-card {
  background: rgba(255,255,255,0.9);
  color: #333;
  border-radius: 15px;
  padding: 20px;
  width: 300px;
  text-align: center;
  margin: 40px auto;
  box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}
.payment-card img {
  width: 200px;
  height: 200px;
  border-radius: 10px;
}
.payment-card h3 {
  color: #b76e79;
  margin-bottom: 10px;
}
.payment-card p {
  font-weight: bold;
  color: #555;
}

/* FOOTER */
footer {
  background: rgba(255, 105, 180, 0.9);
  padding: 15px;
  margin-left: 70px;
  text-align: center;
}
.sidebar.open ~ footer { margin-left: 220px; }
</style>
</head>
<body>

<!-- HEARTS -->
<script>
for (let i = 0; i < 15; i++) {
  let heart = document.createElement("div");
  heart.classList.add("heart");
  heart.style.left = Math.random() * 100 + "vw";
  heart.style.animationDuration = (4 + Math.random() * 6) + "s";
  heart.innerHTML = "💖";
  document.body.appendChild(heart);
}
</script>

<!-- SIDEBAR -->
<div id="mySidebar" class="sidebar">
  <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
  <a href="index.html"><i class="fas fa-home"></i><span>Nyumbani</span></a>
  <a href="about.html"><i class="fas fa-info-circle"></i><span>Kuhusu</span></a>
  <a href="info.html"><i class="fas fa-file-alt"></i><span>Taarifa</span></a>
  <a href="gallery.html"><i class="fas fa-image"></i><span>Picha</span></a>
  <a href="rsvp.html"><i class="fas fa-envelope-open-text"></i><span>RSVP</span></a>
  <a href="directions.html"><i class="fas fa-map-marker-alt"></i><span>Maelekezo</span></a>
  <a href="gifts.html"><i class="fas fa-gift"></i><span>Zawadi</span></a>
  <a href="payment.html"><i class="fas fa-money-bill-wave"></i><span>Malipo</span></a>
</div>

<!-- TOP CONTROLS -->
<div class="top-controls">
  <button class="control-btn" onclick="toggleDarkMode()">🌙</button>
</div>

<header>
  <h1>Uthibitisho wa Malipo</h1>
</header>

<main>
  <div class="payment-card">
    <h3><?php echo $message; ?></h3>
    <?php if ($qrFile): ?>
      <img src="<?php echo $qrFile; ?>" alt="QR Code">
      <p><?php echo nl2br(htmlspecialchars($qrData)); ?></p>
    <?php endif; ?>
    <br>
    <a href="payment.html" style="display:inline-block;margin-top:10px;color:#c71585;font-weight:bold;">⬅ Rudi kwenye Malipo</a>
  </div>
</main>

<footer>
  &copy; 2025 Harusi ya HAMISI na SUBIRA.
</footer>

<script>
function toggleSidebar() {
  document.getElementById("mySidebar").classList.toggle("open");
}
function toggleDarkMode() {
  document.body.classList.toggle("dark");
  localStorage.setItem("darkMode", document.body.classList.contains("dark"));
}
if (localStorage.getItem("darkMode") === "true") {
  document.body.classList.add("dark");
}
</script>

</body>
</html>
