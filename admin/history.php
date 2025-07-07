<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../includes/config.php";

// Cek akses
if (!hasPermission(["admin", "superuser", "cashier"])) {
    header("Location: ../index.php");
    exit;
}

// Tab (bookings, transactions, cancellations, rekap)
$view = isset($_GET["view"]) ? trim($_GET["view"]) : "bookings";
$allowed_views = ["bookings", "transactions", "cancellations", "rekap"];
if (!in_array($view, $allowed_views)) $view = "bookings";

// Filter periode untuk rekap
$rekap_mode = isset($_GET['rekap_mode']) ? $_GET['rekap_mode'] : 'harian'; // harian/mingguan
$rekap_date = isset($_GET['rekap_date']) ? $_GET['rekap_date'] : date('Y-m-d');
$rekap_week = isset($_GET['rekap_week']) ? $_GET['rekap_week'] : date('o-\WW');

// Query rooms untuk dropdown
$stmt = $pdo->query("SELECT id, location, room_number FROM rooms ORDER BY location, room_number");
$rooms = $stmt->fetchAll();

if ($view === "rekap") {
    // Query rekap harian/mingguan
    // 1. Ambil semua kamar
    // 2. Untuk setiap kamar, hitung jumlah booking fullday, transit, nominalnya pada hari/minggu yang dipilih

    $rekapData = [];
    $total_fullday = 0;
    $total_transit = 0;
    $total_omset = 0;

    if ($rekap_mode === 'harian') {
        // Filter tanggal
        $date_start = $rekap_date;
        $date_end = $rekap_date;
    } else {
        // Mingguan (ISO week)
        $week = $rekap_week;
        $dt = new DateTime();
        $dt->setISODate(substr($week, 0, 4), substr($week, 6, 2));
        $date_start = $dt->format('Y-m-d');
        $dt->modify('+6 days');
        $date_end = $dt->format('Y-m-d');
    }

    // Ambil data booking pada rentang waktu
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

    // Susun data kamar
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

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return "Rp " . number_format($amount, 0, ',', '.');
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Booking & Rekap</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"/>
    <style>
        .summary-box { background:#f8f9fa; padding:18px; border-radius:8px; margin-bottom:18px; }
        .tab-pane { margin-top:18px; }
        .pagination a, .pagination span { margin:0 2px; }
    </style>
</head>
<body class="container py-4">
    <h2>Riwayat</h2>
    <ul class="nav nav-tabs">
        <li class="nav-item"><a class="nav-link<?= $view=='bookings'?' active':'' ?>" href="?view=bookings">Bookings</a></li>
        <li class="nav-item"><a class="nav-link<?= $view=='transactions'?' active':'' ?>" href="?view=transactions">Transactions</a></li>
        <li class="nav-item"><a class="nav-link<?= $view=='cancellations'?' active':'' ?>" href="?view=cancellations">Cancellations</a></li>
        <li class="nav-item"><a class="nav-link<?= $view=='rekap'?' active':'' ?>" href="?view=rekap">Rekap Harian/Mingguan</a></li>
    </ul>

    <?php if($view === "rekap"): ?>
        <div class="mt-3">
            <form class="row g-2 mb-2" method="get">
                <input type="hidden" name="view" value="rekap">
                <div class="col-auto">
                    <select name="rekap_mode" class="form-select" onchange="this.form.submit()">
                        <option value="harian"<?= $rekap_mode=='harian'?' selected':'' ?>>Harian</option>
                        <option value="mingguan"<?= $rekap_mode=='mingguan'?' selected':'' ?>>Mingguan</option>
                    </select>
                </div>
                <div class="col-auto" <?= $rekap_mode=='harian'?'':'style="display:none;"' ?>>
                    <input type="date" name="rekap_date" class="form-control" value="<?= htmlspecialchars($rekap_date) ?>" onchange="this.form.submit()">
                </div>
                <div class="col-auto" <?= $rekap_mode=='mingguan'?'':'style="display:none;"' ?>>
                    <input type="week" name="rekap_week" class="form-control" value="<?= htmlspecialchars($rekap_week) ?>" onchange="this.form.submit()">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">Terapkan</button>
                </div>
            </form>
        </div>
        <div class="summary-box">
            <b>Total Fullday:</b> <?= $total_fullday ?> &nbsp;
            <b>Total Transit:</b> <?= $total_transit ?> &nbsp;
            <b>Total Omset:</b> <?= formatCurrency($total_omset) ?>
        </div>
        <table class="table table-bordered table-striped">
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
            <?php
            $n=1;
            foreach($rekapData as $room) {
                $total_kamar = $room['fullday_total'] + $room['transit_total'];
                echo "<tr>";
                echo "<td>".$n++."</td>";
                echo "<td>".htmlspecialchars($room['location'].' no '.$room['room_number'])."</td>";
                echo "<td>".$room['fullday_count']."</td>";
                echo "<td>".$room['transit_count']."</td>";
                echo "<td>".formatCurrency($total_kamar)."</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
    <?php else: ?>
        <!-- Tab Booking/Transaction/Cancellation (kode lama tetap di sini) -->
        <div class="tab-content">
            <div class="tab-pane fade show active">
                <p>Pilih tab <b>Rekap Harian/Mingguan</b> untuk fitur rekapitulasi berdasarkan kamar.</p>
                <!-- Kode tab bookings, transactions, cancellations tetap di sini -->
            </div>
        </div>
    <?php endif; ?>

    <script>
    // Otomatis submit saat ganti filter tanpa perlu klik Terapkan
    document.querySelectorAll('select[name="rekap_mode"],input[name="rekap_date"],input[name="rekap_week"]').forEach(function(el){
        el.addEventListener('change',function(){ this.form.submit(); });
    });
    </script>
</body>
</html>