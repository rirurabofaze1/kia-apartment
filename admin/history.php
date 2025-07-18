<?php
require_once "../includes/config.php";

if (!hasPermission(["admin", "superuser", "cashier"])) {
    header("Location: ../index.php");
    exit;
}

// Tab (bookings, transactions, cancellations, rekap)
$view = isset($_GET["view"]) ? trim($_GET["view"]) : "bookings";
$allowed_views = ["bookings", "transactions", "cancellations", "rekap"];
if (!in_array($view, $allowed_views)) $view = "bookings";

// Filter
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Helper
if (!function_exists('formatDateTime')) {
    function formatDateTime($datetime) {
        if (!$datetime) return "-";
        $dt = new DateTime($datetime);
        return htmlspecialchars($dt->format("Y-m-d H:i"));
    }
}
if (!function_exists('safe')) {
    function safe($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

// Get data for each tab
$data = [];
if ($view == "bookings") {
    $sql = "SELECT * FROM bookings WHERE DATE(checkin_time) = :date";
    $params = [':date' => $date];
    if ($search) {
        $sql .= " AND guest_name LIKE :search";
        $params[':search'] = "%$search%";
    }
    $sql .= " ORDER BY checkin_time DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($view == "transactions") {
    // GUNAKAN KOLOM 'date' BUKAN 'created_at'
    $sql = "SELECT * FROM transactions WHERE DATE(date) = :date";
    $params = [':date' => $date];
    if ($search) {
        $sql .= " AND customer_name LIKE :search";
        $params[':search'] = "%$search%";
    }
    $sql .= " ORDER BY date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($view == "cancellations") {
    $sql = "SELECT * FROM bookings WHERE DATE(updated_at) = :date AND status = 'cancelled'";
    $params = [':date' => $date];
    if ($search) {
        $sql .= " AND guest_name LIKE :search";
        $params[':search'] = "%$search%";
    }
    $sql .= " ORDER BY updated_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($view == "rekap") {
    $rekap_mode = isset($_GET['rekap_mode']) ? $_GET['rekap_mode'] : 'harian';
    $rekap_date = isset($_GET['rekap_date']) ? $_GET['rekap_date'] : date('Y-m-d');
    $rekap_week = isset($_GET['rekap_week']) ? $_GET['rekap_week'] : date('o-\WW');
    $roomStmt = $pdo->query("SELECT id, location, room_number FROM rooms ORDER BY location, room_number");
    $rooms = $roomStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rekap_mode === 'harian') {
        $date_start = $rekap_date;
        $date_end = $rekap_date;
    } else {
        $week = $rekap_week;
        $dt = new DateTime();
        $dt->setISODate(substr($week, 0, 4), substr($week, 6, 2));
        $date_start = $dt->format('Y-m-d');
        $dt->modify('+6 days');
        $date_end = $dt->format('Y-m-d');
    }

    $sql = "SELECT r.id as room_id, r.location, r.room_number, 
                    b.duration_type, COUNT(*) as jumlah, SUM(b.price_amount) as total
            FROM rooms r
            LEFT JOIN bookings b ON b.room_id = r.id 
                AND DATE(b.checkin_time) >= ? AND DATE(b.checkin_time) <= ?
            GROUP BY r.id, r.location, r.room_number, b.duration_type
            ORDER BY r.location, r.room_number";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date_start, $date_end]);
    $db_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $rekapData = [];
    foreach ($rooms as $room) {
        $rekapData[$room['id']] = [
            'location' => $room['location'],
            'room_number' => $room['room_number'],
            'fullday_count' => 0,
            'fullday_total' => 0,
            'transit_count' => 0,
            'transit_total' => 0,
        ];
    }
    $total_fullday = 0;
    $total_transit = 0;
    $total_omset = 0;
    foreach ($db_rows as $row) {
        if ($row['duration_type'] == 'fullday') {
            $rekapData[$row['room_id']]['fullday_count'] = $row['jumlah'] ? (int)$row['jumlah'] : 0;
            $rekapData[$row['room_id']]['fullday_total'] = $row['total'] ? (float)$row['total'] : 0;
            $total_fullday += $row['jumlah'];
            $total_omset += $row['total'];
        }
        if ($row['duration_type'] == 'transit') {
            $rekapData[$row['room_id']]['transit_count'] = $row['jumlah'] ? (int)$row['jumlah'] : 0;
            $rekapData[$row['room_id']]['transit_total'] = $row['total'] ? (float)$row['total'] : 0;
            $total_transit += $row['jumlah'];
            $total_omset += $row['total'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat - KIA SERVICED APARTMENT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
      .tabs { display: flex; gap: 1px; margin-bottom: 2rem;}
      .tabs a { 
        padding: .7rem 1.7rem; background: var(--dark-gray); color: var(--primary-pink);
        border-radius: 10px 10px 0 0; text-decoration: none; font-weight: bold; opacity:.87;
      }
      .tabs a.active { background: var(--primary-pink); color: #fff;}
      .btn-back { margin-bottom: 1.7rem; }
      .table-container {background: var(--dark-gray); border-radius: 10px; padding:2rem;}
      .filter-bar { margin-bottom:1.5rem; display: flex; gap:1rem; align-items:center;}
      .summary-box { background: #fff3f6; color: #b4086b; border-radius: 7px; padding: 1rem; margin-bottom: 1rem;}
      @media (max-width: 900px) {
        .filter-bar {flex-direction:column; align-items:flex-start;}
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
    <h1 style="color: var(--primary-pink); margin-bottom: 1rem;">Riwayat Data</h1>
    <a href="dashboard.php" class="btn btn-primary btn-back">⟵ Kembali ke Dashboard</a>
    <div class="tabs">
        <a href="?view=bookings" class="<?= $view == 'bookings' ? 'active' : '' ?>">Bookings</a>
        <a href="?view=transactions" class="<?= $view == 'transactions' ? 'active' : '' ?>">Transactions</a>
        <a href="?view=cancellations" class="<?= $view == 'cancellations' ? 'active' : '' ?>">Cancellations</a>
        <a href="?view=rekap" class="<?= $view == 'rekap' ? 'active' : '' ?>">Rekap</a>
    </div>

    <?php if ($view == "rekap"): ?>
        <div class="filter-bar">
            <form method="get" style="display:flex;gap:.7rem;align-items:center;">
                <input type="hidden" name="view" value="rekap">
                <select name="rekap_mode" onchange="this.form.submit()" class="form-control" style="width:140px;">
                    <option value="harian"<?= (isset($rekap_mode) && $rekap_mode == 'harian') ? ' selected' : '' ?>>Harian</option>
                    <option value="mingguan"<?= (isset($rekap_mode) && $rekap_mode == 'mingguan') ? ' selected' : '' ?>>Mingguan</option>
                </select>
                <input type="date" name="rekap_date" class="form-control" value="<?= safe($rekap_date ?? date('Y-m-d')) ?>" style="<?= (isset($rekap_mode) && $rekap_mode == 'harian') ? '' : 'display:none;' ?>">
                <input type="week" name="rekap_week" class="form-control" value="<?= safe($rekap_week ?? date('o-\WW')) ?>" style="<?= (isset($rekap_mode) && $rekap_mode == 'mingguan') ? '' : 'display:none;' ?>">
                <button class="btn btn-primary" type="submit">Terapkan</button>
            </form>
        </div>
        <div class="summary-box">
            <b>Total Fullday:</b> <?= $total_fullday ?? 0 ?> &nbsp;
            <b>Total Transit:</b> <?= $total_transit ?? 0 ?> &nbsp;
            <b>Total Omset:</b> <?= function_exists('formatCurrency') ? formatCurrency($total_omset ?? 0) : number_format($total_omset ?? 0,0,',','.') ?>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kamar</th>
                        <th>Jumlah Fullday</th>
                        <th>Jumlah Transit</th>
                        <th>Total Omset</th>
                    </tr>
                </thead>
                <tbody>
                <?php $n=1; foreach($rekapData as $room): $total_kamar = $room['fullday_total'] + $room['transit_total'];?>
                    <tr>
                        <td><?= $n++ ?></td>
                        <td><?= safe($room['location'].' no '.$room['room_number']) ?></td>
                        <td><?= $room['fullday_count'] ?></td>
                        <td><?= $room['transit_count'] ?></td>
                        <td><?= function_exists('formatCurrency') ? formatCurrency($total_kamar) : number_format($total_kamar,0,',','.') ?></td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="filter-bar">
            <form method="get" style="display:flex;gap:.7rem;align-items:center;">
                <input type="hidden" name="view" value="<?= safe($view) ?>">
                <label for="date">Tanggal:</label>
                <input type="date" id="date" name="date" class="form-control" value="<?= safe($date) ?>">
                <?php if ($view == "bookings" || $view == "cancellations"): ?>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama tamu" value="<?= safe($search) ?>">
                <?php elseif ($view == "transactions"): ?>
                    <input type="text" name="search" class="form-control" placeholder="Cari customer" value="<?= safe($search) ?>">
                <?php endif;?>
                <button class="btn btn-primary" type="submit">Filter</button>
            </form>
        </div>
        <div class="table-container">
            <?php if ($view == "bookings"): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <!-- <th>Nama Tamu</th>
                            <th>No HP</th> -->
                            <th>Checkin</th>
                            <th>Checkout</th>
                            <th>Kamar</th>
                            <th>Status</th>
                            <th>Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$data): ?>
                            <tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
                        <?php else: foreach ($data as $row): ?>
                            <tr>
                                <td><?= safe($row['id']) ?></td>
                                <!-- <td><?= safe($row['guest_name']) ?></td>
                                <td><?= safe($row['phone_number']) ?></td> -->
                                <td><?= formatDateTime($row['checkin_time']) ?></td>
                                <td><?= formatDateTime($row['checkout_time']) ?></td>
                                <td><?= safe($row['room_id']) ?></td>
                                <td><?= safe($row['status']) ?></td>
                                <td><?= function_exists('formatCurrency') ? formatCurrency($row['price_amount']) : number_format($row['price_amount'],0,',','.') ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            <?php elseif ($view == "transactions"): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Waktu</th>
                            <!-- <th>Nama Customer</th> -->
                            <th>Nominal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$data): ?>
                            <tr><td colspan="5" class="text-center">Tidak ada data</td></tr>
                        <?php else: foreach ($data as $row): ?>
                            <tr>
                                <td><?= safe($row['id']) ?></td>
                                <td><?= formatDateTime($row['date']) ?></td>
                                <!-- <td><?= safe($row['customer_name']) ?></td> -->
                                <td><?= function_exists('formatCurrency') ? formatCurrency($row['amount']) : number_format($row['amount'],0,',','.') ?></td>
                                <td><?= safe($row['status']) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            <?php elseif ($view == "cancellations"): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <!-- <th>Nama Tamu</th> -->
                            <th>Waktu Batal</th>
                            <th>Kamar</th>
                            <th>Harga</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$data): ?>
                            <tr><td colspan="6" class="text-center">Tidak ada data</td></tr>
                        <?php else: foreach ($data as $row): ?>
                            <tr>
                                <td><?= safe($row['id']) ?></td>
                                <!-- <td><?= safe($row['guest_name']) ?></td> -->
                                <td><?= formatDateTime($row['updated_at']) ?></td>
                                <td><?= safe($row['room_id']) ?></td>
                                <td><?= function_exists('formatCurrency') ? formatCurrency($row['price_amount']) : number_format($row['price_amount'],0,',','.') ?></td>
                                <td><?= safe($row['status']) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<footer class="footer">
    <p>&copy; 2024 KIA SERVICED APARTMENT - Copyright by Riruuu Rabofezu</p>
</footer>
<script>
    // Toggle date/week filter for rekap
    document.querySelector('select[name="rekap_mode"]')?.addEventListener('change', function() {
        if (this.value === 'harian') {
            document.querySelector('input[name="rekap_date"]').style.display = '';
            document.querySelector('input[name="rekap_week"]').style.display = 'none';
        } else {
            document.querySelector('input[name="rekap_date"]').style.display = 'none';
            document.querySelector('input[name="rekap_week"]').style.display = '';
        }
    });
</script>
</body>
</html>