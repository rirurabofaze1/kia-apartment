<?php
// Test version of history.php without authentication for functionality testing
require_once "../includes/config.php";

// Bypass authentication for testing
$_SESSION["user_id"] = 1;
$_SESSION["user_role"] = "admin";
$_SESSION["full_name"] = "Test Admin";

// Input validation and sanitization
$view = isset($_GET["view"]) ? trim($_GET["view"]) : "bookings";
$allowed_views = ["bookings", "transactions", "cancellations"];
if (!in_array($view, $allowed_views)) {
    $view = "bookings";
}

$period = isset($_GET["period"]) ? trim($_GET["period"]) : "this_month";
$allowed_periods = ["today", "yesterday", "this_week", "last_week", "this_month", "last_month", "this_year", "all"];
if (!in_array($period, $allowed_periods)) {
    $period = "this_month";
}

$room_id = isset($_GET["room_id"]) && is_numeric($_GET["room_id"]) ? (int)$_GET["room_id"] : "";
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? max(1, (int)$_GET["page"]) : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Build date filter
$date_filter = "";
$params = [];

switch ($period) {
    case "today":
        $date_filter = "DATE(b.created_at) = CURDATE()";
        break;
    case "yesterday":
        $date_filter = "DATE(b.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
        break;
    case "this_week":
        $date_filter = "YEARWEEK(b.created_at, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case "last_week":
        $date_filter = "YEARWEEK(b.created_at, 1) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 WEEK), 1)";
        break;
    case "this_month":
        $date_filter = "MONTH(b.created_at) = MONTH(CURDATE()) AND YEAR(b.created_at) = YEAR(CURDATE())";
        break;
    case "last_month":
        $date_filter = "MONTH(b.created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(b.created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
        break;
    case "this_year":
        $date_filter = "YEAR(b.created_at) = YEAR(CURDATE())";
        break;
    case "all":
        $date_filter = "1=1";
        break;
}

// Add room filter if specified
if ($room_id) {
    $date_filter .= " AND b.room_id = ?";
    $params[] = $room_id;
}

// Add search filter if specified
$search_filter = "";
if ($search) {
    $search_filter = " AND (b.guest_name LIKE ? OR b.phone_number LIKE ? OR r.room_number LIKE ? OR r.location LIKE ?)";
    $search_param = "%" . $search . "%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

try {
    // Get data based on view with pagination
    if ($view == "bookings") {
        // Count total records for pagination
        $count_sql = "SELECT COUNT(*) as total
                      FROM bookings b
                      JOIN rooms r ON b.room_id = r.id
                      JOIN users u ON b.created_by = u.id
                      WHERE $date_filter $search_filter";
        
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetch()["total"];
        
        // Get paginated data
        $sql = "SELECT b.*, r.room_number, r.location, r.room_type, u.full_name as created_by_name
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                JOIN users u ON b.created_by = u.id
                WHERE $date_filter $search_filter
                ORDER BY b.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $per_page;
        $params[] = $offset;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    
    // Calculate pagination
    $total_pages = ceil($total_records / $per_page);
    
} catch (PDOException $e) {
    error_log("Database error in history.php: " . $e->getMessage());
    $data = [];
    $total_records = 0;
    $total_pages = 0;
    $error_message = "An error occurred while fetching data. Please try again.";
}

// Get rooms for filter dropdown
try {
    $stmt = $pdo->query("SELECT id, location, room_number FROM rooms ORDER BY location, room_number");
    $rooms = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching rooms: " . $e->getMessage());
    $rooms = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Test - KIA SERVICED APARTMENT</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #1a1a1a; color: white; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #ff69b4; padding: 1rem; margin-bottom: 2rem; border-radius: 8px; }
        .filters { background: #2d2d2d; padding: 1rem; margin-bottom: 2rem; border-radius: 8px; }
        .filter-group { margin-bottom: 1rem; }
        .filter-group label { display: block; margin-bottom: 0.5rem; color: #f5f5f5; }
        .filter-group select, .filter-group input { padding: 0.5rem; border-radius: 4px; border: 1px solid #555; background: #1a1a1a; color: white; }
        table { width: 100%; border-collapse: collapse; background: #2d2d2d; border-radius: 8px; overflow: hidden; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #555; }
        th { background: #ff69b4; color: white; }
        tr:hover { background: rgba(255, 105, 180, 0.1); }
        .btn { padding: 0.5rem 1rem; background: #ff69b4; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #e91e63; }
        .status-booked { background: #2196f3; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
        .status-checkin { background: #ff9800; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
        .status-checkout { background: #4caf50; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
        .pagination { text-align: center; margin-top: 2rem; }
        .pagination a { padding: 0.5rem 1rem; margin: 0 0.25rem; background: #2d2d2d; color: white; text-decoration: none; border-radius: 4px; }
        .pagination a:hover { background: #ff69b4; }
        .pagination .current { background: #ff69b4; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>KIA SERVICED APARTMENT - History Test</h1>
            <p>Testing improved history.php functionality</p>
        </div>

        <div class="filters">
            <form method="GET">
                <div class="filter-group">
                    <label for="period">Time Period:</label>
                    <select name="period" id="period">
                        <option value="today" <?php echo $period == "today" ? "selected" : ""; ?>>Today</option>
                        <option value="this_week" <?php echo $period == "this_week" ? "selected" : ""; ?>>This Week</option>
                        <option value="this_month" <?php echo $period == "this_month" ? "selected" : ""; ?>>This Month</option>
                        <option value="all" <?php echo $period == "all" ? "selected" : ""; ?>>All Time</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="search">Search:</label>
                    <input type="text" name="search" id="search" placeholder="Guest name, phone, room..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <button type="submit" class="btn">Apply Filters</button>
                <a href="history_test.php" class="btn">Reset</a>
            </form>
        </div>

        <div style="margin-bottom: 1rem;">
            <strong>Total Records:</strong> <?php echo number_format($total_records ?? 0); ?>
        </div>

        <?php if (empty($data)): ?>
        <div style="text-align: center; padding: 2rem; background: #2d2d2d; border-radius: 8px;">
            <h3>No records found</h3>
            <p>Try adjusting your filters or search criteria.</p>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Room</th>
                    <th>Guest Name</th>
                    <th>Phone</th>
                    <th>Arrival</th>
                    <th>Duration</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $record): ?>
                <tr>
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
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&period=<?php echo $period; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
