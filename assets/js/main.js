// KIA SERVICED APARTMENT - Main JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the application
    initializeApp();
    
    // Start countdown timers
    startCountdownTimers();
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        if (!document.querySelector('.modal') || document.querySelector('.modal').style.display === 'none') {
            location.reload();
        }
    }, 30000);
});

function initializeApp() {
    // Login modal functionality
    const loginBtn = document.getElementById('loginBtn');
    const loginModal = document.getElementById('loginModal');
    const closeModal = document.querySelector('.close');
    
    if (loginBtn) {
        loginBtn.addEventListener('click', function() {
            loginModal.style.display = 'block';
        });
    }
    
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            loginModal.style.display = 'none';
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === loginModal) {
            loginModal.style.display = 'none';
        }
    });
    
    // Filter functionality
    const filterInputs = document.querySelectorAll('.filter-control');
    filterInputs.forEach(input => {
        input.addEventListener('change', filterRooms);
        input.addEventListener('keyup', filterRooms);
    });
}

function filterRooms() {
    const roomTypeFilter = document.getElementById('roomTypeFilter')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value.toLowerCase() || '';
    const searchFilter = document.getElementById('searchFilter')?.value.toLowerCase() || '';
    
    const roomCards = document.querySelectorAll('.room-card');
    
    roomCards.forEach(card => {
        const roomType = card.dataset.roomType?.toLowerCase() || '';
        const status = card.dataset.status?.toLowerCase() || '';
        const roomNumber = card.dataset.roomNumber?.toLowerCase() || '';
        const location = card.dataset.location?.toLowerCase() || '';
        
        const matchesType = !roomTypeFilter || roomType.includes(roomTypeFilter);
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesSearch = !searchFilter || 
            roomNumber.includes(searchFilter) || 
            location.includes(searchFilter);
        
        if (matchesType && matchesStatus && matchesSearch) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function startCountdownTimers() {
    const countdownElements = document.querySelectorAll('.countdown-timer');
    
    countdownElements.forEach(element => {
        const targetTime = element.dataset.target;
        if (targetTime) {
            updateCountdown(element, targetTime);
            setInterval(() => updateCountdown(element, targetTime), 1000);
        }
    });
}

function updateCountdown(element, targetTime) {
    const now = new Date().getTime();
    const target = new Date(targetTime).getTime();
    const difference = target - now;
    
    if (difference > 0) {
        const hours = Math.floor(difference / (1000 * 60 * 60));
        const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((difference % (1000 * 60)) / 1000);
        
        element.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        element.parentElement.classList.remove('expired');
    } else {
        element.textContent = 'EXPIRED';
        element.parentElement.classList.add('expired');
        
        // Show confirmation buttons for expired bookings
        const confirmationDiv = element.parentElement.parentElement.querySelector('.confirmation-buttons');
        if (confirmationDiv) {
            confirmationDiv.style.display = 'block';
        }
    }
}

// AJAX Functions
function performAction(action, data, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'includes/ajax.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (callback) callback(response);
            } catch (e) {
                console.error('Error parsing response:', e);
            }
        }
    };
    
    const params = new URLSearchParams();
    params.append('action', action);
    for (const key in data) {
        params.append(key, data[key]);
    }
    
    xhr.send(params.toString());
}

// Room Actions
function bookRoom(roomId) {
    const modal = createBookingModal(roomId);
    document.body.appendChild(modal);
    modal.style.display = 'block';
}

function createBookingModal(roomId) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Book Room</h2>
            <form id="bookingForm">
                <div class="form-group">
                    <label>Guest Name:</label>
                    <input type="text" name="guest_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phone Number:</label>
                    <input type="tel" name="phone_number" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Arrival Time:</label>
                    <input type="datetime-local" name="arrival_time" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Duration Type:</label>
                    <select name="duration_type" class="form-control" required>
                        <option value="hourly">Per Jam</option>
                        <option value="fullday">Full Day</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Duration (Hours):</label>
                    <input type="number" name="duration_hours" class="form-control" min="1" required>
                </div>
                <div class="form-group">
                    <label>Price Amount:</label>
                    <input type="number" name="price_amount" class="form-control" min="0" step="1000" required>
                </div>
                <div class="form-group">
                    <label>Payment Method:</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                        <option value="card">Card</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deposit Type:</label>
                    <select name="deposit_type" class="form-control" required>
                        <option value="cash">Cash</option>
                        <option value="id_card">ID Card</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deposit Amount:</label>
                    <input type="number" name="deposit_amount" class="form-control" min="0" step="1000">
                </div>
                <div class="form-group">
                    <label>Notes:</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Book Room</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal(this)">Cancel</button>
            </form>
        </div>
    `;
    
    // Handle form submission
    modal.querySelector('#bookingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('room_id', roomId);
        
        performAction('book_room', Object.fromEntries(formData), function(response) {
            if (response.success) {
                alert('Room booked successfully!');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    });
    
    // Close modal functionality
    modal.querySelector('.close').addEventListener('click', function() {
        closeModal(this);
    });
    
    return modal;
}

function closeModal(element) {
    const modal = element.closest('.modal');
    modal.remove();
}

function checkinRoom(bookingId) {
    if (confirm('Confirm check-in for this booking?')) {
        performAction('checkin_room', {booking_id: bookingId}, function(response) {
            if (response.success) {
                alert('Check-in successful!');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}

function checkoutRoom(bookingId) {
    if (confirm('Confirm check-out for this booking?')) {
        performAction('checkout_room', {booking_id: bookingId}, function(response) {
            if (response.success) {
                alert('Check-out successful!');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}

function cancelBooking(bookingId) {
    if (confirm('Are you sure you want to cancel this booking?')) {
        performAction('cancel_booking', {booking_id: bookingId}, function(response) {
            if (response.success) {
                alert('Booking cancelled successfully!');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}

function confirmArrival(bookingId) {
    checkinRoom(bookingId);
}

function markNoShow(bookingId) {
    if (confirm('Mark this booking as no-show?')) {
        performAction('mark_no_show', {booking_id: bookingId}, function(response) {
            if (response.success) {
                alert('Booking marked as no-show!');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}

function addExtraTime(bookingId) {
    const hours = prompt('Enter additional hours:');
    const amount = prompt('Enter additional amount:');
    
    if (hours && amount) {
        performAction('add_extra_time', {
            booking_id: bookingId,
            extra_hours: hours,
            extra_amount: amount
        }, function(response) {
            if (response.success) {
                alert('Extra time added successfully!');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}

function printReceipt(bookingId) {
    window.open('includes/print_receipt.php?booking_id=' + bookingId, '_blank');
}

function processRefund(bookingId) {
    const amount = prompt('Enter refund amount:');
    const method = prompt('Enter refund method (cash/transfer):');
    
    if (amount && method) {
        performAction('process_refund', {
            booking_id: bookingId,
            refund_amount: amount,
            refund_method: method
        }, function(response) {
            if (response.success) {
                alert('Refund processed successfully!');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}

function setRoomReady(roomId) {
    if (confirm('Set this room to ready status?')) {
        performAction('set_room_ready', {room_id: roomId}, function(response) {
            if (response.success) {
                alert('Room status updated to ready!');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}