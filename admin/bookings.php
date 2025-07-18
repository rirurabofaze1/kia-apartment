<?php
require_once "../includes/config.php";

if (!hasPermission(["admin", "superuser"])) {
    header("Location: ../index.php");
    exit;
}

// Get all bookings with room and user information
$sql = "SELECT b.*, r.room_number, r.location, r.room_type, u.full_name as created_by_name
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        JOIN users u ON b.created_by = u.id
        ORDER BY 
            CASE 
                WHEN b.status = \"booked\" AND b.arrival_time < NOW() THEN 1
                WHEN b.status = \"booked\" THEN 2
                WHEN b.status = \"checkin\" THEN 3
                WHEN b.status = \"checkout\" THEN 4
                ELSE 5
            END,
            b.arrival_time ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$bookings = $stmt->fetchAll();

// Get summary statistics
$stats = [];
$stmt = $pdo->query("SELECT 
    COUNT(CASE WHEN status = \"booked\" THEN 1 END) as booked,
    COUNT(CASE WHEN status = \"checkin\" THEN 1 END) as checkin,
    COUNT(CASE WHEN status = \"checkout\" THEN 1 END) as checkout,
    COUNT(CASE WHEN status = \"cancelled\" THEN 1 END) as cancelled,
    COUNT(CASE WHEN status = \"no_show\" THEN 1 END) as no_show,
    COUNT(CASE WHEN status = \"booked\" AND arrival_time < NOW() THEN 1 END) as overdue
    FROM bookings 
    WHERE DATE(created_at) = CURDATE()");
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Bookings - KIA SERVICED APARTMENT</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .booking-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--dark-gray);
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            border: 2px solid var(--primary-pink);
        }
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-pink);
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: var(--light-gray);
            font-size: 0.8rem;
        }
        .booking-row.overdue {
            background: rgba(244, 67, 54, 0.1);
            border-left: 4px solid var(--danger);
        }
        .booking-actions {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="../index.php" class="logo">KIA SERVICED APARTMENT</a>
            <div>
                <span style="color: var(--primary-pink); margin-right: 1rem;">
                    Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]); ?> (<?php echo ucfirst($_SESSION["user_role"]); ?>)
                </span>
                <a href="../index.php" class="btn btn-primary">Public View</a>
                <a href="../includes/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 style="color: var(--primary-pink); margin-bottom: 2rem;">All Bookings Management</h1>

        <div style="background: var(--dark-gray); padding: 1rem; border-radius: 10px; margin-bottom: 2rem;">
            <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
            <a href="rooms.php" class="btn btn-info">Manage Rooms</a>
            <a href="bookings.php" class="btn btn-info">All Bookings</a>
            <a href="reports.php" class="btn btn-warning">Financial Reports</a>
            <a href="history.php" class="btn btn-warning">History</a>
            <?php if (hasPermission(["superuser"])): ?>
                <a href="users.php" class="btn btn-danger">Manage Users</a>
            <?php endif; ?>
            <a href="shift_report.php" class="btn btn-success">End Shift</a>
        </div>

        <div class="booking-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats["booked"]; ?></div>
                <div class="stat-label">Booked Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats["checkin"]; ?></div>
                <div class="stat-label">Checked In</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats["checkout"]; ?></div>
                <div class="stat-label">Checked Out</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats["cancelled"]; ?></div>
                <div class="stat-label">Cancelled</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats["no_show"]; ?></div>
                <div class="stat-label">No Shows</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--danger);"><?php echo $stats["overdue"]; ?></div>
                <div class="stat-label">Overdue Arrivals</div>
            </div>
        </div>

        <div class="table-container">
            <h2 style="color: var(--primary-pink); margin: 1rem;">
                All Bookings (<?php echo count($bookings); ?> total)
            </h2>
            
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Room</th>
                            <!-- <th>Guest Info</th> -->
                            <th>Arrival Time</th>
                            <th>Duration</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <?php 
                            $is_overdue = ($booking["status"] == "booked" && strtotime($booking["arrival_time"]) < time());
                            $total_amount = $booking["price_amount"] + $booking["extra_time_amount"];
                            $total_hours = $booking["duration_hours"] + $booking["extra_time_hours"];
                            ?>
                            <tr class="booking-row <?php echo $is_overdue ? "overdue" : ""; ?>">
                                <td>
                                    <strong><?php echo $booking["id"]; ?></strong>
                                    <?php if ($is_overdue): ?>
                                        <br><small style="color: var(--danger);">OVERDUE</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($booking["location"]); ?></strong><br>
                                    Room <?php echo htmlspecialchars($booking["room_number"]); ?><br>
                                    <small><?php echo htmlspecialchars($booking["room_type"]); ?></small>
                                </td>
                                <!-- <td>
                                    <strong><?php echo htmlspecialchars($booking["guest_name"]); ?></strong><br>
                                    <?php echo htmlspecialchars($booking["phone_number"]); ?>
                                </td> -->
                                <td>
                                    <?php echo formatDateTime($booking["arrival_time"]); ?>
                                    <?php if ($booking["checkin_time"]): ?>
                                        <br><small>Checked in: <?php echo formatDateTime($booking["checkin_time"]); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $total_hours; ?> hours<br>
                                    <small>(<?php echo ucfirst($booking["duration_type"]); ?>)</small>
                                    <?php if ($booking["extra_time_hours"] > 0): ?>
                                        <br><small style="color: var(--warning);">+<?php echo $booking["extra_time_hours"]; ?>h extra</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo formatCurrency($total_amount); ?>
                                    <?php if ($booking["extra_time_amount"] > 0): ?>
                                        <br><small style="color: var(--warning);">+<?php echo formatCurrency($booking["extra_time_amount"]); ?> extra</small>
                                    <?php endif; ?>
                                    <?php if ($booking["refund_amount"] > 0): ?>
                                        <br><small style="color: var(--danger);">-<?php echo formatCurrency($booking["refund_amount"]); ?> refund</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo ucfirst($booking["payment_method"]); ?><br>
                                    <small>Deposit: <?php echo ucfirst($booking["deposit_type"]); ?></small>
                                    <?php if ($booking["deposit_amount"] > 0): ?>
                                        <br><small><?php echo formatCurrency($booking["deposit_amount"]); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-<?php echo $booking["status"]; ?>">
                                        <?php echo ucfirst(str_replace("_", " ", $booking["status"])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($booking["created_by_name"]); ?><br>
                                    <small><?php echo formatDateTime($booking["created_at"]); ?></small>
                                </td>
                                <td>
                                    <div class="booking-actions">
                                        <?php if ($booking["status"] == "booked"): ?>
                                            <?php if ($is_overdue): ?>
                                                <button onclick="confirmArrival(<?php echo $booking["id"]; ?>)" 
                                                        class="btn btn-success btn-sm">Confirm Arrival</button>
                                                <button onclick="markNoShow(<?php echo $booking["id"]; ?>)" 
                                                        class="btn btn-danger btn-sm">No Show</button>
                                            <?php else: ?>
                                                <button onclick="checkinRoom(<?php echo $booking["id"]; ?>)" 
                                                        class="btn btn-success btn-sm">Check In</button>
                                            <?php endif; ?>
                                            <button onclick="cancelBooking(<?php echo $booking["id"]; ?>)" 
                                                    class="btn btn-danger btn-sm">Cancel</button>
                                        <?php elseif ($booking["status"] == "checkin"): ?>
                                            <button onclick="printReceipt(<?php echo $booking["id"]; ?>)" 
                                                    class="btn btn-info btn-sm">Print Receipt</button>
                                            <button onclick="addExtraTime(<?php echo $booking["id"]; ?>)" 
                                                    class="btn btn-warning btn-sm">Add Extra Time</button>
                                            <button onclick="checkoutRoom(<?php echo $booking["id"]; ?>)" 
                                                    class="btn btn-danger btn-sm">Check Out</button>
                                            <button onclick="processRefund(<?php echo $booking["id"]; ?>)" 
                                                    class="btn btn-warning btn-sm">Refund</button>
                                        <?php elseif ($booking["status"] == "checkout"): ?>
                                            <button onclick="printReceipt(<?php echo $booking["id"]; ?>)" 
                                                    class="btn btn-info btn-sm">Print Receipt</button>
                                        <?php endif; ?>
                                        
                                        <?php if ($booking["notes"]): ?>
                                            <button onclick="showNotes(\"<?php echo htmlspecialchars($booking["notes"], ENT_QUOTES); ?>\")" 
                                                    class="btn btn-secondary btn-sm">Notes</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 KIA SERVICED APARTMENT - Copyright by Riruuu Rabofezu</p>
    </footer>

    <script src="../assets/js/main.js"></script>
    <script>
        function showNotes(notes) {
            alert("Notes: " + notes);
        }
        
        setInterval(function() {
            if (!document.querySelector(".modal") || document.querySelector(".modal").style.display === "none") {
                location.reload();
            }
        }, 60000);
    </script>
</body>
</html>
