<?php
require_once "../includes/config.php";

if (!hasPermission(["cashier", "admin", "superuser"])) {
    header("Location: ../index.php");
    exit;
}

// Handle shift report submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["end_shift"])) {
    $notes = $_POST["notes"] ?? "";
    
    // Get todays transactions for this user
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_transactions, SUM(amount) as total_amount 
                          FROM transactions 
                          WHERE DATE(transaction_date) = CURDATE() 
                          AND created_by = ? 
                          AND amount > 0");
    $stmt->execute([$_SESSION["user_id"]]);
    $shift_data = $stmt->fetch();
    
    // Insert shift report
    $stmt = $pdo->prepare("INSERT INTO shift_reports (user_id, shift_date, total_transactions, total_amount, notes) 
                          VALUES (?, CURDATE(), ?, ?, ?)");
    $stmt->execute([
        $_SESSION["user_id"],
        $shift_data["total_transactions"],
        $shift_data["total_amount"],
        $notes
    ]);
    
    $success = "Shift report submitted successfully!";
}

// Get todays transactions for current user
$stmt = $pdo->prepare("SELECT t.*, b.guest_name, r.room_number, r.location
                      FROM transactions t
                      JOIN bookings b ON t.booking_id = b.id
                      JOIN rooms r ON b.room_id = r.id
                      WHERE DATE(t.transaction_date) = CURDATE() 
                      AND t.created_by = ?
                      ORDER BY t.transaction_date DESC");
$stmt->execute([$_SESSION["user_id"]]);
$today_transactions = $stmt->fetchAll();

// Calculate totals
$total_transactions = 0;
$total_amount = 0;
foreach ($today_transactions as $transaction) {
    if ($transaction["amount"] > 0) {
        $total_transactions++;
        $total_amount += $transaction["amount"];
    }
}

// Check if shift report already submitted today
$stmt = $pdo->prepare("SELECT id FROM shift_reports WHERE user_id = ? AND shift_date = CURDATE()");
$stmt->execute([$_SESSION["user_id"]]);
$already_submitted = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>End Shift Report - KIA SERVICED APARTMENT</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .shift-summary {
            background: var(--dark-gray);
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border: 2px solid var(--primary-pink);
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .summary-item {
            text-align: center;
            padding: 1rem;
            background: var(--black);
            border-radius: 5px;
        }
        .summary-number {
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
        <h1 style="color: var(--primary-pink); margin-bottom: 2rem;">End Shift Report</h1>

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

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($already_submitted): ?>
            <div class="alert alert-info">You have already submitted your shift report for today.</div>
        <?php endif; ?>

        <div class="shift-summary">
            <h2 style="color: var(--primary-pink); margin-bottom: 1rem;">Todays Shift Summary</h2>
            <p style="color: var(--light-gray); margin-bottom: 1rem;">
                Date: <?php echo date("d/m/Y"); ?> | 
                Cashier: <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
            </p>
            
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-number"><?php echo $total_transactions; ?></div>
                    <div class="summary-label">Total Transactions</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number"><?php echo formatCurrency($total_amount); ?></div>
                    <div class="summary-label">Total Amount</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number"><?php echo count($today_transactions); ?></div>
                    <div class="summary-label">All Activities</div>
                </div>
            </div>

            <?php if (!$already_submitted): ?>
            <form method="POST" style="margin-top: 1rem;">
                <div class="form-group">
                    <label>Additional Notes (Optional):</label>
                    <textarea name="notes" class="form-control" rows="3" 
                              placeholder="Any additional notes about your shift..."></textarea>
                </div>
                <button type="submit" name="end_shift" class="btn btn-success" 
                        onclick="return confirm(\"Are you sure you want to end your shift? This action cannot be undone.\")">
                    End Shift & Submit Report
                </button>
            </form>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <h2 style="color: var(--primary-pink); margin: 1rem;">Todays Transactions</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($today_transactions)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--light-gray);">
                            No transactions found for today.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($today_transactions as $transaction): ?>
                        <tr>
                            <td><?php echo date("H:i:s", strtotime($transaction["transaction_date"])); ?></td>
                            <td><?php echo ucfirst(str_replace("_", " ", $transaction["transaction_type"])); ?></td>
                            <td><?php echo htmlspecialchars($transaction["guest_name"]); ?></td>
                            <td><?php echo htmlspecialchars($transaction["location"] . " - " . $transaction["room_number"]); ?></td>
                            <td><?php echo ucfirst($transaction["payment_method"]); ?></td>
                            <td style="color: <?php echo $transaction["amount"] >= 0 ? "var(--success)" : "var(--danger)"; ?>">
                                <?php echo formatCurrency($transaction["amount"]); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (hasPermission(["admin", "superuser"])): ?>
            <?php
            $stmt = $pdo->prepare("SELECT sr.*, u.full_name 
                                  FROM shift_reports sr 
                                  JOIN users u ON sr.user_id = u.id 
                                  ORDER BY sr.shift_date DESC, sr.created_at DESC 
                                  LIMIT 10");
            $stmt->execute();
            $recent_reports = $stmt->fetchAll();
            ?>
            
            <div class="table-container">
                <h2 style="color: var(--primary-pink); margin: 1rem;">Recent Shift Reports</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Cashier</th>
                            <th>Transactions</th>
                            <th>Total Amount</th>
                            <th>Notes</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_reports as $report): ?>
                        <tr>
                            <td><?php echo formatDate($report["shift_date"]); ?></td>
                            <td><?php echo htmlspecialchars($report["full_name"]); ?></td>
                            <td><?php echo $report["total_transactions"]; ?></td>
                            <td><?php echo formatCurrency($report["total_amount"]); ?></td>
                            <td><?php echo htmlspecialchars($report["notes"] ?: "-"); ?></td>
                            <td><?php echo formatDateTime($report["created_at"]); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2024 KIA SERVICED APARTMENT - Copyright by Riruuu Rabofezu</p>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>
