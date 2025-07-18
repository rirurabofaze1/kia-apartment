<?php
require_once 'includes/config.php';

// Get rooms with their current bookings
// For public view: only show active bookings (booked, checkin, checkout)
// For logged-in users: show all recent bookings including checkout for management
$isLoggedIn = isLoggedIn();

if ($isLoggedIn) {
    // Admin view: show all recent bookings including checkout
    $sql = "SELECT r.*, 
                   b.id as booking_id,
                   b.guest_name,
                   b.arrival_time,
                   b.phone_number,
                   b.duration_type,
                   b.duration_hours,
                   b.price_amount,
                   b.payment_method,
                   b.deposit_type,
                   b.deposit_amount,
                   b.notes,
                   b.status as booking_status,
                   b.checkin_time,
                   b.checkout_time,
                   b.extra_time_hours,
                   b.extra_time_amount
            FROM rooms r 
            LEFT JOIN (
                SELECT b1.*
                FROM bookings b1
                INNER JOIN (
                    SELECT room_id, MAX(id) as max_id
                    FROM bookings 
                    WHERE status IN ('booked', 'checkin', 'checkout')
                    GROUP BY room_id
                ) b2 ON b1.room_id = b2.room_id AND b1.id = b2.max_id
            ) b ON r.id = b.room_id
            ORDER BY 
                CASE 
                    WHEN b.status = 'booked' AND b.arrival_time < NOW() THEN 1
                    ELSE 2
                END,
                r.location, r.floor_number, r.room_number";
} else {
    // Public view: show all rooms, but only ready and checkout will be shown in the list
    $sql = "SELECT r.*, 
               b.id as booking_id,
               b.guest_name,
               b.arrival_time,
               b.phone_number,
               b.duration_type,
               b.duration_hours,
               b.price_amount,
               b.payment_method,
               b.deposit_type,
               b.deposit_amount,
               b.notes,
               b.status as booking_status,
               b.checkin_time,
               b.checkout_time,
               b.extra_time_hours,
               b.extra_time_amount
        FROM rooms r 
        LEFT JOIN (
            SELECT b1.*
            FROM bookings b1
            INNER JOIN (
                SELECT room_id, MAX(id) as max_id
                FROM bookings 
                WHERE status IN ('booked', 'checkin', 'checkout')
                GROUP BY room_id
            ) b2 ON b1.room_id = b2.room_id AND b1.id = b2.max_id
        ) b ON r.id = b.room_id";
}

$stmt = $pdo->prepare($sql);
$stmt->execute();
$rooms = $stmt->fetchAll();

// Update room statuses based on bookings and room status
foreach ($rooms as $key => $room) {
    if ($room['status'] == 'ready') {
        $rooms[$key]['status'] = 'ready';
        // Clear booking info for privacy
        $rooms[$key]['booking_id'] = null;
        $rooms[$key]['guest_name'] = null;
        $rooms[$key]['arrival_time'] = null;
        $rooms[$key]['phone_number'] = null;
        $rooms[$key]['duration_type'] = null;
        $rooms[$key]['duration_hours'] = null;
        $rooms[$key]['price_amount'] = null;
        $rooms[$key]['payment_method'] = null;
        $rooms[$key]['deposit_type'] = null;
        $rooms[$key]['deposit_amount'] = null;
        $rooms[$key]['notes'] = null;
        $rooms[$key]['booking_status'] = null;
        $rooms[$key]['checkin_time'] = null;
        $rooms[$key]['checkout_time'] = null;
        $rooms[$key]['extra_time_hours'] = null;
        $rooms[$key]['extra_time_amount'] = null;
    } elseif ($room['booking_id']) {
        $rooms[$key]['status'] = $room['booking_status'];
    } else {
        $rooms[$key]['status'] = 'ready';
    }
}

