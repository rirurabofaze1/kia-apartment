<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

// Get statistics
$stats = [];

// Total rooms
$stmt = $pdo->query("SELECT COUNT(*) as total FROM rooms");
$stats['total_rooms'] = $stmt->fetch()['total'];

// Rooms by status
$stmt = $pdo->query("SELECT 
    SUM(CASE WHEN (SELECT COUNT(*) FROM bookings WHERE room_id = r.id AND status IN ('booked', 'checkin', 'checkout')) = 0 THEN 1 ELSE 0 END) as ready,
    SUM(CASE WHEN (SELECT COUNT(*) FROM bookings WHERE room_id = r.id AND status = 'booked') > 0 THEN 1 ELSE 0 END) as booked,
    SUM(CASE WHEN (SELECT COUNT(*) FROM bookings WHERE room_id = r.id AND status = 'checkin') > 0 THEN 1 ELSE 0 END) as checkin,
    SUM(CASE WHEN (SELECT COUNT(*) FROM bookings WHERE room_id = r.id AND status = 'checkout') > 0 THEN 1 ELSE 0 END) as checkout
    FROM rooms r");
$room_stats = $stmt->fetch();

// Today's revenue
$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM transactions WHERE DATE(transaction_date) = CURDATE() AND amount > 0");
$stmt->execute();
$stats['today_revenue'] = $stmt->fetch()['total'] ?? 0;

// This month's revenue
$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM transactions WHERE MONTH(transaction_date) = MONTH(CURDATE()) AND YEAR(transaction_date) = YEAR(CURDATE()) AND amount > 0");
$stmt->execute();
$stats['month_revenue'] = $stmt->fetch()['total'] ?? 0;

// Recent bookings
$stmt = $pdo->prepare("SELECT b.*, r.room_number, r.location FROM bookings b 
                      JOIN rooms r ON b.room_id = r.id 
                      ORDER BY b.created_at DESC LIMIT 10");
$stmt->execute();
$recent_bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KIA SERVICED APARTMENT</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--dark-gray);
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            border: 2px solid var(--primary-pink);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-pink);
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: var(--light-gray);
            font-size: 0.9rem;
        }
        .nav-menu {
            background: var(--dark-gray);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .nav-menu a {
            display: inline-block;
            margin: 0.25rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="../index.php" class="logo">KIA SERVICED APARTMENT</a>
            <div>
                <span style="color: var(--primary-pink); margin-right: 1rem;">
                    Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?> (<?php echo ucfirst($_SESSION['user_role']); ?>)
                </span>
                <a href="../index.php" class="btn btn-primary">Public View</a>
                <a href="../includes/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 style="color: var(--primary-pink); margin-bottom: 2rem;">Admin Dashboard</h1>

        <!-- Navigation Menu -->
        <div class="nav-menu">
            <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
            <?php if (hasPermission(['admin', 'superuser'])): ?>
                <a href="rooms.php" class="btn btn-info">Manage Rooms</a>
                <a href="bookings.php" class="btn btn-info">All Bookings</a>
                <a href="reports.php" class="btn btn-warning">Financial Reports</a>
                <a href="history.php" class="btn btn-warning">History</a>
            <?php endif; ?>
            <?php if (hasPermission(['superuser'])): ?>
                <a href="users.php" class="btn btn-danger">Manage Users</a>
            <?php endif; ?>
            <?php if (hasPermission(['cashier', 'admin', 'superuser'])): ?>
                <a href="shift_report.php" class="btn btn-success">End Shift</a>
            <?php endif; ?>
        </div>

        <!-- Statistics Dashboard -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_rooms']; ?></div>
                <div class="stat-label">Total Rooms</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $room_stats['ready']; ?></div>
                <div class="stat-label">Ready Rooms</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $room_stats['booked']; ?></div>
                <div class="stat-label">Booked Rooms</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $room_stats['checkin']; ?></div>
                <div class="stat-label">Checked In</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $room_stats['checkout']; ?></div>
                <div class="stat-label">Checkout</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo formatCurrency($stats['today_revenue']); ?></div>
                <div class="stat-label">Today's Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo formatCurrency($stats['month_revenue']); ?></div>
                <div class="stat-label">This Month</div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="table-container">
            <h2 style="color: var(--primary-pink); margin: 1rem;">Recent Bookings</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Room</th>
                        <!-- <th>Guest</th>
                        <th>Phone</th> -->
                        <th>Arrival</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_bookings as $booking): ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['location'] . ' - ' . $booking['room_number']); ?></td>
                        <!-- <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['phone_number']); ?></td> -->
                        <td><?php echo formatDateTime($booking['arrival_time']); ?></td>
                        <td>
                            <span class="status-<?php echo $booking['status']; ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </td>
                        <td><?php echo formatCurrency($booking['price_amount']); ?></td>
                        <td><?php echo formatDateTime($booking['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 KIA SERVICED APARTMENT - Copyright by Riruuu Rabofezu</p>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>
