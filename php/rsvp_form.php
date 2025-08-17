<?php
session_start();

$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
$old = $_SESSION['old'] ?? ['jina' => '', 'barua' => '', 'ujumbe' => ''];

// Ondoa data baada ya kuonyesha ili isionekane tena kwenye reload
unset($_SESSION['errors'], $_SESSION['success'], $_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>RSVP - Thibitisha Uwepo Wako</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    /* Hapa weka style kama ulivyotaka, nimeshusha kwa kifupi kwa ajili ya muundo */
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #fce4ec, #ffe0b2);
      color: #880e4f;
      margin: 0;
      padding: 30px 15px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
    h2 { font-size: 2.4rem; font-weight: 700; margin-bottom: 25px; color: #d81b60; text-shadow: 1px 1px 3px rgba(136,14,79,0.3); }
    form {
      background: #fff0f5;
      padding: 25px 20px;
      border-radius: 20px;
      box-shadow: 0 8px 20px rgba(216, 27, 96, 0.3);
      max-width: 450px;
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 15px;
      position: relative;
    }
    input[type="text"], input[type="email"], textarea {
      padding: 12px 15px;
      border-radius: 10px;
      border: 2px solid #d81b60;
      font-size: 1.1rem;
      outline: none;
      transition: border-color 0.3s ease;
      resize: vertical;
      min-height: 40px;
      font-family: inherit;
      color: #5a004f;
      box-sizing: border-box;
      width: 100%;
    }
    input[type="text"]:focus, input[type="email"]:focus, textarea:focus {
      border-color: #880e4f;
    }
    textarea { min-height: 100px; }
    .btn {
      background-color: #d81b60;
      color: white;
      border: none;
      border-radius: 30px;
      padding: 15px;
      font-size: 1.2rem;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 6px 15px rgba(216, 27, 96, 0.4);
      transition: background-color 0.3s ease;
      user-select: none;
      width: 100%;
      max-width: 100%;
    }
    .btn:hover { background-color: #880e4f; }
    .messages { max-width: 450px; margin-bottom: 20px; }
    .messages .success { background-color: #d4edda; color: #155724; padding: 15px; border-radius: 15px; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(21, 87, 40, 0.3); }
    .messages .error { background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 15px; margin-bottom: 10px; box-shadow: 0 4px 10px rgba(114, 28, 36, 0.3); }
  </style>
</head>
<body>
  <?php include 'php/check_session.php'; ?>
<?php include 'menu.php'; ?>


  <h2>Thibitisha Uwepo Wako</h2>

  <div class="messages">
    <?php if ($success): ?>
      <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>
  </div>

  <form id="rsvpForm" action="process_rsvp.php" method="POST" novalidate>
    <input type="text" id="jina" name="jina" placeholder="Jina lako kamili" required minlength="3" maxlength="50" autocomplete="name" value="<?= htmlspecialchars($old['jina']) ?>" />
    <input type="email" id="barua" name="barua" placeholder="Barua pepe" required autocomplete="email" value="<?= htmlspecialchars($old['barua']) ?>" />
    <textarea id="ujumbe" name="ujumbe" placeholder="Ujumbe kwa wanandoa (hiari)"><?= htmlspecialchars($old['ujumbe']) ?></textarea>
    <button type="submit" class="btn">Thibitisha</button>
  </form>

</body>
</html>