// Get unique room types for filter
$roomTypes = array_unique(array_column($rooms, 'room_type'));

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KIA SERVICED APARTMENT</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">KIA SERVICED APARTMENT</a>
            <div class="header-actions">
                <a href="countdown.php" class="btn-countdown-header">
                    <i class="countdown-icon-small">⏰</i>
                    <span>Countdown</span>
                </a>
                <?php if ($isLoggedIn): ?>
                    <span style="color: var(--primary-pink); margin-right: 1rem;">
                        Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?> (<?php echo ucfirst($_SESSION['user_role']); ?>)
                    </span>
                    <a href="admin/dashboard.php" class="btn btn-primary">Dashboard</a>
                    <a href="includes/logout.php" class="btn btn-danger">Logout</a>
                <?php else: ?>
                    <button id="loginBtn" class="nav-toggle">Login</button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <!-- Filters -->
        <div class="filters">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="roomTypeFilter">Room Type:</label>
                    <select id="roomTypeFilter" class="form-control filter-control">
                        <option value="">All Types</option>
                        <?php foreach ($roomTypes as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>">
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="statusFilter">Status:</label>
                    <select id="statusFilter" class="form-control filter-control">
                        <option value="">All Status</option>
                        <option value="ready">Ready</option>
                        <option value="checkout">Checkout</option>
                        <?php if ($isLoggedIn): ?>
                            <option value="booked">Booked</option>
                            <option value="checkin">Check-in</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="searchFilter">Search:</label>
                    <input type="text" id="searchFilter" class="form-control filter-control" 
                           placeholder="Room number or location...">
                </div>
            </div>
        </div>

        <!-- Rooms Grid -->
        <div class="rooms-grid" id="roomsGrid">
            <?php foreach ($rooms as $room): ?>
                <?php 
                // Only show ready and checkout rooms to public
                if (!$isLoggedIn && !in_array($room['status'], ['ready', 'checkout'])) {
                    continue;
                }
                ?>
                <div class="room-card <?php echo $room['status']; ?>" 
                     data-room-type="<?php echo htmlspecialchars($room['room_type']); ?>"
                     data-status="<?php echo $room['status']; ?>"
                     data-room-number="<?php echo htmlspecialchars($room['room_number']); ?>"
                     data-location="<?php echo htmlspecialchars($room['location']); ?>">
                    
                    <div class="room-status status-<?php echo $room['status']; ?>">
                        <?php echo ucfirst($room['status']); ?>
                    </div>
                    
                    <div class="room-info">
                        <h3>Room <?php echo htmlspecialchars($room['room_number']); ?></h3>
                        <div class="room-details">
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($room['location']); ?></p>
                            <p><strong>Floor:</strong> <?php echo $room['floor_number']; ?></p>
                            <p><strong>Type:</strong> <?php echo htmlspecialchars($room['room_type']); ?></p>
                            
                            <?php if ($isLoggedIn): ?>
                                <p><strong>WiFi:</strong> <?php echo htmlspecialchars($room['wifi_name']); ?></p>
                                <p><strong>Password:</strong> <?php echo htmlspecialchars($room['wifi_password']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($room['booking_id'] && $isLoggedIn): ?>
                        <div class="guest-info">
                            <h4>Guest Information</h4>
                            <!-- <p><strong>Name:</strong> <?php echo htmlspecialchars($room['guest_name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($room['phone_number']); ?></p> -->
                            <p><strong>Arrival:</strong> <?php echo formatDateTime($room['arrival_time']); ?></p>
                            <p><strong>Duration:</strong> <?php echo $room['duration_hours']; ?> hours (<?php echo ucfirst($room['duration_type']); ?>)</p>
                            <p><strong>Amount:</strong> <?php echo formatCurrency($room['price_amount']); ?></p>
                            <p><strong>Payment:</strong> <?php echo ucfirst($room['payment_method']); ?></p>
                            <p><strong>Deposit:</strong> <?php echo ucfirst($room['deposit_type']); ?> 
                               <?php if ($room['deposit_amount'] > 0): ?>
                                   (<?php echo formatCurrency($room['deposit_amount']); ?>)
                               <?php endif; ?>
                            </p>
                            <?php if ($room['notes']): ?>
                                <p><strong>Notes:</strong> <?php echo htmlspecialchars($room['notes']); ?></p>
                            <?php endif; ?>
                        </div>

                        <?php if ($room['status'] == 'booked'): ?>
                            <?php
                            $arrival_time = strtotime($room['arrival_time']);
                            $current_time = time();
                            $is_expired = $current_time > $arrival_time;
                            ?>
                            <div class="countdown <?php echo $is_expired ? 'expired' : ''; ?>">
                                <p><strong>Arrival Countdown:</strong></p>
                                <div class="countdown-timer" data-target="<?php echo $room['arrival_time']; ?>">
                                    <?php echo $is_expired ? 'EXPIRED' : ''; ?>
                                </div>
                            </div>
                            
                            <?php if ($is_expired): ?>
                                <div class="confirmation-buttons" style="display: block;">
                                    <button onclick="confirmArrival(<?php echo $room['booking_id']; ?>)" 
                                            class="btn btn-success btn-sm">Confirm Arrival</button>
                                    <button onclick="markNoShow(<?php echo $room['booking_id']; ?>)" 
                                            class="btn btn-danger btn-sm">No Show</button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($room['status'] == 'checkin'): ?>
                            <?php
                            $checkin_time = strtotime($room['checkin_time']);
                            $checkout_time = $checkin_time + ($room['duration_hours'] * 3600) + ($room['extra_time_hours'] * 3600);
                            $checkout_datetime = date('Y-m-d H:i:s', $checkout_time);
                            ?>
                            <div class="countdown">
                                <p><strong>Checkout Time:</strong></p>
                                <div class="countdown-timer" 
                                     data-target="<?php echo $checkout_datetime; ?>"
                                     data-booking-id="<?php echo $room['booking_id']; ?>"></div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($isLoggedIn): ?>
                        <div class="room-actions">
                            <?php if ($room['status'] == 'ready'): ?>
                                <button onclick="bookRoom(<?php echo $room['id']; ?>)" 
                                        class="btn btn-primary btn-sm">Book Room</button>
                            <?php elseif ($room['status'] == 'booked'): ?>
                                <button onclick="checkinRoom(<?php echo $room['booking_id']; ?>)" 
                                        class="btn btn-success btn-sm">Check In</button>
                                <button onclick="cancelBooking(<?php echo $room['booking_id']; ?>)" 
                                        class="btn btn-danger btn-sm">Cancel</button>
                            <?php elseif ($room['status'] == 'checkin'): ?>
                                <button onclick="printReceipt(<?php echo $room['booking_id']; ?>)" 
                                        class="btn btn-info btn-sm">Print Receipt</button>
                                <button onclick="addExtraTime(<?php echo $room['booking_id']; ?>)" 
                                        class="btn btn-warning btn-sm">Add Extra Time</button>
                                <button onclick="checkoutRoom(<?php echo $room['booking_id']; ?>)" 
                                        class="btn btn-danger btn-sm">Check Out</button>
                                <?php if (hasPermission(['admin', 'superuser'])): ?>
                                    <button onclick="processRefund(<?php echo $room['booking_id']; ?>)" 
                                            class="btn btn-warning btn-sm">Refund</button>
                                <?php endif; ?>
                            <?php elseif ($room['status'] == 'checkout'): ?>
                                <button onclick="setRoomReady(<?php echo $room['id']; ?>)" 
                                        class="btn btn-success btn-sm">Set Ready</button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Login</h2>
            <form action="includes/login.php" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 KIA SERVICED APARTMENT - Copyright by Riruuu Rabofezu</p>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        // Inisialisasi semua fitur JS di main.js setelah DOM siap
        document.addEventListener("DOMContentLoaded", function() {
            initializeApp();
            startCountdownTimers();
        });
    </script>
</body>
</html>