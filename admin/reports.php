<?php
require_once "../includes/config.php";

if (!hasPermission(["admin", "superuser", "cashier"])) {
    header("Location: ../index.php");
    exit;
}

$period = $_GET["period"] ?? "today";
$custom_start = $_GET["start_date"] ?? "";
$custom_end = $_GET["end_date"] ?? "";

// Build date filter based on period
$date_filter = "";
$params = [];

switch ($period) {
    case "today":
        $date_filter = "DATE(transaction_date) = CURDATE()";
        break;
    case "yesterday":
        $date_filter = "DATE(transaction_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
        break;
    case "this_week":
        $date_filter = "YEARWEEK(transaction_date) = YEARWEEK(CURDATE())";
        break;
    case "last_week":
        $date_filter = "YEARWEEK(transaction_date) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 WEEK))";
        break;
    case "this_month":
        $date_filter = "MONTH(transaction_date) = MONTH(CURDATE()) AND YEAR(transaction_date) = YEAR(CURDATE())";
        break;
    case "last_month":
        $date_filter = "MONTH(transaction_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(transaction_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
        break;
    case "this_year":
        $date_filter = "YEAR(transaction_date) = YEAR(CURDATE())";
        break;
    case "custom":
        if ($custom_start && $custom_end) {
            $date_filter = "DATE(transaction_date) BETWEEN ? AND ?";
            $params = [$custom_start, $custom_end];
        } else {
            $date_filter = "DATE(transaction_date) = CURDATE()";
        }
        break;
}

// Get financial summary
$sql = "SELECT 
            COUNT(CASE WHEN amount > 0 THEN 1 END) as total_transactions,
            SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as total_refunds,
            SUM(amount) as net_income
        FROM transactions 
        WHERE $date_filter";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$summary = $stmt->fetch();

// Get transactions by type
$sql = "SELECT 
            transaction_type,
            COUNT(*) as count,
            SUM(amount) as total
        FROM transactions 
        WHERE $date_filter
        GROUP BY transaction_type";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$by_type = $stmt->fetchAll();

// Get transactions by payment method
$sql = "SELECT 
            payment_method,
            COUNT(*) as count,
            SUM(amount) as total
        FROM transactions 
        WHERE $date_filter AND amount > 0
        GROUP BY payment_method";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$by_payment = $stmt->fetchAll();

// Get detailed transactions
$sql = "SELECT t.*, b.guest_name, r.room_number, r.location, u.full_name as cashier_name
        FROM transactions t
        JOIN bookings b ON t.booking_id = b.id
        JOIN rooms r ON b.room_id = r.id
        JOIN users u ON t.created_by = u.id
        WHERE $date_filter
        ORDER BY t.transaction_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Reports - KIA SERVICED APARTMENT</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .report-filters {
            background: var(--dark-gray);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .summary-card {
            background: var(--dark-gray);
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            border: 2px solid var(--primary-pink);
        }
        .summary-amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-pink);
            margin-bottom: 0.5rem;
        }
        .summary-label {
            color: var(--light-gray);
            font-size: 0.9rem;
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
        <h1 style="color: var(--primary-pink); margin-bottom: 2rem;">Financial Reports</h1>

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

        <div class="report-filters">
            <form method="GET">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                    <div class="form-group">
                        <label>Period:</label>
                        <select name="period" class="form-control" onchange="toggleCustomDates(this.value)">
                            <option value="today" <?php echo $period == "today" ? "selected" : ""; ?>>Today</option>
                            <option value="yesterday" <?php echo $period == "yesterday" ? "selected" : ""; ?>>Yesterday</option>
                            <option value="this_week" <?php echo $period == "this_week" ? "selected" : ""; ?>>This Week</option>
                            <option value="last_week" <?php echo $period == "last_week" ? "selected" : ""; ?>>Last Week</option>
                            <option value="this_month" <?php echo $period == "this_month" ? "selected" : ""; ?>>This Month</option>
                            <option value="last_month" <?php echo $period == "last_month" ? "selected" : ""; ?>>Last Month</option>
                            <option value="this_year" <?php echo $period == "this_year" ? "selected" : ""; ?>>This Year</option>
                            <option value="custom" <?php echo $period == "custom" ? "selected" : ""; ?>>Custom Range</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="custom_dates" style="display: <?php echo $period == "custom" ? "block" : "none"; ?>;">
                        <label>Start Date:</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo $custom_start; ?>">
                    </div>
                    
                    <div class="form-group" id="custom_dates2" style="display: <?php echo $period == "custom" ? "block" : "none"; ?>;">
                        <label>End Date:</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo $custom_end; ?>">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-amount"><?php echo $summary["total_transactions"]; ?></div>
                <div class="summary-label">Total Transactions</div>
            </div>
            <div class="summary-card">
                <div class="summary-amount"><?php echo formatCurrency($summary["total_income"]); ?></div>
                <div class="summary-label">Total Income</div>
            </div>
            <div class="summary-card">
                <div class="summary-amount"><?php echo formatCurrency($summary["total_refunds"]); ?></div>
                <div class="summary-label">Total Refunds</div>
            </div>
            <div class="summary-card">
                <div class="summary-amount"><?php echo formatCurrency($summary["net_income"]); ?></div>
                <div class="summary-label">Net Income</div>
            </div>
        </div>

        <div class="table-container">
            <h2 style="color: var(--primary-pink); margin: 1rem;">Breakdown by Transaction Type</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Transaction Type</th>
                        <th>Count</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($by_type as $type): ?>
                    <tr>
                        <td><?php echo ucfirst(str_replace("_", " ", $type["transaction_type"])); ?></td>
                        <td><?php echo $type["count"]; ?></td>
                        <td><?php echo formatCurrency($type["total"]); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h2 style="color: var(--primary-pink); margin: 1rem;">Breakdown by Payment Method</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Payment Method</th>
                        <th>Count</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($by_payment as $payment): ?>
                    <tr>
                        <td><?php echo ucfirst($payment["payment_method"]); ?></td>
                        <td><?php echo $payment["count"]; ?></td>
                        <td><?php echo formatCurrency($payment["total"]); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h2 style="color: var(--primary-pink); margin: 1rem;">Detailed Transactions</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date/Time</th>
                        <th>Type</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
                        <th>Cashier</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo $transaction["id"]; ?></td>
                        <td><?php echo formatDateTime($transaction["transaction_date"]); ?></td>
                        <td><?php echo ucfirst(str_replace("_", " ", $transaction["transaction_type"])); ?></td>
                        <td><?php echo htmlspecialchars($transaction["guest_name"]); ?></td>
                        <td><?php echo htmlspecialchars($transaction["location"] . " - " . $transaction["room_number"]); ?></td>
                        <td><?php echo ucfirst($transaction["payment_method"]); ?></td>
                        <td style="color: <?php echo $transaction["amount"] >= 0 ? "var(--success)" : "var(--danger)"; ?>">
                            <?php echo formatCurrency($transaction["amount"]); ?>
                        </td>
                        <td><?php echo htmlspecialchars($transaction["cashier_name"]); ?></td>
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
    <script>
        function toggleCustomDates(period) {
            const customDates = document.getElementById("custom_dates");
            const customDates2 = document.getElementById("custom_dates2");
            if (period === "custom") {
                customDates.style.display = "block";
                customDates2.style.display = "block";
            } else {
                customDates.style.display = "none";
                customDates2.style.display = "none";
            }
        }
    </script>
</body>
</html>
