<?php
require_once 'includes/config.php';

// Get rooms that will checkout in less than 1 hour
$sql = "SELECT r.*, 
               b.id as booking_id,
               b.guest_name,
               b.checkin_time,
               b.duration_hours,
               b.extra_time_hours,
               b.status as booking_status,
               (b.checkin_time + INTERVAL (b.duration_hours + b.extra_time_hours) HOUR) as checkout_time
        FROM rooms r 
        INNER JOIN bookings b ON r.id = b.room_id 
        WHERE b.status = 'checkin'
        AND (b.checkin_time + INTERVAL (b.duration_hours + b.extra_time_hours) HOUR) <= DATE_ADD(NOW(), INTERVAL 1 HOUR)
        AND (b.checkin_time + INTERVAL (b.duration_hours + b.extra_time_hours) HOUR) > NOW()
        ORDER BY (b.checkin_time + INTERVAL (b.duration_hours + b.extra_time_hours) HOUR) ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$urgent_rooms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Countdown Checkout - KIA SERVICED APARTMENT</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .countdown-page {
            min-height: 100vh;
            padding-top: 2rem;
        }
        
        .countdown-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .countdown-header h1 {
            color: var(--primary-pink);
            font-size: 2.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .countdown-header p {
            color: var(--light-gray);
            font-size: 1.2rem;
        }
        
        .urgent-room-card {
            background: linear-gradient(135deg, var(--dark-gray) 0%, var(--black) 100%);
            border: 3px solid var(--primary-pink);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(255, 105, 180, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .urgent-room-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 105, 180, 0.1), transparent);
            transition: left 0.5s;
        }
        
        .urgent-room-card:hover::before {
            left: 100%;
        }
        
        .urgent-room-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(255, 105, 180, 0.4);
        }
        
        .room-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            align-items: center;
        }
        
        .room-details h3 {
            color: var(--primary-pink);
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }
        
        .room-meta {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .room-meta span {
            color: var(--white);
            font-size: 1rem;
        }
        
        .room-meta strong {
            color: var(--primary-pink);
        }
        
        .countdown-display {
            text-align: center;
            background: var(--black);
            border-radius: 10px;
            padding: 2rem;
            border: 2px solid var(--primary-pink);
        }
        
        .countdown-label {
            color: var(--primary-pink);
            font-size: 1.2rem;
            margin-bottom: 1rem;
            font-weight: bold;
        }
        
        .countdown-timer-large {
            font-size: 3rem;
            font-weight: bold;
            color: var(--white);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            margin-bottom: 1rem;
        }
        
        .countdown-timer-large.urgent {
            color: var(--danger);
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .checkout-time {
            color: var(--light-gray);
            font-size: 1rem;
        }
        
        .no-rooms {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--dark-gray);
            border-radius: 15px;
            margin: 2rem 0;
        }
        
        .no-rooms h2 {
            color: var(--primary-pink);
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .no-rooms p {
            color: var(--light-gray);
            font-size: 1.2rem;
        }
        
        .back-button {
            position: fixed;
            top: 100px;
            left: 2rem;
            z-index: 1000;
        }
        
        @media (max-width: 768px) {
            .room-info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .countdown-timer-large {
                font-size: 2rem;
            }
            
            .countdown-header h1 {
                font-size: 2rem;
            }
            
            .back-button {
                position: static;
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">KIA SERVICED APARTMENT</a>
            <div>
                <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
            </div>
        </div>
    </header>

    <div class="countdown-page">
        <div class="container">
            <div class="countdown-header">
                <h1>🕐 Countdown Checkout</h1>
                <p>Kamar yang akan checkout dalam waktu kurang dari 1 jam</p>
            </div>

            <?php if (empty($urgent_rooms)): ?>
                <div class="no-rooms">
                    <h2>✅ Tidak Ada Kamar Urgent</h2>
                    <p>Saat ini tidak ada kamar yang akan checkout dalam 1 jam ke depan.</p>
                    <p>Semua kamar dalam kondisi aman! 🎉</p>
                </div>
            <?php else: ?>
                <?php foreach ($urgent_rooms as $room): ?>
                    <?php
                    $checkin_time = strtotime($room['checkin_time']);
                    $checkout_time = $checkin_time + ($room['duration_hours'] * 3600) + ($room['extra_time_hours'] * 3600);
                    $checkout_datetime = date('Y-m-d H:i:s', $checkout_time);
                    $checkout_display = date('d/m/Y H:i', $checkout_time);
                    
                    // Calculate time remaining in minutes
                    $time_remaining = ($checkout_time - time()) / 60;
                    ?>
                    <div class="urgent-room-card">
                        <div class="room-info-grid">
                            <div class="room-details">
                                <h3>🏠 Kamar <?php echo htmlspecialchars($room['room_number']); ?></h3>
                                <div class="room-meta">
                                    <span><strong>Lokasi:</strong> <?php echo htmlspecialchars($room['location']); ?></span>
                                    <span><strong>Lantai:</strong> <?php echo $room['floor_number']; ?></span>
                                    <span><strong>Tipe:</strong> <?php echo htmlspecialchars($room['room_type']); ?></span>
                                    <span><strong>Tamu:</strong> <?php echo htmlspecialchars($room['guest_name']); ?></span>
                                    <span><strong>Check-in:</strong> <?php echo formatDateTime($room['checkin_time']); ?></span>
                                    <span><strong>Durasi:</strong> <?php echo $room['duration_hours'] + $room['extra_time_hours']; ?> jam</span>
                                </div>
                            </div>
                            
                            <div class="countdown-display">
                                <div class="countdown-label">⏰ Sisa Waktu</div>
                                <div class="countdown-timer-large <?php echo $time_remaining <= 15 ? 'urgent' : ''; ?>" 
                                     data-target="<?php echo $checkout_datetime; ?>">
                                    Loading...
                                </div>
                                <div class="checkout-time">
                                    Checkout: <?php echo $checkout_display; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 KIA SERVICED APARTMENT - Copyright by Riruuu Rabofezu</p>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        // Enhanced countdown for urgent rooms
        document.addEventListener('DOMContentLoaded', function() {
            startCountdownTimers();
            
            // Auto-refresh every 30 seconds
            setInterval(function() {
                location.reload();
            }, 30000);
        });
        
        function updateCountdown(element, targetTime) {
            const now = new Date().getTime();
            const target = new Date(targetTime).getTime();
            const difference = target - now;
            
            if (difference > 0) {
                const hours = Math.floor(difference / (1000 * 60 * 60));
                const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((difference % (1000 * 60)) / 1000);
                
                element.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                // Add urgent class if less than 15 minutes
                if (difference <= 15 * 60 * 1000) {
                    element.classList.add('urgent');
                } else {
                    element.classList.remove('urgent');
                }
            } else {
                element.textContent = 'EXPIRED';
                element.classList.add('urgent');
            }
        }
    </script>
</body>
</html>
