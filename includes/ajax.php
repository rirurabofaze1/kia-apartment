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
    $guest_name = $_POST['guest_name'] ?? '';
    $arrival_time = $_POST['arrival_time'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $duration_type = $_POST['duration_type'] ?? '';
    $duration_hours = $_POST['duration_hours'] ?? 0;
    $price_amount = $_POST['price_amount'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? '';
    $deposit_type = $_POST['deposit_type'] ?? '';
    $deposit_amount = $_POST['deposit_amount'] ?? 0;
    $notes = $_POST['notes'] ?? '';
    
    // Validate required fields
    if (empty($guest_name) || empty($arrival_time) || empty($phone_number) || 
        empty($duration_type) || empty($duration_hours) || empty($price_amount) || 
        empty($payment_method) || empty($deposit_type)) {
        return ['success' => false, 'message' => 'All required fields must be filled'];
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
                          deposit_amount, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([$room_id, $guest_name, $arrival_time, $phone_number, $duration_type, 
                   $duration_hours, $price_amount, $payment_method, $deposit_type, 
                   $deposit_amount, $notes, $_SESSION['user_id']]);
    
    $booking_id = $pdo->lastInsertId();
    
    // Create transaction record
    $stmt = $pdo->prepare("INSERT INTO transactions (booking_id, transaction_type, amount, 
                          payment_method, created_by) VALUES (?, 'booking', ?, ?, ?)");
    $stmt->execute([$booking_id, $price_amount, $payment_method, $_SESSION['user_id']]);
    
    return ['success' => true, 'message' => 'Room booked successfully'];
}

function checkinRoom() {
    global $pdo;
    
    $booking_id = $_POST['booking_id'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'checkin', checkin_time = NOW() WHERE id = ?");
    $stmt->execute([$booking_id]);
    
    if ($stmt->rowCount() > 0) {
        return ['success' => true, 'message' => 'Check-in successful'];
    } else {
        return ['success' => false, 'message' => 'Booking not found'];
    }
}

function checkoutRoom() {
    global $pdo;
    
    $booking_id = $_POST['booking_id'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'checkout', checkout_time = NOW() WHERE id = ?");
    $stmt->execute([$booking_id]);
    
    if ($stmt->rowCount() > 0) {
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
    
    // Update any checkout bookings for this room to completed
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'completed' WHERE room_id = ? AND status = 'checkout'");
    $stmt->execute([$room_id]);
    
    return ['success' => true, 'message' => 'Room set to ready status'];
}
?>
