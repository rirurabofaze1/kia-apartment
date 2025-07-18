<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'book_room':
            $result = bookRoom();
            break;
        case 'checkin_room':
            $result = checkinRoom();
            break;
        case 'checkout_room':
            $result = checkoutRoom();
            break;
        case 'cancel_booking':
            $result = cancelBooking();
            break;
        case 'mark_no_show':
            $result = markNoShow();
            break;
        case 'add_extra_time':
            $result = addExtraTime();
            break;
        case 'process_refund':
            $result = processRefund();
            break;
        case 'set_room_ready':
            $result = setRoomReady();
            break;
        default:
            $result = ['success' => false, 'message' => 'Invalid action'];
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function bookRoom() {
    global $pdo;

    $room_id = $_POST['room_id'] ?? 0;
    $guest_name = "Default Guest";
    $arrival_time = $_POST['arrival_time'] ?? '';
    $phone_number = "6666666";
    $duration_type = $_POST['duration_type'] ?? '';
    $duration_hours = $_POST['duration_hours'] ?? 0;
    $price_amount = $_POST['price_amount'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? '';
    $deposit_type = $_POST['deposit_type'] ?? '';
    $deposit_amount = $_POST['deposit_amount'] ?? 0;
    $notes = $_POST['notes'] ?? '';

    // Default, checkout_time null
    $planned_checkout_time = null;

    if ($duration_type === 'fullday') {
        if (!empty($arrival_time)) {
            $arrival = new DateTime($arrival_time);
            $hour = (int)$arrival->format('H');
            $minute = (int)$arrival->format('i');

            $checkout = clone $arrival;
            if (
                ($hour < 23) ||
                ($hour == 23 && $minute < 59) ||
                ($hour == 0 && $minute <= 1)
            ) {
                $checkout->modify('+1 day');
            }
            $checkout->setTime(12, 0, 0); // Selalu 12:00:00

            $planned_checkout_time = $checkout->format('Y-m-d H:i:s');
            $interval = $arrival->diff($checkout);
            $duration_hours = ($interval->days * 24) + $interval->h + ($interval->i > 0 ? 1 : 0);
			
            // Jika ingin simpan checkout_time (opsional):
            // $checkout_time = $checkout->format('Y-m-d H:i:s');
        }
    }
    
    // Validate required fields
    if (empty($guest_name) || empty($arrival_time) || empty($phone_number) || 
        empty($duration_type) || empty($price_amount) || empty($payment_method) || 
        empty($deposit_type)) {
        return ['success' => false, 'message' => 'All required fields must be filled'];
    }
    
    // Validate duration hours for transit bookings
    if ($duration_type === 'transit' && (empty($duration_hours) || $duration_hours <= 0)) {
        return ['success' => false, 'message' => 'Duration hours is required for transit bookings'];
    }
    
    // Validate deposit amount for cash deposits
    if ($deposit_type === 'cash' && (empty($deposit_amount) || $deposit_amount <= 0)) {
        return ['success' => false, 'message' => 'Deposit amount is required for cash deposits'];
    }
    
    // Set deposit amount to 0 for no_deposit and id_card types
    if ($deposit_type === 'no_deposit' || $deposit_type === 'id_card') {
        $deposit_amount = 0;
    }
    
    // Check for existing active booking
    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE room_id = ? AND status IN ('booked', 'checkin')");
    $stmt->execute([$room_id]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Room is already booked'];
    }
    
    // Create booking
    $stmt = $pdo->prepare("INSERT INTO bookings (room_id, guest_name, arrival_time, phone_number, 
                          duration_type, duration_hours, price_amount, payment_method, deposit_type, 
                          deposit_amount, notes, created_by, planned_checkout_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([$room_id, $guest_name, $arrival_time, $phone_number, $duration_type, 
                   $duration_hours, $price_amount, $payment_method, $deposit_type, 
                   $deposit_amount, $notes, $_SESSION['user_id'], $planned_checkout_time]);
    
    $booking_id = $pdo->lastInsertId();
    
    // Create transaction record
    $stmt = $pdo->prepare("INSERT INTO transactions (booking_id, transaction_type, amount, 
                          payment_method, created_by) VALUES (?, 'booking', ?, ?, ?)");
    $stmt->execute([$booking_id, $price_amount, $payment_method, $_SESSION['user_id']]);
    
    // Update room status to 'booked'
    $stmt = $pdo->prepare("UPDATE rooms SET status = 'booked' WHERE id = ?");
    $stmt->execute([$room_id]);
    
    return ['success' => true, 'message' => 'Room booked successfully'];
}

function checkinRoom() {
    global $pdo;
    
    $booking_id = $_POST['booking_id'] ?? 0;
    
    // Get room_id from booking
    $stmt = $pdo->prepare("SELECT room_id FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        return ['success' => false, 'message' => 'Booking not found'];
    }
    
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'checkin', checkin_time = NOW() WHERE id = ?");
    $stmt->execute([$booking_id]);
    
    if ($stmt->rowCount() > 0) {
        // Update room status to 'checkin'
        $stmt = $pdo->prepare("UPDATE rooms SET status = 'checkin' WHERE id = ?");
        $stmt->execute([$booking['room_id']]);
        
        return ['success' => true, 'message' => 'Check-in successful'];
    } else {
        return ['success' => false, 'message' => 'Booking not found'];
    }
}

function checkoutRoom() {
    global $pdo;
    
    $booking_id = $_POST['booking_id'] ?? 0;
    
    // Get room_id from booking
    $stmt = $pdo->prepare("SELECT room_id FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        return ['success' => false, 'message' => 'Booking not found'];
    }
    
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'checkout', checkout_time = NOW() WHERE id = ?");
    $stmt->execute([$booking_id]);
    
    if ($stmt->rowCount() > 0) {
        // Update room status to 'checkout'
        $stmt = $pdo->prepare("UPDATE rooms SET status = 'checkout' WHERE id = ?");
        $stmt->execute([$booking['room_id']]);
        
        return ['success' => true, 'message' => 'Check-out successful'];
    } else {
        return ['success' => false, 'message' => 'Booking not found'];
    }
}

function cancelBooking() {
    global $pdo;
    
    $booking_id = $_POST['booking_id'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$booking_id]);
    
    if ($stmt->rowCount() > 0) {
        return ['success' => true, 'message' => 'Booking cancelled successfully'];
    } else {
        return ['success' => false, 'message' => 'Booking not found'];
    }
}

function markNoShow() {
    global $pdo;
    
    $booking_id = $_POST['booking_id'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'no_show' WHERE id = ?");
    $stmt->execute([$booking_id]);
    
    if ($stmt->rowCount() > 0) {
        return ['success' => true, 'message' => 'Booking marked as no-show'];
    } else {
        return ['success' => false, 'message' => 'Booking not found'];
    }
}

function addExtraTime() {
    global $pdo;
    
    $booking_id = $_POST['booking_id'] ?? 0;
    $extra_hours = $_POST['extra_hours'] ?? 0;
    $extra_amount = $_POST['extra_amount'] ?? 0;
    
    if ($extra_hours <= 0 || $extra_amount <= 0) {
        return ['success' => false, 'message' => 'Invalid extra time or amount'];
    }
    
    $stmt = $pdo->prepare("UPDATE bookings SET extra_time_hours = extra_time_hours + ?, 
                          extra_time_amount = extra_time_amount + ? WHERE id = ?");
    $stmt->execute([$extra_hours, $extra_amount, $booking_id]);
    
    if ($stmt->rowCount() > 0) {
        // Create transaction record
        $stmt = $pdo->prepare("INSERT INTO transactions (booking_id, transaction_type, amount, 
                              payment_method, created_by) VALUES (?, 'extra_time', ?, 'cash', ?)");
        $stmt->execute([$booking_id, $extra_amount, $_SESSION['user_id']]);
        
        return ['success' => true, 'message' => 'Extra time added successfully'];
    } else {
        return ['success' => false, 'message' => 'Booking not found'];
    }
}

function processRefund() {
    global $pdo;
    
    if (!hasPermission(['admin', 'superuser'])) {
        return ['success' => false, 'message' => 'Not authorized for refunds'];
    }
    
    $booking_id = $_POST['booking_id'] ?? 0;
    $refund_amount = $_POST['refund_amount'] ?? 0;
    $refund_method = $_POST['refund_method'] ?? '';
    
    if ($refund_amount <= 0 || empty($refund_method)) {
        return ['success' => false, 'message' => 'Invalid refund amount or method'];
    }
    
    $stmt = $pdo->prepare("UPDATE bookings SET refund_amount = ?, refund_method = ? WHERE id = ?");
    $stmt->execute([$refund_amount, $refund_method, $booking_id]);
    
    if ($stmt->rowCount() > 0) {
        // Create transaction record
        $stmt = $pdo->prepare("INSERT INTO transactions (booking_id, transaction_type, amount, 
                              payment_method, created_by) VALUES (?, 'refund', ?, ?, ?)");
        $stmt->execute([$booking_id, -$refund_amount, $refund_method, $_SESSION['user_id']]);
        
        return ['success' => true, 'message' => 'Refund processed successfully'];
    } else {
        return ['success' => false, 'message' => 'Booking not found'];
    }
}

function setRoomReady() {
    global $pdo;
    
    $room_id = $_POST['room_id'] ?? 0;
    
    // Start transaction to ensure data consistency
    $pdo->beginTransaction();
    
    try {
        // Update the room status to 'ready'
        $stmt = $pdo->prepare("UPDATE rooms SET status = 'ready' WHERE id = ?");
        $stmt->execute([$room_id]);
        
        if ($stmt->rowCount() > 0) {
            // Also mark any checkout bookings for this room as completed to clear customer info
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'completed' WHERE room_id = ? AND status = 'checkout'");
            $stmt->execute([$room_id]);
            
            $pdo->commit();
            return ['success' => true, 'message' => 'Room set to ready status'];
        } else {
            $pdo->rollback();
            return ['success' => false, 'message' => 'Room not found'];
        }
    } catch (Exception $e) {
        $pdo->rollback();
        return ['success' => false, 'message' => 'Error setting room ready: ' . $e->getMessage()];
    }
}
?>
