<?php 
require_once 'php/check_session.php';
requireLogin(); // Zuia wasio login
?>
<?php include 'menu.php'; ?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Hadithi ya upendo ya Harusi ya Hamisi na Subira - Tazama picha, soma hadithi yetu, na shiriki nasi siku yetu maalum.">
  <meta name="keywords" content="Harusi, Hamisi, Subira, Upendo, Wedding, Love Story">
  <title>Hadithi Yetu - Harusi ya HAMISI na SUBIRA</title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #fff8f0;
      color: #333;
      text-align: center;
    }
    h2 {
      font-family: 'Great Vibes', cursive;
      font-size: 2.5em;
      color: #e91e63;
      margin-top: 30px;
    }
    .divider {
      width: 60px;
      height: 4px;
      background-color: #e91e63;
      margin: 10px auto 30px;
    }
    .photo-row {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .circle-img {
      width: 250px;
      height: 250px;
      object-fit: cover;
      border-radius: 50%;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      cursor: pointer;
      transition: transform 0.3s;
    }
    .circle-img:hover {
      transform: scale(1.05);
    }
    p {
      max-width: 700px;
      margin: 0 auto 30px;
      font-size: 1.1em;
      line-height: 1.6;
    }
    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      padding-top: 60px;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.8);
    }
    .modal-content {
      margin: auto;
      display: block;
      max-width: 80%;
    }
    .close {
      position: absolute;
      top: 20px;
      right: 35px;
      color: #fff;
      font-size: 40px;
      font-weight: bold;
      cursor: pointer;
    }
    @media screen and (max-width: 600px) {
      .circle-img {
        width: 180px;
        height: 180px;
      }
    }
  </style>
</head>
<body>

  <main>
    <h2>Hadithi Yetu</h2>
    <div class="divider"></div>

    <div class="photo-row">
      <img src="image/mk.JPG" alt="Hamisi na Subira wakiwa pamoja" class="circle-img" onclick="openModal(this)" />
      <img src="image/ku.jpg" alt="Mgeni akipumzika kwenye benchi" class="circle-img" onclick="openModal(this)" />
    </div>

    <p>
      Tulikutana mwaka 2024 maeneo ya mbagala.
      Tangu siku hiyo, urafiki wetu ulianza kukua na hatimaye tukajua kuwa tulikuwa pamoja kwa ajili ya kila mmoja.
      Safari yetu imekuwa ya upendo, migongano, msaada, na ndoto za pamoja.
      Tunamshukuru ALLAH kwa kila jambo, bila yeye sio sisi leo kuwa hapa, kwahiyo KARIBUNI SANA.
    </p>
  </main>

  <!-- Modal -->
  <div id="myModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage">
  </div>

  <script>
    function openModal(img) {
      document.getElementById("myModal").style.display = "block";
      document.getElementById("modalImage").src = img.src;
    }
    function closeModal() {
      document.getElementById("myModal").style.display = "none";
    }
    window.onclick = function(event) {
      if (event.target == document.getElementById("myModal")) {
        closeModal();
      }
    }
  </script>

</body>
</html>
