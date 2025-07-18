<?php
require_once "../includes/config.php";

if (!isLoggedIn()) {
    die('Not authorized');
}

$booking_id = $_GET['booking_id'] ?? 0;

// Get booking details (tambahkan planned_checkout_time jika ada di DB)
$stmt = $pdo->prepare("SELECT b.*, r.room_number, r.location, r.room_type, r.wifi_name, r.floor_number, r.wifi_password, u.full_name as created_by_name 
                      FROM bookings b 
                      JOIN rooms r ON b.room_id = r.id 
                      JOIN users u ON b.created_by = u.id 
                      WHERE b.id = ?");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    die('Booking not found');
}

// Calculate total amount
$total_amount = $booking['price_amount'] + $booking['extra_time_amount'] - $booking['refund_amount'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk | KIA SERVICED APARTMENT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: monospace, Arial, sans-serif;
            margin: 0;
            width: 58mm;
            background: white;
        }
        #struk {
            width: 58mm;
            max-width: 58mm;
            padding: 20px;
            font-size: 15px;
            color: #000;
        }
        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        table {
            width: 100%;
        }
        td {
            vertical-align: top;
        }
        #buttons {
            margin-top: 20px;
        }
        button, a {
            padding: 8px 12px;
            background-color: #333;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            margin-right: 10px;
            cursor: pointer;
        }
        button:hover, a:hover {
            background-color: #555;
        }
        @media print {
            body { margin: 0; }
            #buttons { display: none; }
        }
    </style>
</head>
<body>
<div id="struk">
    <center>
        <div style="width: 100%; height: 120px; overflow: hidden; position: relative;">
            <img src="../assets/images/logo.png" alt="Logo Strip"
                 style="width: 100%; position: absolute; top: 50%; left: 0; transform: translateY(-50%);">
        </div>
        <p>
            Ruko A1 Gedung Pink Apartment Grand Sentraland Wadas<br>
            Telukjambe Timur, Karawang, Jawa Barat 41361<br>
            0895-3171-0777
        </p>
        <hr />
        <p><?= formatDateTime($booking['created_at']); ?><br/>Room No.<?= htmlspecialchars($booking['room_number']); ?></p>
    </center>
    <p>Receipt ID: <?= $booking['id'] ?></p>
    <table>
        <tr>
            <td>Room</td>
            <td style="text-align:right;"><?= htmlspecialchars($booking['location'] . ' Lt ' . $booking['floor_number'] . ' No.' . $booking['room_number']); ?></td>
        </tr>
        <tr>
            <td>WiFi</td>
            <td style="text-align:right;"><?= htmlspecialchars($booking['wifi_name']); ?></td>
        </tr>
        <tr>
            <td>WiFi Pass</td>
            <td style="text-align:right;"><?= htmlspecialchars($booking['wifi_password']); ?></td>
        </tr>
        <tr>
            <td>Duration</td>
            <td style="text-align:right;">
                <?php
                if ($booking['duration_type'] == 'fullday') {
                    echo "Full Day";
                } else {
                    echo $booking['duration_hours'] . " Jam (" . ucfirst($booking['duration_type']) . ")";
                }
                ?>
            </td>
        </tr>
        <?php if ($booking['extra_time_hours'] > 0): ?>
        <tr>
            <td>Extra Time</td>
            <td style="text-align:right;"><?= $booking['extra_time_hours']; ?> Jam</td>
        </tr>
        <?php endif; ?>
        <tr>
            <td>Check-in</td>
            <td style="text-align:right;">
                <?= $booking['checkin_time'] ? formatDateTime($booking['checkin_time']) : 'Not checked in'; ?>
            </td>
        </tr>
        <tr>
            <td>Check-out</td>
            <td style="text-align:right;">
            <?php
                if ($booking['duration_type'] == 'fullday') {
                    // Jika ada planned_checkout_time, pakai itu
                    if (!empty($booking['planned_checkout_time'])) {
                        echo formatDateTime($booking['planned_checkout_time']);
                    } else if ($booking['checkin_time']) {
                        // fallback, cari tanggal checkout dari tanggal checkin, tapi jam 12:00:00
                        $checkin = new DateTime($booking['checkin_time']);
                        $checkout = clone $checkin;
                        $hour = (int)$checkin->format('H');
                        $minute = (int)$checkin->format('i');
                        if (
                            ($hour < 23) ||
                            ($hour == 23 && $minute < 59) ||
                            ($hour == 0 && $minute <= 1)
                        ) {
                            $checkout->modify('+1 day');
                        }
                        $checkout->setTime(12, 0, 0);
                        echo formatDateTime($checkout->format('Y-m-d H:i:s'));
                    } else {
                        echo '12:00 (TBA)';
                    }
                } else if ($booking['checkin_time'] && $booking['duration_hours']) {
                    $est_checkout = date('Y-m-d H:i:s', strtotime($booking['checkin_time'] . ' + ' . $booking['duration_hours'] . ' hours'));
                    echo formatDateTime($est_checkout);
                } else {
                    echo 'N/A';
                }
            ?>
            </td>
        </tr>
    </table>
    <hr />
    <table>
        <tr>
            <td>Base</td>
            <td style="text-align:right;"><?= formatCurrency($booking['price_amount']); ?></td>
        </tr>
        <?php if ($booking['extra_time_amount'] > 0): ?>
        <tr>
            <td>Extra Time</td>
            <td style="text-align:right;"><?= formatCurrency($booking['extra_time_amount']); ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($booking['refund_amount'] > 0): ?>
        <tr>
            <td>Refund</td>
            <td style="text-align:right;">-<?= formatCurrency($booking['refund_amount']); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td>Total</td>
            <td style="text-align:right;"><strong><?= formatCurrency($total_amount); ?></strong></td>
        </tr>
        <tr>
            <td>Payment (<?= ucfirst($booking['payment_method']); ?>)</td>
            <td style="text-align:right;">
                <?= formatCurrency($booking['price_amount'] + $booking['extra_time_amount']); ?>
            </td>
        </tr>
        <tr>
            <td>Deposit (<?= ucfirst($booking['deposit_type']); ?>)</td>
            <td style="text-align:right;"><?= formatCurrency($booking['deposit_amount']); ?></td>
        </tr>
    </table>
    <center>
        <p>Terima kasih telah menggunakan layanan kami!<br>Thank you for using our services!</p>
        <p>Served by: <?= htmlspecialchars($booking['created_by_name']); ?></p>
    </center>
    <div id="buttons" style="text-align: center;">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>
</div>
<script>
window.onload = function () {
    setTimeout(() => {
        window.print();
        setTimeout(function(){
            window.close();
        }, 2000);
    }, 1500);
};
</script>
</body>
</html>