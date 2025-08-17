<?php
require_once '../config/session_check.php';
requireLogin();

// Get user info
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Kuhusu Sisi - HAMISI na SUBIRA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
<style>
html, body {
  height: 100%;
  margin: 0;
  font-family: 'Poppins', sans-serif;
  color: white;
  text-align: center;
  background: url('public/images/Love.jpg') no-repeat center center fixed;
  background-size: cover;
  position: relative;
}

/* Overlay */
body::before {
  content: "";
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.4);
  z-index: -1;
}

/* Sidebar */
.sidebar {
  height: 100%;
  width: 70px;
  background: rgba(173, 20, 87, 0.95);
  position: fixed;
  left: 0; top: 0;
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

/* Dark mode */
body.dark {
  background: url('public/images/Love.jpg') no-repeat center center fixed;
  background-size: cover;
  color: #f1f1f1;
}
body.dark::before {
  background: rgba(0,0,0,0.6);
}
body.dark .sidebar { background: rgba(30, 30, 30, 0.95); }
body.dark .info-section { background: rgba(50, 50, 50, 0.85); color: white; }
body.dark .info-section h2 { color: #ff6f91; border-bottom: 3px solid #ff6f91; }

/* Top controls */
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

/* Header */
header {
  padding: 20px;
  background: rgba(255, 192, 203, 0.85);
  border-radius: 0 0 20px 20px;
  position: sticky;
  top: 0;
  margin-left: 70px;
}
.sidebar.open ~ header { margin-left: 220px; }
header h1 {
  margin: 0;
  font-size: 2.2rem;
  color: #c71585;
}

/* Content */
main {
  padding: 60px 20px;
  margin-left: 70px;
}
.sidebar.open ~ main { margin-left: 220px; }
.info-section {
  background: rgba(255,255,255,0.85);
  border-radius: 20px;
  padding: 30px 25px;
  max-width: 700px;
  margin: auto;
  color: #5a2d2d;
}
.info-section h2 {
  color: #b76e79;
  font-weight: 700;
  border-bottom: 3px solid #b76e79;
  padding-bottom: 8px;
}

/* Footer */
footer {
  background: rgba(255, 105, 180, 0.9);
  padding: 15px;
  margin-left: 70px;
}
.sidebar.open ~ footer { margin-left: 220px; }
</style>
</head>
<body>

<!-- Sidebar -->
<div id="mySidebar" class="sidebar">
  <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
  <a href="index.html"><span>Nyumbani</span></a>
  <a href="about.html"><span>Kuhusu</span></a>
  <a href="info.html"><span>Taarifa</span></a>
  <a href="gallery.html"><span>Picha</span></a>
  <a href="rsvp.html"><span>RSVP</span></a>
  <a href="directions.html"><span>Maelekezo</span></a>
  <a href="gifts.html"><span>Zawadi</span></a>
</div>

<!-- Top controls -->
<div class="top-controls">
  <button class="control-btn" onclick="toggleDarkMode()">🌙</button>
</div>

<header>
  <h1>Kuhusu Sisi</h1>
</header>

<main>
  <section class="info-section">
    <h2>Safari Yetu ya Upendo</h2>
    <p>
      Harusi hii ni matunda ya safari ndefu ya upendo, mshikamano, na heshima ya dhati.
      Hamisi na Subira wamepita changamoto nyingi na furaha nyingi pamoja, na sasa
      wanakaribia kuungana rasmi ili kuanzisha maisha mapya ya familia yenye upendo,
      mshikamano, na maelewano.
    </p>
  </section>
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
