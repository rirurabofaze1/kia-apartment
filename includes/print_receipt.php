<?php
require_once 'config.php';

if (!isLoggedIn()) {
    die('Not authorized');
}

$booking_id = $_GET['booking_id'] ?? 0;

// Get booking details
$stmt = $pdo->prepare("SELECT b.*, r.room_number, r.location, r.room_type, r.wifi_name, r.wifi_password, u.full_name as created_by_name 
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
<html>
<head>
    <title>Receipt - KIA SERVICED APARTMENT</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .receipt { max-width: 400px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin-bottom: 10px; }
        .total { border-top: 2px solid #000; padding-top: 10px; font-weight: bold; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h2>KIA SERVICED APARTMENT</h2>
			<p>Ruko A1 Gedung Pink Apartment Grand Sentraland Wadas, Telukjambe Timur, Karawang, Jawa Barat 41361</p>
            <p>Receipt #<?php echo $booking['id']; ?></p>
            <p><?php echo formatDateTime($booking['created_at']); ?></p>
        </div>
        
        <div class="details">
            <p><strong>Guest:</strong> <?php echo htmlspecialchars($booking['guest_name']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone_number']); ?></p>
            <p><strong>Room:</strong> <?php echo htmlspecialchars($booking['location'] . ' - ' . $booking['room_number']); ?></p>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($booking['room_type']); ?></p>
            <p><strong>WiFi:</strong> <?php echo htmlspecialchars($booking['wifi_name']); ?></p>
            <p><strong>WiFi Password:</strong> <?php echo htmlspecialchars($booking['wifi_password']); ?></p>
            <p><strong>Duration:</strong> <?php echo $booking['duration_hours']; ?> hours (<?php echo ucfirst($booking['duration_type']); ?>)</p>
            <?php if ($booking['extra_time_hours'] > 0): ?>
                <p><strong>Extra Time:</strong> <?php echo $booking['extra_time_hours']; ?> hours</p>
            <?php endif; ?>
            <p><strong>Check-in:</strong> <?php echo $booking['checkin_time'] ? formatDateTime($booking['checkin_time']) : 'Not checked in'; ?></p>
            <p><strong>Check-out:</strong> <?php echo $booking['checkout_time'] ? formatDateTime($booking['checkout_time']) : 'Not checked out'; ?></p>
        </div>
        
        <div class="total">
            <p>Base Amount: <?php echo formatCurrency($booking['price_amount']); ?></p>
            <?php if ($booking['extra_time_amount'] > 0): ?>
                <p>Extra Time: <?php echo formatCurrency($booking['extra_time_amount']); ?></p>
            <?php endif; ?>
            <?php if ($booking['refund_amount'] > 0): ?>
                <p>Refund: -<?php echo formatCurrency($booking['refund_amount']); ?></p>
            <?php endif; ?>
            <p><strong>Total: <?php echo formatCurrency($total_amount); ?></strong></p>
            <p>Payment: <?php echo ucfirst($booking['payment_method']); ?></p>
            <p>Deposit: <?php echo formatCurrency($booking['deposit_amount']); ?> (<?php echo ucfirst($booking['deposit_type']); ?>)</p>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <p>Terima kasih telah menggunakan layanan kami!</p>
            <p>Thank you for using our services!</p>
            <p>Served by: <?php echo htmlspecialchars($booking['created_by_name']); ?></p>
        </div>
        
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()">Print Receipt</button>
            <button onclick="window.close()">Close</button>
        </div>
    </div>
</body>
</html>