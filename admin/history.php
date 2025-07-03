<?php
require_once "../includes/config.php";

if (!hasPermission(["admin", "superuser", "cashier"])) {
    header("Location: ../index.php");
    exit;
}

$view = $_GET["view"] ?? "bookings";
$period = $_GET["period"] ?? "this_month";
$room_id = $_GET["room_id"] ?? "";

// Build date filter
$date_filter = "";
$params = [];

switch ($period) {
    case "today":
        $date_filter = "DATE(created_at) = CURDATE()";
        break;
    case "yesterday":
        $date_filter = "DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
        break;
    case "this_week":
        $date_filter = "YEARWEEK(created_at) = YEARWEEK(CURDATE())";
        break;
    case "last_week":
        $date_filter = "YEARWEEK(created_at) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 WEEK))";
        break;
    case "this_month":
        $date_filter = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
        break;
    case "last_month":
        $date_filter = "MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
        break;
    case "this_year":
        $date_filter = "YEAR(created_at) = YEAR(CURDATE())";
        break;
    case "all":
        $date_filter = "1=1";
        break;
}

// Add room filter if specified
if ($room_id) {
    $date_filter .= " AND room_id = ?";
    $params[] = $room_id;
}

// Get data based on view
if ($view == "bookings") {
    $sql = "SELECT b.*, r.room_number, r.location, r.room_type, u.full_name as created_by_name
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            JOIN users u ON b.created_by = u.id
            WHERE $date_filter
            ORDER BY b.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    
} elseif ($view == "transactions") {
    $sql = "SELECT t.*, b.guest_name, r.room_number, r.location, u.full_name as created_by_name
            FROM transactions t
            JOIN bookings b ON t.booking_id = b.id
            JOIN rooms r ON b.room_id = r.id
            JOIN users u ON t.created_by = u.id
            WHERE " . str_replace("created_at", "t.transaction_date", $date_filter) . "
            ORDER BY t.transaction_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    
} elseif ($view == "cancellations") {
    $sql = "SELECT b.*, r.room_number, r.location, r.room_type, u.full_name as created_by_name
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            JOIN users u ON b.created_by = u.id
            WHERE b.status IN (\"cancelled\", \"no_show\") AND $date_filter
            ORDER BY b.updated_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
}

// Get rooms for filter
$stmt = $pdo->query("SELECT id, location, room_number FROM rooms ORDER BY location, room_number");
$rooms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - KIA SERVICED APARTMENT</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .history-filters {
            background: var(--dark-gray);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .view-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        .view-tab {
            padding: 0.75rem 1.5rem;
            background: var(--dark-gray);
            color: var(--light-gray);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .view-tab.active {
            background: var(--primary-pink);
            color: var(--white);
        }
        .view-tab:hover {
            background: var(--dark-pink);
            color: var(--white);
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
        <h1 style="color: var(--primary-pink); margin-bottom: 2rem;">History & Reports</h1>

        <div style="background: var(--dark-gray); padding: 1rem; border-radius: 10px; margin-bottom: 2rem;">
            <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
            <?php if (hasPermission(["admin", "superuser"])): ?>
                <a href="rooms.php" class="btn btn-info">Manage Rooms</a>
                <a href="bookings.php" class="btn btn-info">All Bookings</a>
            <?php endif; ?>
            <a href="reports.php" class="btn btn-warning">Financial Reports</a>
            <a href="history.php" class="btn btn-warning">History</a>
            <?php if (hasPermission(["superuser"])): ?>
                <a href="users.php" class="btn btn-danger">Manage Users</a>
            <?php endif; ?>
            <a href="shift_report.php" class="btn btn-success">End Shift</a>
        </div>

        <div class="view-tabs">
            <a href="?view=bookings&period=<?php echo $period; ?>&room_id=<?php echo $room_id; ?>" 
               class="view-tab <?php echo $view == "bookings" ? "active" : ""; ?>">
                Booking History
            </a>
            <a href="?view=transactions&period=<?php echo $period; ?>&room_id=<?php echo $room_id; ?>" 
               class="view-tab <?php echo $view == "transactions" ? "active" : ""; ?>">
                Transaction History
            </a>
            <a href="?view=cancellations&period=<?php echo $period; ?>&room_id=<?php echo $room_id; ?>" 
               class="view-tab <?php echo $view == "cancellations" ? "active" : ""; ?>">
                Cancellations & No-Shows
            </a>
        </div>

        <div class="history-filters">
            <form method="GET">
                <input type="hidden" name="view" value="<?php echo $view; ?>">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                    <div class="form-group">
                        <label>Period:</label>
                        <select name="period" class="form-control">
                            <option value="today" <?php echo $period == "today" ? "selected" : ""; ?>>Today</option>
                            <option value="yesterday" <?php echo $period == "yesterday" ? "selected" : ""; ?>>Yesterday</option>
                            <option value="this_week" <?php echo $period == "this_week" ? "selected" : ""; ?>>This Week</option>
                            <option value="last_week" <?php echo $period == "last_week" ? "selected" : ""; ?>>Last Week</option>
                            <option value="this_month" <?php echo $period == "this_month" ? "selected" : ""; ?>>This Month</option>
                            <option value="last_month" <?php echo $period == "last_month" ? "selected" : ""; ?>>Last Month</option>
                            <option value="this_year" <?php echo $period == "this_year" ? "selected" : ""; ?>>This Year</option>
                            <option value="all" <?php echo $period == "all" ? "selected" : ""; ?>>All Time</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Room:</label>
                        <select name="room_id" class="form-control">
                            <option value="">All Rooms</option>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo $room["id"]; ?>" <?php echo $room_id == $room["id"] ? "selected" : ""; ?>>
                                    <?php echo htmlspecialchars($room["location"] . " - " . $room["room_number"]); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-container">
            <h2 style="color: var(--primary-pink); margin: 1rem;">
                <?php 
                switch($view) {
                    case "bookings": echo "Booking History"; break;
                    case "transactions": echo "Transaction History"; break;
                    case "cancellations": echo "Cancellations & No-Shows"; break;
                }
                ?>
                (<?php echo count($data); ?> records)
            </h2>
            
            <table class="table">
                <thead>
                    <tr>
                        <?php if ($view == "bookings"): ?>
                            <th>ID</th>
                            <th>Room</th>
                            <th>Guest</th>
                            <th>Phone</th>
                            <th>Arrival</th>
                            <th>Duration</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created</th>
                        <?php elseif ($view == "transactions"): ?>
                            <th>ID</th>
                            <th>Date/Time</th>
                            <th>Type</th>
                            <th>Guest</th>
                            <th>Room</th>
                            <th>Payment Method</th>
                            <th>Amount</th>
                            <th>Cashier</th>
                        <?php elseif ($view == "cancellations"): ?>
                            <th>ID</th>
                            <th>Room</th>
                            <th>Guest</th>
                            <th>Phone</th>
                            <th>Arrival</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Date</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="10" style="text-align: center; color: var(--light-gray);">
                            No records found for the selected criteria.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($data as $record): ?>
                        <tr>
                            <?php if ($view == "bookings"): ?>
                                <td><?php echo $record["id"]; ?></td>
                                <td><?php echo htmlspecialchars($record["location"] . " - " . $record["room_number"]); ?></td>
                                <td><?php echo htmlspecialchars($record["guest_name"]); ?></td>
                                <td><?php echo htmlspecialchars($record["phone_number"]); ?></td>
                                <td><?php echo formatDateTime($record["arrival_time"]); ?></td>
                                <td><?php echo $record["duration_hours"]; ?> hours</td>
                                <td><?php echo formatCurrency($record["price_amount"] + $record["extra_time_amount"]); ?></td>
                                <td>
                                    <span class="status-<?php echo $record["status"]; ?>">
                                        <?php echo ucfirst($record["status"]); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($record["created_by_name"]); ?></td>
                                <td><?php echo formatDateTime($record["created_at"]); ?></td>
                            <?php elseif ($view == "transactions"): ?>
                                <td><?php echo $record["id"]; ?></td>
                                <td><?php echo formatDateTime($record["transaction_date"]); ?></td>
                                <td><?php echo ucfirst(str_replace("_", " ", $record["transaction_type"])); ?></td>
                                <td><?php echo htmlspecialchars($record["guest_name"]); ?></td>
                                <td><?php echo htmlspecialchars($record["location"] . " - " . $record["room_number"]); ?></td>
                                <td><?php echo ucfirst($record["payment_method"]); ?></td>
                                <td style="color: <?php echo $record["amount"] >= 0 ? "var(--success)" : "var(--danger)"; ?>">
                                    <?php echo formatCurrency($record["amount"]); ?>
                                </td>
                                <td><?php echo htmlspecialchars($record["created_by_name"]); ?></td>
                            <?php elseif ($view == "cancellations"): ?>
                                <td><?php echo $record["id"]; ?></td>
                                <td><?php echo htmlspecialchars($record["location"] . " - " . $record["room_number"]); ?></td>
                                <td><?php echo htmlspecialchars($record["guest_name"]); ?></td>
                                <td><?php echo htmlspecialchars($record["phone_number"]); ?></td>
                                <td><?php echo formatDateTime($record["arrival_time"]); ?></td>
                                <td><?php echo formatCurrency($record["price_amount"]); ?></td>
                                <td>
                                    <span class="status-<?php echo $record["status"]; ?>">
                                        <?php echo ucfirst(str_replace("_", " ", $record["status"])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($record["notes"] ?: "-"); ?></td>
                                <td><?php echo formatDateTime($record["updated_at"]); ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
