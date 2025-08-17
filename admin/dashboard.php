<?php
/**
 * Live Admin Dashboard
 * Real-time monitoring of guest registrations and payments
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modules/payment/payment_processor.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login/index.html');
    exit();
}

$pdo = getDB();
$paymentProcessor = new PaymentProcessor();

// Get statistics
$guestStats = $pdo->query("
    SELECT 
        COUNT(*) as total_guests,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_guests,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_guests,
        SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) as declined_guests,
        SUM(guest_count) as total_guest_count
    FROM guests
")->fetch();

$paymentStats = $paymentProcessor->getPaymentStats();

// Get recent activities
$recentGuests = $pdo->query("
    SELECT * FROM guests ORDER BY created_at DESC LIMIT 10
")->fetchAll();

$recentPayments = $pdo->query("
    SELECT p.*, g.full_name, g.email 
    FROM payments p 
    LEFT JOIN guests g ON p.guest_id = g.id 
    ORDER BY p.created_at DESC LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Harusi ya HAMISI na SUBIRA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* BACKGROUND */
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow-x: hidden;
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

        /* HEADER */
        .header {
            background: rgba(255,255,255,0.95);
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header h1 {
            margin: 0;
            color: #333;
            text-align: center;
            font-size: 2.5rem;
        }
        .header .subtitle {
            text-align: center;
            color: #666;
            margin-top: 5px;
        }

        /* MAIN CONTAINER */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        /* STATS CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 2px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
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
        .stat-card:hover::before {
            opacity: 0.1;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            border-color: #ff6b9d;
        }
        @keyframes glowing {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .stat-card .icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ff6b9d;
        }
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        .stat-card .label {
            color: #666;
            font-size: 1.1rem;
            font-weight: 600;
        }

        /* CONTENT SECTIONS */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .content-card {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        .content-card:hover {
            border-color: #ff6b9d;
            transform: translateY(-3px);
        }
        .content-card h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .content-card h3 i {
            color: #ff6b9d;
        }

        /* TABLES */
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status.confirmed { background: #d4edda; color: #155724; }
        .status.pending { background: #fff3cd; color: #856404; }
        .status.declined { background: #f8d7da; color: #721c24; }
        .status.completed { background: #d1ecf1; color: #0c5460; }

        /* REFRESH BUTTON */
        .refresh-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #ff6b9d;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(255,107,157,0.4);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        .refresh-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(255,107,157,0.6);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            .header h1 {
                font-size: 2rem;
            }
        }

        /* LOADING ANIMATION */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #ff6b9d;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* NOTIFICATION */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            transform: translateX(400px);
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        .notification.show {
            transform: translateX(0);
        }
        .notification.error {
            background: #dc3545;
        }
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

    <!-- HEADER -->
    <div class="header">
        <h1>🎉 Admin Dashboard</h1>
        <div class="subtitle">Harusi ya HAMISI na SUBIRA - Live Monitoring</div>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="container">
        <!-- STATISTICS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">👥</div>
                <div class="number" id="totalGuests"><?php echo $guestStats['total_guests'] ?? 0; ?></div>
                <div class="label">Total Wageni</div>
            </div>
            <div class="stat-card">
                <div class="icon">✅</div>
                <div class="number" id="confirmedGuests"><?php echo $guestStats['confirmed_guests'] ?? 0; ?></div>
                <div class="label">Wageni Walio Thibitisha</div>
            </div>
            <div class="stat-card">
                <div class="icon">💰</div>
                <div class="number" id="totalPayments"><?php echo $paymentStats['total_payments'] ?? 0; ?></div>
                <div class="label">Malipo Yote</div>
            </div>
            <div class="stat-card">
                <div class="icon">💳</div>
                <div class="number" id="totalAmount"><?php echo number_format($paymentStats['total_amount'] ?? 0); ?></div>
                <div class="label">Jumla ya Malipo (TZS)</div>
            </div>
        </div>

        <!-- CONTENT GRID -->
        <div class="content-grid">
            <!-- RECENT GUESTS -->
            <div class="content-card">
                <h3><i class="fas fa-users"></i> Wageni wa Hivi Karibuni</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Jina</th>
                                <th>Email</th>
                                <th>Simu</th>
                                <th>Idadi</th>
                                <th>Hali</th>
                                <th>Tarehe</th>
                            </tr>
                        </thead>
                        <tbody id="guestsTable">
                            <?php foreach ($recentGuests as $guest): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($guest['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($guest['email']); ?></td>
                                <td><?php echo htmlspecialchars($guest['phone'] ?? '-'); ?></td>
                                <td><?php echo $guest['guest_count']; ?></td>
                                <td><span class="status <?php echo $guest['status']; ?>"><?php echo ucfirst($guest['status']); ?></span></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($guest['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RECENT PAYMENTS -->
            <div class="content-card">
                <h3><i class="fas fa-money-bill-wave"></i> Malipo ya Hivi Karibuni</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Mgeni</th>
                                <th>Kiasi</th>
                                <th>Njia</th>
                                <th>Hali</th>
                                <th>Tarehe</th>
                            </tr>
                        </thead>
                        <tbody id="paymentsTable">
                            <?php foreach ($recentPayments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['full_name'] ?? 'N/A'); ?></td>
                                <td><?php echo number_format($payment['amount']); ?> TZS</td>
                                <td><?php echo ucfirst($payment['payment_method']); ?></td>
                                <td><span class="status <?php echo $payment['status']; ?>"><?php echo ucfirst($payment['status']); ?></span></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($payment['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- REFRESH BUTTON -->
    <button class="refresh-btn" onclick="refreshData()" title="Refresh Data">
        <i class="fas fa-sync-alt"></i>
    </button>

    <!-- NOTIFICATION -->
    <div id="notification" class="notification"></div>

    <script>
        // Auto-refresh data every 30 seconds
        setInterval(refreshData, 30000);

        // Refresh data function
        function refreshData() {
            const refreshBtn = document.querySelector('.refresh-btn i');
            refreshBtn.classList.add('fa-spin');
            
            fetch('get_dashboard_data.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateDashboard(data.data);
                        showNotification('Data updated successfully!', 'success');
                    } else {
                        showNotification('Failed to update data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error updating data', 'error');
                })
                .finally(() => {
                    refreshBtn.classList.remove('fa-spin');
                });
        }

        // Update dashboard with new data
        function updateDashboard(data) {
            // Update statistics
            document.getElementById('totalGuests').textContent = data.guestStats.total_guests;
            document.getElementById('confirmedGuests').textContent = data.guestStats.confirmed_guests;
            document.getElementById('totalPayments').textContent = data.paymentStats.total_payments;
            document.getElementById('totalAmount').textContent = new Intl.NumberFormat().format(data.paymentStats.total_amount);

            // Update tables
            updateGuestsTable(data.recentGuests);
            updatePaymentsTable(data.recentPayments);
        }

        // Update guests table
        function updateGuestsTable(guests) {
            const tbody = document.getElementById('guestsTable');
            tbody.innerHTML = guests.map(guest => `
                <tr>
                    <td>${guest.full_name}</td>
                    <td>${guest.email}</td>
                    <td>${guest.phone || '-'}</td>
                    <td>${guest.guest_count}</td>
                    <td><span class="status ${guest.status}">${guest.status.charAt(0).toUpperCase() + guest.status.slice(1)}</span></td>
                    <td>${new Date(guest.created_at).toLocaleDateString('sw-TZ')}</td>
                </tr>
            `).join('');
        }

        // Update payments table
        function updatePaymentsTable(payments) {
            const tbody = document.getElementById('paymentsTable');
            tbody.innerHTML = payments.map(payment => `
                <tr>
                    <td>${payment.full_name || 'N/A'}</td>
                    <td>${new Intl.NumberFormat().format(payment.amount)} TZS</td>
                    <td>${payment.payment_method.charAt(0).toUpperCase() + payment.payment_method.slice(1)}</td>
                    <td><span class="status ${payment.status}">${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}</span></td>
                    <td>${new Date(payment.created_at).toLocaleDateString('sw-TZ')}</td>
                </tr>
            `).join('');
        }

        // Show notification
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('Dashboard loaded successfully!', 'success');
        });
    </script>
</body>
</html>
