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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RSVP - Thibitisha Uwepo Wako</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* BACKGROUND */
        html, body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(270deg, #ff6f91, #ff9671, #ffc75f, #f9f871);
            background-size: 800% 800%;
            animation: bgGradient 20s ease infinite;
            color: white;
            min-height: 100vh;
        }
        @keyframes bgGradient {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        /* HEARTS ANIMATION */
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
        body.dark .rsvp-card { background: rgba(50, 50, 50, 0.85); }

        /* TOP CONTROLS */
        .top-controls {
            position: fixed;
            top: 80px;
            left: 10px;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
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
            transform: scale(1.1);
        }

        /* HEADER */
        header {
            padding: 20px;
            background: rgba(255, 192, 203, 0.85);
            border-radius: 0 0 20px 20px;
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

        /* MAIN CONTAINER */
        main {
            padding: 40px 20px;
            margin-left: 70px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: calc(100vh - 200px);
        }
        .sidebar.open ~ main { margin-left: 220px; }

        /* RSVP CARD */
        .rsvp-card {
            background: rgba(255,255,255,0.95);
            color: #5a2d2d;
            border-radius: 25px;
            padding: 35px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            border: 3px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .rsvp-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, #ff6b9d, #c44569, #f093fb, #f5576c);
            background-size: 400% 400%;
            animation: glowing 3s ease-in-out infinite alternate;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }
        .rsvp-card:hover::before {
            opacity: 0.05;
        }
        .rsvp-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            border-color: #ff6b9d;
        }
        @keyframes glowing {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .rsvp-card h2 {
            text-align: center;
            color: #b76e79;
            margin-bottom: 30px;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        /* FORM GROUPS */
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #555;
            font-size: 1.1rem;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border-radius: 15px;
            border: 2px solid #e1e5e9;
            font-size: 1rem;
            font-family: inherit;
            box-sizing: border-box;
            transition: all 0.3s ease;
            background: white;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #ff6b9d;
            box-shadow: 0 0 20px rgba(255,107,157,0.3);
            outline: none;
            transform: translateY(-2px);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        /* PHONE FIELD STYLING */
        .phone-group {
            position: relative;
        }
        .phone-group input {
            padding-left: 50px;
        }
        .phone-prefix {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* GUEST COUNT STYLING */
        .guest-count-group {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .guest-count-group input {
            max-width: 100px;
            text-align: center;
        }
        .guest-count-info {
            font-size: 0.9rem;
            color: #666;
            font-style: italic;
        }

        /* SUBMIT BUTTON */
        .submit-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(45deg, #ff6b9d, #c44569);
            border: none;
            border-radius: 25px;
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .submit-btn:hover::before {
            left: 100%;
        }
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255,107,157,0.4);
        }
        .submit-btn:active {
            transform: translateY(-1px);
        }
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* SUCCESS MESSAGE */
        .success-message {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            margin-top: 25px;
            display: none;
            animation: slideInUp 0.5s ease;
            box-shadow: 0 10px 30px rgba(40,167,69,0.3);
        }
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .success-message h3 {
            margin: 0 0 20px 0;
            font-size: 1.8rem;
        }
        .success-message p {
            margin: 0 0 25px 0;
            font-size: 1.2rem;
        }
        .qr-code-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin: 25px 0;
            display: inline-block;
        }
        .qr-code-container img {
            max-width: 200px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* ERROR MESSAGE */
        .error-message {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
            display: none;
            animation: shake 0.5s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* LOADING SPINNER */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* FOOTER */
        footer {
            background: rgba(255, 105, 180, 0.9);
            padding: 15px;
            margin-left: 70px;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .sidebar.open ~ footer { margin-left: 220px; }

        /* RESPONSIVE DESIGN */
        @media (max-width: 768px) {
            .rsvp-card {
                margin: 20px;
                padding: 25px;
            }
            header h1 {
                font-size: 1.8rem;
            }
            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 12px;
                font-size: 16px; /* Prevents zoom on iOS */
            }
            .submit-btn {
                padding: 15px;
            }
            .guest-count-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        @media (max-width: 480px) {
            main {
                padding: 20px 10px;
            }
            .rsvp-card {
                padding: 20px;
                border-radius: 20px;
            }
            .rsvp-card h2 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <!-- HEARTS -->
    <script>
        for (let i = 0; i < 20; i++) {
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
        <a href="rsvp-enhanced.html"><i class="fas fa-envelope-open-text"></i><span>RSVP</span></a>
        <a href="directions.html"><i class="fas fa-map-marker-alt"></i><span>Maelekezo</span></a>
        <a href="gifts.html"><i class="fas fa-gift"></i><span>Zawadi</span></a>
        <a href="payment-enhanced.html"><i class="fas fa-money-bill-wave"></i><span>Malipo</span></a>
    </div>

    <!-- TOP CONTROLS -->
    <div class="top-controls">
        <button class="control-btn" onclick="toggleDarkMode()">🌙</button>
    </div>

    <!-- HEADER -->
    <header>
        <h1>💌 RSVP - Thibitisha Uwepo Wako</h1>
    </header>

    <!-- MAIN CONTENT -->
    <main>
        <div class="rsvp-card">
            <h2><i class="fas fa-heart"></i> Jaza Taarifa Zako</h2>
            
            <form id="rsvpForm">
                <div class="form-group">
                    <label for="full_name"><i class="fas fa-user"></i> Jina Kamili</label>
                    <input type="text" id="full_name" name="full_name" required placeholder="Jina lako kamili">
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Barua Pepe</label>
                    <input type="email" id="email" name="email" required placeholder="barua@example.com">
                </div>

                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Namba ya Simu (Hiari)</label>
                    <div class="phone-group">
                        <span class="phone-prefix">+255</span>
                        <input type="tel" id="phone" name="phone" placeholder="7XXXXXXXX" pattern="[0-9]{9}" title="Tafadhali weka namba ya simu sahihi">
                    </div>
                </div>

                <div class="form-group">
                    <label for="guest_count"><i class="fas fa-users"></i> Idadi ya Wageni</label>
                    <div class="guest-count-group">
                        <input type="number" id="guest_count" name="guest_count" min="1" max="10" value="1" required>
                        <span class="guest-count-info">Utaweza kuleta wageni hadi 10</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="message"><i class="fas fa-comment"></i> Ujumbe kwa Wanandoa (Hiari)</label>
                    <textarea id="message" name="message" placeholder="Andika ujumbe wako kwa wanandoa..."></textarea>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="btn-text"><i class="fas fa-paper-plane"></i> Thibitisha Uwepo</span>
                    <span class="btn-loading" style="display: none;">
                        <span class="loading-spinner"></span>Inatumwa...
                    </span>
                </button>
            </form>

            <!-- SUCCESS MESSAGE -->
            <div class="success-message" id="successMessage">
                <h3><i class="fas fa-check-circle"></i> Uwepo Wako Umethibitishwa!</h3>
                <p>Asante kwa kuthibitisha uwepo wako! Tutawasiliana nawe hivi karibuni.</p>
                <div class="qr-code-container" id="qrContainer">
                    <!-- QR code will be displayed here -->
                </div>
                <p><strong>Namba ya Usajili:</strong> <span id="registrationNumber"></span></p>
                <button onclick="resetForm()" class="submit-btn" style="max-width: 250px; margin-top: 20px;">
                    <i class="fas fa-plus"></i> Usajili Mwingine
                </button>
            </div>

            <!-- ERROR MESSAGE -->
            <div class="error-message" id="errorMessage">
                <!-- Error details will be displayed here -->
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer>
        &copy; 2025 Harusi ya HAMISI na SUBIRA.
    </footer>

    <script>
        // Sidebar toggle
        function toggleSidebar() {
            document.getElementById("mySidebar").classList.toggle("open");
        }

        // Dark mode toggle
        function toggleDarkMode() {
            document.body.classList.toggle("dark");
            localStorage.setItem("darkMode", document.body.classList.contains("dark"));
        }

        // Load dark mode preference
        if (localStorage.getItem("darkMode") === "true") {
            document.body.classList.add("dark");
        }

        // Form submission
        document.getElementById('rsvpForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            
            // Show loading state
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-block';
            
            // Hide previous messages
            document.getElementById('successMessage').style.display = 'none';
            document.getElementById('errorMessage').style.display = 'none';
            
            try {
                // Prepare form data
                const formData = new FormData(this);
                
                // Send registration request
                const response = await fetch('modules/registration/guest_registration.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success message
                    showSuccessMessage(result);
                } else {
                    // Show error message
                    showErrorMessage(result.message, result.errors);
                }
                
            } catch (error) {
                console.error('Error:', error);
                showErrorMessage('Kuna tatizo la mtandao. Tafadhali jaribu tena.');
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                btnText.style.display = 'inline-block';
                btnLoading.style.display = 'none';
            }
        });

        // Show success message
        function showSuccessMessage(result) {
            const successMsg = document.getElementById('successMessage');
            const qrContainer = document.getElementById('qrContainer');
            const registrationNumber = document.getElementById('registrationNumber');
            
            // Set registration number
            registrationNumber.textContent = result.registration_number || 'N/A';
            
            // Show QR code if available
            if (result.qr_code) {
                qrContainer.innerHTML = `<img src="${result.qr_url}" alt="QR Code" />`;
            } else {
                qrContainer.innerHTML = '<p>QR Code haijatengenezwa</p>';
            }
            
            // Hide form and show success message
            document.getElementById('rsvpForm').style.display = 'none';
            successMsg.style.display = 'block';
            
            // Scroll to success message
            successMsg.scrollIntoView({ behavior: 'smooth' });
        }

        // Show error message
        function showErrorMessage(message, errors = null) {
            const errorMsg = document.getElementById('errorMessage');
            
            let errorText = message;
            if (errors) {
                errorText += '<br><br>Details:<br>';
                for (const [field, error] of Object.entries(errors)) {
                    errorText += `• ${field}: ${error}<br>`;
                }
            }
            
            errorMsg.innerHTML = errorText;
            errorMsg.style.display = 'block';
            
            // Scroll to error message
            errorMsg.scrollIntoView({ behavior: 'smooth' });
        }

        // Reset form
        function resetForm() {
            document.getElementById('rsvpForm').reset();
            document.getElementById('rsvpForm').style.display = 'block';
            document.getElementById('successMessage').style.display = 'none';
            document.getElementById('errorMessage').style.display = 'none';
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 9) {
                value = value.substring(0, 9);
            }
            e.target.value = value;
        });

        // Guest count validation
        document.getElementById('guest_count').addEventListener('input', function(e) {
            let value = parseInt(e.target.value) || 1;
            if (value < 1) {
                e.target.value = 1;
            } else if (value > 10) {
                e.target.value = 10;
            }
        });

        // Message character counter
        document.getElementById('message').addEventListener('input', function(e) {
            const maxLength = 500;
            const currentLength = e.target.value.length;
            const remaining = maxLength - currentLength;
            
            // Update label to show character count
            const label = e.target.previousElementSibling;
            if (remaining < 50) {
                label.innerHTML = `<i class="fas fa-comment"></i> Ujumbe kwa Wanandoa (Hiari) - <span style="color: ${remaining < 0 ? '#dc3545' : '#ffc107'};">${remaining} characters remaining</span>`;
            } else {
                label.innerHTML = `<i class="fas fa-comment"></i> Ujumbe kwa Wanandoa (Hiari)`;
            }
            
            // Limit text if exceeds max length
            if (currentLength > maxLength) {
                e.target.value = e.target.value.substring(0, maxLength);
            }
        });
    </script>
</body>
</html>
