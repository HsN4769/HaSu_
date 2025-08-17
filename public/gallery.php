<?php
require_once '../config/session_check.php';
requireLogin();

// Get user info
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Albamu ya Picha & Video - Harusi ya HAMISI na SUBIRA</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<style>
  body {
    height: 100vh;
    margin: 0;
    font-family: sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    color: white;
  }

  /* Background na cinematic zoom */
  body::before {
    content: "";
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: url('public/images/Love.jpg') no-repeat center center/cover;
    z-index: -4;
    animation: zoomBG 40s ease-in-out infinite alternate;
  }
  @keyframes zoomBG {
    from { transform: scale(1); }
    to { transform: scale(1.15); }
  }

  /* Overlay ya rangi */
  body::after {
    content: "";
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: radial-gradient(circle at top left, rgba(255,105,180,0.35), rgba(255,255,0,0.25), rgba(0,255,255,0.2));
    backdrop-filter: brightness(0.8);
    z-index: -3;
  }

  /* Disco light effect */
  .disco-lights {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none;
    z-index: -2;
    background: conic-gradient(red, yellow, lime, cyan, blue, magenta, red);
    animation: spinLights 5s linear infinite;
    mix-blend-mode: overlay;
    opacity: 0.2;
  }
  @keyframes spinLights {
    0% { transform: rotate(0deg);}
    100% { transform: rotate(360deg);}
  }

  /* Sidebar */
  .sidebar {
    position: fixed;
    left: 0; top: 0;
    height: 100%;
    width: 60px;
    background-color: rgba(255,63,52,0.9);
    padding: 20px 0;
    overflow: hidden;
    border-radius: 0 8px 8px 0;
    transition: width 0.5s ease;
    z-index: 10;
  }
  .sidebar:hover { width: 200px; }
  .sidebar ul { list-style: none; padding: 0; margin: 0; }
  .sidebar ul li { margin: 15px 0; }
  .sidebar ul li a {
    display: flex;
    align-items: center;
    color: #fff;
    padding: 10px;
    text-decoration: none;
    transition: background 0.3s;
  }
  .sidebar ul li a:hover {
    background: rgba(255,255,255,0.2);
    border-radius: 6px;
  }
  .item-icon { font-size: 1.5em; min-width: 40px; text-align: center; }
  .item-txt { opacity: 0; transition: opacity 0.3s ease; }
  .sidebar:hover .item-txt { opacity: 1; margin-left: 5px; }

  /* Title */
  h1 {
    position: absolute;
    top: 20px;
    color: white;
    text-shadow: 0 0 15px rgba(0,0,0,0.6);
    font-size: 1.8rem;
    z-index: 5;
  }

  /* Music button */
  .music-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255,255,255,0.2);
    border: none;
    padding: 12px;
    border-radius: 50%;
    font-size: 1.4rem;
    color: white;
    cursor: pointer;
    z-index: 6;
    transition: background 0.3s;
  }
  .music-btn:hover {
    background: rgba(255,255,255,0.35);
  }

  /* 3D Spinner */
  .container {
    width: 250px;
    height: 400px;
    position: relative;
    transform-style: preserve-3d;
    transform: perspective(1000px);
    animation: gallery 25s linear infinite;
    cursor: pointer;
    z-index: 2;
  }
  .paused { animation-play-state: paused !important; }
  @keyframes gallery {
    from { transform: perspective(1000px) rotateY(0deg); }
    to { transform: perspective(1000px) rotateY(360deg); }
  }
  .container span {
    position: absolute;
    top: 0; left: 0;
    transform: rotateY(calc(var(--i) * 45deg)) translateZ(350px);
  }
  .container span img, 
  .container span video {
    width: 250px;
    height: 400px;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.4);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    -webkit-box-reflect: below 8px linear-gradient(transparent, rgba(255,255,255,0.15));
  }
  .container span img:hover, 
  .container span video:hover {
    transform: scale(1.05);
    box-shadow: 0 15px 35px rgba(0,0,0,0.6);
  }

  /* Confetti Canvas */
  #confettiCanvas {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none;
    z-index: 3;
  }
</style>
</head>
<body>

<!-- USER INFO -->
<div style="position:fixed;top:20px;right:20px;background:rgba(255,255,255,0.1);backdrop-filter:blur(10px);padding:10px 15px;border-radius:20px;color:white;font-size:0.9rem;z-index:1200;">
  Karibu, <?php echo htmlspecialchars($user['email']); ?>!
  <button onclick="logout()" style="background:rgba(255,0,0,0.7);border:none;color:white;padding:5px 10px;border-radius:15px;cursor:pointer;margin-left:10px;font-size:0.8rem;">Logout</button>
</div>

<!-- Disco lights -->
<div class="disco-lights"></div>

<!-- Sidebar -->
<div class="sidebar">
  <ul>
    <li><a href="index.html"><span class="item-icon"><i class='bx bxs-home'></i></span><span class="item-txt">Nyumbani</span></a></li>
    <li><a href="about.html"><span class="item-icon"><i class='bx bxs-user'></i></span><span class="item-txt">Kuhusu</span></a></li>
    <li><a href="info.html"><span class="item-icon"><i class='bx bxs-info-circle'></i></span><span class="item-txt">Taarifa</span></a></li>
    <li><a href="gallery.html"><span class="item-icon"><i class='bx bxs-photo-album'></i></span><span class="item-txt">Picha</span></a></li>
    <li><a href="rsvp.html"><span class="item-icon"><i class='bx bxs-edit'></i></span><span class="item-txt">RSVP</span></a></li>
    <li><a href="directions.html"><span class="item-icon"><i class='bx bxs-map'></i></span><span class="item-txt">Maelekezo</span></a></li>
    <li><a href="gifts.html"><span class="item-icon"><i class='bx bxs-gift'></i></span><span class="item-txt">Zawadi</span></a></li>
  </ul>
