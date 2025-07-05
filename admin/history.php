<?php
require_once "../includes/config.php";

// Security: Check user permissions
if (!hasPermission(["admin", "superuser", "cashier"])) {
    header("Location: ../index.php");
    exit;
}

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

// Custom date range
$start_date = isset($_GET["start_date"]) ? trim($_GET["start_date"]) : "";
$end_date = isset($_GET["end_date"]) ? trim($_GET["end_date"]) : "";

// Build date filter with proper parameterization
$date_filter = "";
$params = [];

if ($start_date && $end_date) {
    // Custom date range
    $date_filter = "DATE(b.created_at) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
} else {
    // Predefined periods
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
        
    } elseif ($view == "transactions") {
        // Adjust date filter for transactions table
        // Build separate parameters for transactions view
        $trans_params = [];
        $trans_date_filter = str_replace(["b.created_at", "created_at"], "t.transaction_date", $date_filter);
        
        // Reset params for transactions and rebuild them
        $trans_params = [];
        if ($start_date && $end_date) {
            $trans_params[] = $start_date;
            $trans_params[] = $end_date;
        }
        
        // Add room filter if specified
        if ($room_id) {
            $trans_params[] = $room_id;
        }
        
        // Add search filter parameters
        if ($search) {
            $search_param = "%" . $search . "%";
            $trans_params = array_merge($trans_params, [$search_param, $search_param, $search_param, $search_param]);
        }
        
        // Count total records
        $count_sql = "SELECT COUNT(*) as total
                      FROM transactions t
                      JOIN bookings b ON t.booking_id = b.id
                      JOIN rooms r ON b.room_id = r.id
                      JOIN users u ON t.created_by = u.id
                      WHERE $trans_date_filter $search_filter";
        
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($trans_params);
        $total_records = $count_stmt->fetch()["total"];
        
        // Get paginated data
        $sql = "SELECT t.*, b.guest_name, r.room_number, r.location, u.full_name as created_by_name
                FROM transactions t
                JOIN bookings b ON t.booking_id = b.id
                JOIN rooms r ON b.room_id = r.id
                JOIN users u ON t.created_by = u.id
                WHERE $trans_date_filter $search_filter
                ORDER BY t.transaction_date DESC
                LIMIT ? OFFSET ?";
        
        $trans_params[] = $per_page;
        $trans_params[] = $offset;
        $params = $trans_params;
        
    } elseif ($view == "cancellations") {
        // Count total records
        $count_sql = "SELECT COUNT(*) as total
                      FROM bookings b
                      JOIN rooms r ON b.room_id = r.id
                      JOIN users u ON b.created_by = u.id
                      WHERE b.status IN ('cancelled', 'no_show') AND $date_filter $search_filter";
        
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetch()["total"];
        
        // Get paginated data
        $sql = "SELECT b.*, r.room_number, r.location, r.room_type, u.full_name as created_by_name
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                JOIN users u ON b.created_by = u.id
                WHERE b.status IN ('cancelled', 'no_show') AND $date_filter $search_filter
                ORDER BY b.updated_at DESC
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

// Calculate summary statistics
$summary_stats = [];
if (!empty($data)) {
    try {
        if ($view == "bookings") {
            $total_amount = array_sum(array_column($data, "price_amount"));
            $total_extra = array_sum(array_column($data, "extra_time_amount"));
            $summary_stats = [
                "total_bookings" => count($data),
                "total_revenue" => $total_amount + $total_extra,
                "average_booking" => ($total_amount + $total_extra) / count($data)
            ];
        } elseif ($view == "transactions") {
            $total_amount = array_sum(array_column($data, "amount"));
            $summary_stats = [
                "total_transactions" => count($data),
                "total_amount" => $total_amount,
                "average_transaction" => $total_amount / count($data)
            ];
        }
    } catch (Exception $e) {
        error_log("Error calculating summary stats: " . $e->getMessage());
    }
}

// Helper function to build URL with current parameters
function buildUrl($new_params = []) {
    global $view, $period, $room_id, $search, $start_date, $end_date;
    
    $params = [
        "view" => $view,
        "period" => $period,
        "room_id" => $room_id,
        "search" => $search,
        "start_date" => $start_date,
        "end_date" => $end_date
    ];
    
    $params = array_merge($params, $new_params);
    $params = array_filter($params, function($value) {
        return $value !== "" && $value !== null;
    });
    
    return "history.php?" . http_build_query($params);
}

// Helper function to get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case "booked":
            return "status-booked";
        case "checkin":
            return "status-checkin";
        case "checkout":
            return "status-checkout";
        case "cancelled":
            return "status-cancelled";
        case "no_show":
            return "status-no-show";
        default:
            return "status-default";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - KIA SERVICED APARTMENT</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .history-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .history-filters {
            background: var(--dark-gray);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .filter-group label {
            color: var(--light-gray);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 0.75rem;
            border: 2px solid var(--dark-gray);
            border-radius: 8px;
            background: var(--black);
            color: var(--white);
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary-pink);
            box-shadow: 0 0 0 3px rgba(255, 105, 180, 0.1);
        }
        
        .view-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .view-tab {
            padding: 0.75rem 1.5rem;
            background: var(--dark-gray);
            color: var(--light-gray);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            border: 2px solid transparent;
        }
        
        .view-tab.active {
            background: linear-gradient(135deg, var(--primary-pink), var(--dark-pink));
            color: var(--white);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(255, 105, 180, 0.3);
        }
        
        .view-tab:hover:not(.active) {
            background: var(--dark-pink);
            color: var(--white);
            transform: translateY(-2px);
        }
        
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--dark-gray), var(--black));
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            border: 1px solid rgba(255, 105, 180, 0.2);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-pink);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--light-gray);
            font-size: 0.9rem;
        }
        
        .data-table-container {
            background: var(--dark-gray);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .table-header {
            background: linear-gradient(135deg, var(--primary-pink), var(--dark-pink));
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--white);
        }
        
        .table-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-export {
            background: rgba(255, 255, 255, 0.2);
            color: var(--white);
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .btn-export:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--dark-gray);
        }
        
        .data-table th {
            background: var(--black);
            color: var(--primary-pink);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--primary-pink);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--white);
        }
        
        .data-table tr:hover {
            background: rgba(255, 105, 180, 0.1);
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-booked { background: var(--info); color: var(--white); }
        .status-checkin { background: var(--warning); color: var(--white); }
        .status-checkout { background: var(--success); color: var(--white); }
        .status-cancelled { background: var(--danger); color: var(--white); }
        .status-no-show { background: #6c757d; color: var(--white); }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            background: var(--dark-gray);
            color: var(--white);
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        
        .pagination a:hover {
            background: var(--primary-pink);
            border-color: var(--primary-pink);
        }
        
        .pagination .current {
            background: var(--primary-pink);
            border-color: var(--primary-pink);
        }
        
        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .no-data {
            text-align: center;
            padding: 3rem;
            color: var(--light-gray);
        }
        
        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .error-message {
            background: var(--danger);
            color: var(--white);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .loading {
            text-align: center;
            padding: 2rem;
            color: var(--light-gray);
        }
        
        .loading i {
            font-size: 2rem;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .history-container {
                padding: 1rem;
            }
            
            .filter-row {
                grid-template-columns: 1fr;
            }
            
            .view-tabs {
                flex-direction: column;
            }
            
            .data-table-container {
                overflow-x: auto;
            }
            
            .data-table {
                min-width: 800px;
            }
            
            .table-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .summary-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="../index.php" class="logo">KIA SERVICED APARTMENT</a>
            <div class="header-actions">
                <span style="color: var(--primary-pink); margin-right: 1rem;">
                    Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]); ?> (<?php echo ucfirst($_SESSION["user_role"]); ?>)
                </span>
                <a href="../countdown.php" class="btn-countdown-header">
                    <i class="fas fa-clock countdown-icon-small"></i>
                    Live Countdown
                </a>
                <a href="../includes/logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <div class="history-container">
        <h1 style="color: var(--primary-pink); margin-bottom: 2rem; text-align: center;">
            <i class="fas fa-history"></i> History & Reports
        </h1>

        <?php if (isset($error_message)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <!-- View Tabs -->
        <div class="view-tabs">
            <a href="<?php echo buildUrl(["view" => "bookings", "page" => 1]); ?>" 
               class="view-tab <?php echo $view == "bookings" ? "active" : ""; ?>">
                <i class="fas fa-bed"></i>
                Bookings History
            </a>
            <a href="<?php echo buildUrl(["view" => "transactions", "page" => 1]); ?>" 
               class="view-tab <?php echo $view == "transactions" ? "active" : ""; ?>">
                <i class="fas fa-money-bill-wave"></i>
                Transactions
            </a>
            <a href="<?php echo buildUrl(["view" => "cancellations", "page" => 1]); ?>" 
               class="view-tab <?php echo $view == "cancellations" ? "active" : ""; ?>">
                <i class="fas fa-times-circle"></i>
                Cancellations
            </a>
        </div>

        <!-- Filters -->
        <div class="history-filters">
            <form method="GET" action="history.php" id="filterForm">
                <input type="hidden" name="view" value="<?php echo htmlspecialchars($view); ?>">
                <input type="hidden" name="page" value="1">
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="period">Time Period</label>
                        <select name="period" id="period" onchange="toggleCustomDates()">
                            <option value="today" <?php echo $period == "today" ? "selected" : ""; ?>>Today</option>
                            <option value="yesterday" <?php echo $period == "yesterday" ? "selected" : ""; ?>>Yesterday</option>
                            <option value="this_week" <?php echo $period == "this_week" ? "selected" : ""; ?>>This Week</option>
                            <option value="last_week" <?php echo $period == "last_week" ? "selected" : ""; ?>>Last Week</option>
                            <option value="this_month" <?php echo $period == "this_month" ? "selected" : ""; ?>>This Month</option>
                            <option value="last_month" <?php echo $period == "last_month" ? "selected" : ""; ?>>Last Month</option>
                            <option value="this_year" <?php echo $period == "this_year" ? "selected" : ""; ?>>This Year</option>
                            <option value="custom" <?php echo ($start_date && $end_date) ? "selected" : ""; ?>>Custom Range</option>
                            <option value="all" <?php echo $period == "all" ? "selected" : ""; ?>>All Time</option>
                        </select>
                    </div>
                    
                    <div class="filter-group" id="customDates" style="display: <?php echo ($start_date && $end_date) ? 'block' : 'none'; ?>;">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    
                    <div class="filter-group" id="customDatesEnd" style="display: <?php echo ($start_date && $end_date) ? 'block' : 'none'; ?>;">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="room_id">Room</label>
                        <select name="room_id" id="room_id">
                            <option value="">All Rooms</option>
                            <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room["id"]; ?>" <?php echo $room_id == $room["id"] ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($room["location"] . " - " . $room["room_number"]); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <input type="text" name="search" id="search" placeholder="Guest name, phone, room..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="history.php?view=<?php echo $view; ?>" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Summary Statistics -->
        <?php if (!empty($summary_stats)): ?>
        <div class="summary-stats">
            <?php if ($view == "bookings"): ?>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($summary_stats["total_bookings"]); ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo formatCurrency($summary_stats["total_revenue"]); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo formatCurrency($summary_stats["average_booking"]); ?></div>
                <div class="stat-label">Average per Booking</div>
            </div>
            <?php elseif ($view == "transactions"): ?>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($summary_stats["total_transactions"]); ?></div>
                <div class="stat-label">Total Transactions</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo formatCurrency($summary_stats["total_amount"]); ?></div>
                <div class="stat-label">Total Amount</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo formatCurrency($summary_stats["average_transaction"]); ?></div>
                <div class="stat-label">Average Transaction</div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Data Table -->
        <div class="data-table-container">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-<?php echo $view == 'bookings' ? 'bed' : ($view == 'transactions' ? 'money-bill-wave' : 'times-circle'); ?>"></i>
                    <?php echo ucfirst($view); ?> 
                    (<?php echo number_format($total_records ?? 0); ?> records)
                </div>
                <div class="table-actions">
                    <a href="export.php?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>" class="btn-export">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </a>
                    <a href="export.php?<?php echo http_build_query(array_merge($_GET, ['export' => 'pdf'])); ?>" class="btn-export">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
            
            <?php if (empty($data)): ?>
            <div class="no-data">
                <i class="fas fa-inbox"></i>
                <h3>No records found</h3>
                <p>Try adjusting your filters or search criteria.</p>
            </div>
            <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <?php if ($view == "bookings"): ?>
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
                            <?php elseif ($view == "transactions"): ?>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Payment Method</th>
                                <th>Amount</th>
                                <th>Created By</th>
                            <?php elseif ($view == "cancellations"): ?>
                                <th>ID</th>
                                <th>Room</th>
                                <th>Guest Name</th>
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
                                    <span class="status-badge <?php echo getStatusBadgeClass($record["status"]); ?>">
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
                                    <span class="status-badge <?php echo getStatusBadgeClass($record["status"]); ?>">
                                        <?php echo ucfirst(str_replace("_", " ", $record["status"])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($record["notes"] ?: "-"); ?></td>
                                <td><?php echo formatDateTime($record["updated_at"]); ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?php echo buildUrl(['page' => 1]); ?>" title="First Page">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="<?php echo buildUrl(['page' => $page - 1]); ?>" title="Previous Page">
                    <i class="fas fa-angle-left"></i>
                </a>
            <?php endif; ?>
            
            <?php
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="<?php echo buildUrl(['page' => $i]); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="<?php echo buildUrl(['page' => $page + 1]); ?>" title="Next Page">
                    <i class="fas fa-angle-right"></i>
                </a>
                <a href="<?php echo buildUrl(['page' => $total_pages]); ?>" title="Last Page">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin-top: 1rem; color: var(--light-gray);">
            Showing <?php echo (($page - 1) * $per_page) + 1; ?> to <?php echo min($page * $per_page, $total_records); ?> 
            of <?php echo number_format($total_records); ?> records
        </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2024 KIA SERVICED APARTMENT - Copyright by Riruuu Rabofezu</p>
    </footer>

    <script src="../assets/js/main.js"></script>
    <script>
        function toggleCustomDates() {
            const period = document.getElementById('period').value;
            const customDates = document.getElementById('customDates');
            const customDatesEnd = document.getElementById('customDatesEnd');
            
            if (period === 'custom') {
                customDates.style.display = 'block';
                customDatesEnd.style.display = 'block';
            } else {
                customDates.style.display = 'none';
                customDatesEnd.style.display = 'none';
            }
        }
        
        // Auto-submit form when filters change
        document.getElementById('period').addEventListener('change', function() {
            if (this.value !== 'custom') {
                document.getElementById('filterForm').submit();
            }
        });
        
        document.getElementById('room_id').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
        
        // Search with debounce
        let searchTimeout;
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 500);
        });
        
        // Initialize custom dates visibility
        toggleCustomDates();
    </script>
</body>
</html>