</div>

<h1>Harusi ya HAMISI na SUBIRA</h1>

<!-- Music control -->
<button class="music-btn" id="musicToggle"><i class='bx bx-play'></i></button>
<audio id="bgMusic" loop>
  <source src="music/Aste Aste (feat. Yammi).mp3" type="audio/mpeg">
</audio>

<!-- Spinner -->
<div class="container" id="gallery">
  <span style="--i:1"><img src="image/love.png" alt="Hamisi na Subira"></span>
  <span style="--i:2"><video src="video/ngayo.mp4" muted loop autoplay playsinline></video></span>
  <span style="--i:3"><img src="image/hs.jpg" alt="Hamisi"></span>
  <span style="--i:4"><video src="video/love.mp4" muted loop autoplay playsinline></video></span>
  <span style="--i:5"><img src="image/ku.jpg" alt="Mgeni"></span>
  <span style="--i:6"><img src="image/suuh.JPG" alt="Subira"></span>
  <span style="--i:7"><video src="video/ngayo.mp4" muted loop autoplay playsinline></video></span>
  <span style="--i:8"><img src="image/mk.JPG" alt="Mgeni Maalum"></span>
</div>

<!-- Confetti Canvas -->
<canvas id="confettiCanvas"></canvas>

<script>
function logout() {
  if (confirm('Una uhakika unataka kutoka?')) {
    window.location.href = '../login/logout.php';
  }
}
  // Spinner pause on hover
  const gallery = document.getElementById('gallery');
  gallery.addEventListener('mouseenter', () => gallery.classList.add('paused'));
  gallery.addEventListener('mouseleave', () => gallery.classList.remove('paused'));

  // Overlay preview
  document.querySelectorAll('.container img, .container video').forEach(el => {
    el.addEventListener('click', () => {
      const overlay = document.createElement('div');
      overlay.style.position = 'fixed';
      overlay.style.top = 0;
      overlay.style.left = 0;
      overlay.style.width = '100%';
      overlay.style.height = '100%';
      overlay.style.background = 'rgba(0,0,0,0.9)';
      overlay.style.display = 'flex';
      overlay.style.alignItems = 'center';
      overlay.style.justifyContent = 'center';
      overlay.style.zIndex = '999';
      overlay.style.cursor = 'pointer';

      let bigElement;
      if (el.tagName.toLowerCase() === 'video') {
        bigElement = document.createElement('video');
        bigElement.src = el.src;
        bigElement.controls = true;
        bigElement.autoplay = true;
      } else {
        bigElement = document.createElement('img');
        bigElement.src = el.src;
      }
      bigElement.style.maxWidth = '90%';
      bigElement.style.maxHeight = '90%';
      bigElement.style.borderRadius = '10px';
      bigElement.style.boxShadow = '0 0 30px rgba(255,255,255,0.4)';

      overlay.appendChild(bigElement);
      document.body.appendChild(overlay);

      overlay.addEventListener('click', () => document.body.removeChild(overlay));
    });
  });

  // Music control
  const music = document.getElementById('bgMusic');
  const musicBtn = document.getElementById('musicToggle');
  let isPlaying = false;
  musicBtn.addEventListener('click', () => {
    if (!isPlaying) {
      music.play();
      isPlaying = true;
      musicBtn.innerHTML = "<i class='bx bx-pause'></i>";
    } else {
      music.pause();
      isPlaying = false;
      musicBtn.innerHTML = "<i class='bx bx-play'></i>";
    }
  });

  // Confetti Effect
  const canvas = document.getElementById("confettiCanvas");
  const ctx = canvas.getContext("2d");
  let confetti = [];
  const colors = ["#ff0", "#ff5e5e", "#ff69b4", "#00e5ff", "#00ff7f", "#ff8c00"];

  function resizeCanvas() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  }
  window.addEventListener("resize", resizeCanvas);
  resizeCanvas();

  function createConfetti() {
    return {
      x: Math.random() * canvas.width,
      y: Math.random() * canvas.height - canvas.height,
      r: Math.random() * 6 + 4,
      color: colors[Math.floor(Math.random() * colors.length)],
      speed: Math.random() * 3 + 2,
      tilt: Math.random() * 10 - 10
    };
  }
  for (let i = 0; i < 150; i++) confetti.push(createConfetti());

  function drawConfetti() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    confetti.forEach(c => {
      ctx.beginPath();
      ctx.fillStyle = c.color;
      ctx.ellipse(c.x, c.y, c.r, c.r / 2, c.tilt, 0, Math.PI * 2);
      ctx.fill();
    });
  }

  function updateConfetti() {
    confetti.forEach(c => {
      c.y += c.speed;
      c.x += Math.sin(c.tilt / 2);
      c.tilt += 0.1;
      if (c.y > canvas.height) {
        c.x = Math.random() * canvas.width;
        c.y = -10;
      }
    });
  }

  function animate() {
    drawConfetti();
    updateConfetti();
    requestAnimationFrame(animate);
  }
  animate();
</script>

</body>
</html>
