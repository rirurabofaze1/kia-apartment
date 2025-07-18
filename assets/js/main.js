// KIA SERVICED APARTMENT - Main JavaScript

function initializeApp() {
    // Login modal functionality
    const loginBtn = document.getElementById('loginBtn');
    const loginModal = document.getElementById('loginModal');
    const closeModalBtn = document.querySelector('#loginModal .close');
    
    if (loginBtn && loginModal) {
        loginBtn.onclick = function() {
            loginModal.style.display = "block";
        };
    }
    if (closeModalBtn && loginModal) {
        closeModalBtn.onclick = function() {
            loginModal.style.display = "none";
        };
    }
    window.onclick = function(event) {
        if (event.target == loginModal) {
            loginModal.style.display = "none";
        }
    };

    // Filter functionality
    const roomTypeFilter = document.getElementById('roomTypeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchFilter = document.getElementById('searchFilter');

    if (roomTypeFilter) roomTypeFilter.addEventListener('change', filterRooms);
    if (statusFilter) statusFilter.addEventListener('change', filterRooms);
    if (searchFilter) searchFilter.addEventListener('input', filterRooms); // Use 'input' for instant search

    // Initial filter on page load
    filterRooms();
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
        
        const matchesType = !roomTypeFilter || roomType === roomTypeFilter;
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesSearch = !searchFilter || 
            roomNumber.includes(searchFilter) || 
            location.includes(searchFilter);
        
        if (matchesType && matchesStatus && matchesSearch) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function startCountdownTimers() {
    // Handle all countdown-timer elements, both with and without bookingId
    document.querySelectorAll('.countdown-timer').forEach(function(element) {
        var target = element.getAttribute('data-target');
        var bookingId = element.getAttribute('data-booking-id');
        if (target && bookingId) {
            var targetDate = new Date(target.replace(' ', 'T')).getTime();
            startCountdownTimer(element, targetDate, bookingId);
        } else if (target) {
            // Arrival countdown (no bookingId)
            var targetDate = new Date(target.replace(' ', 'T')).getTime();
            startArrivalCountdown(element, targetDate);
        }
    });
    document.querySelectorAll('.countdown-timer-large').forEach(function(element) {
        const targetTime = element.dataset.target;
        if (targetTime) {
            updateCountdown(element, targetTime);
            setInterval(() => updateCountdown(element, targetTime), 1000);
        }
    });
}

function startCountdownTimer(element, targetTime, bookingId) {
    function updateCountdown() {
        var now = new Date().getTime();
        var distance = targetTime - now;
        if (distance <= 0) {
            element.innerHTML = "EXPIRED";
            if (bookingId && !element.dataset.autocheckoutDone) {
                element.dataset.autocheckoutDone = "1";
                fetch('includes/ajax.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=checkout_room&booking_id=' + encodeURIComponent(bookingId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) location.reload();
                });
            }
            return;
        }
        var hours = Math.floor(distance / 1000 / 60 / 60);
        var minutes = Math.floor((distance / 1000 / 60) % 60);
        var seconds = Math.floor((distance / 1000) % 60);
        element.innerHTML = hours + "h " + minutes + "m " + seconds + "s ";
        setTimeout(updateCountdown, 1000);
    }
    updateCountdown();
}

function startArrivalCountdown(element, targetTime) {
    function updateArrivalCountdown() {
        var now = new Date().getTime();
        var distance = targetTime - now;
        if (distance <= 0) {
            element.innerHTML = "EXPIRED";
            return;
        }
        var hours = Math.floor(distance / 1000 / 60 / 60);
        var minutes = Math.floor((distance / 1000 / 60) % 60);
        var seconds = Math.floor((distance / 1000) % 60);
        element.innerHTML = hours + "h " + minutes + "m " + seconds + "s ";
        setTimeout(updateArrivalCountdown, 1000);
    }
    updateArrivalCountdown();
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
        
        if (element.classList.contains('countdown-timer-large')) {
            if (difference <= 15 * 60 * 1000) {
                element.classList.add('urgent');
            } else {
                element.classList.remove('urgent');
            }
        } else {
            element.parentElement.classList.remove('expired');
        }
    } else {
        element.textContent = 'EXPIRED';
        if (element.classList.contains('countdown-timer-large')) {
            element.classList.add('urgent');
        } else {
            element.parentElement.classList.add('expired');
            const confirmationDiv = element.parentElement.parentElement.querySelector('.confirmation-buttons');
            if (confirmationDiv) {
                confirmationDiv.style.display = 'block';
            }
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
    modal.className = 'modal booking-modal';
    modal.style.display = 'block';
    
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>Book Room</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="bookingForm" autocomplete="off">
                    <!-- Guest Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            Guest Information
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="guest_name">Guest Name:</label>
                                <input type="hidden" id="guest_name" name="guest_name" class="form-control" autocomplete="off" placeholder="Enter guest full name" required>
                            </div>
                            <div class="form-group">
                                <label for="phone_number">Phone Number:</label>
                                <input type="hidden" id="phone_number" name="phone_number" class="form-control" autocomplete="off" placeholder="e.g., +62812345678" required>
                            </div>
                        </div>
                    </div>
                    <!-- Booking Details Section -->
                    <div class="form-section">
                        <div class="section-title">
                            Booking Details
                        </div>
                        <div class="form-group">
                            <label for="arrival_time">Arrival Time:</label>
                            <input type="datetime-local" id="arrival_time" name="arrival_time" class="form-control" autocomplete="off" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="duration_type">Duration Type:</label>
                                <select id="duration_type" name="duration_type" class="form-control" autocomplete="off" required>
                                    <option value="">Select Duration Type</option>
                                    <option value="transit">Transit</option>
                                    <option value="fullday">Full Day</option>
                                </select>
                            </div>
                            <div class="form-group" id="duration_hours_group">
                                <label for="duration_hours">Duration (Hours):</label>
                                <input type="number" id="duration_hours" name="duration_hours" class="form-control" min="1" autocomplete="off" placeholder="e.g., 3" required>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            Payment Information
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="price_amount">Price Amount:</label>
                                <input type="number" id="price_amount" name="price_amount" class="form-control" step="0.01" autocomplete="off" placeholder="0.00" required>
                            </div>
                            <div class="form-group">
                                <label for="payment_method">Payment Method:</label>
                                <select id="payment_method" name="payment_method" class="form-control" autocomplete="off" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="deposit_type">Deposit Type:</label>
                                <select id="deposit_type" name="deposit_type" class="form-control" autocomplete="off" required>
                                    <option value="">Select Deposit Type</option>
                                    <option value="cash">Cash</option>
                                    <option value="id_card">ID Card</option>
                                    <option value="no_deposit">No Deposit</option>
                                </select>
                            </div>
                            <div class="form-group" id="deposit_amount_group">
                                <label for="deposit_amount">Deposit Amount:</label>
                                <input type="number" id="deposit_amount" name="deposit_amount" class="form-control" step="0.01" autocomplete="off" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>
                    <!-- Additional Notes Section -->
                    <div class="form-section">
                        <div class="section-title">
                            Additional Notes
                        </div>
                        <div class="form-group">
                            <label for="notes">Notes (Optional):</label>
                            <textarea id="notes" name="notes" class="form-control" rows="3" autocomplete="off" placeholder="Any special requests or additional information..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal(this)">Cancel</button>
                <button type="submit" form="bookingForm" class="btn btn-primary">
					Book Room
                </button>
            </div>
        </div>
    `;
    
    // Handle duration type change
    modal.querySelector('#duration_type').addEventListener('change', function() {
        const durationHoursGroup = modal.querySelector('#duration_hours_group');
        const durationHoursInput = modal.querySelector('#duration_hours');
        const arrivalTimeInput = modal.querySelector('#arrival_time');

        if (this.value === 'fullday') {
            durationHoursGroup.style.display = 'none';
            durationHoursInput.removeAttribute('required');

            if (arrivalTimeInput.value) {
                const arrivalDate = new Date(arrivalTimeInput.value);
                const hour = arrivalDate.getHours();
                const minute = arrivalDate.getMinutes();

                let checkoutDate = new Date(arrivalDate);
                // Kunci: checkout selalu jam 12:00:00, tidak jam 12:menit checkin
                if (
                    (hour < 23) ||
                    (hour === 23 && minute < 59) ||
                    (hour === 0 && minute <= 1)
                ) {
                    checkoutDate.setDate(checkoutDate.getDate() + 1);
                    checkoutDate.setHours(12, 0, 0, 0);
                } else {
                    checkoutDate.setHours(12, 0, 0, 0);
                }
                const durationMs = checkoutDate.getTime() - arrivalDate.getTime();
                const durationHours = Math.ceil(durationMs / (1000 * 60 * 60));
                durationHoursInput.value = durationHours;
            } else {
                durationHoursInput.value = 12;
            }
        } else {
            durationHoursGroup.style.display = 'block';
            durationHoursInput.setAttribute('required', 'required');
            durationHoursInput.value = '';
        }
    });

    // Handle arrival time change for fullday calculation
    modal.querySelector('#arrival_time').addEventListener('change', function() {
        const durationTypeSelect = modal.querySelector('#duration_type');
        const durationHoursInput = modal.querySelector('#duration_hours');
        if (durationTypeSelect.value === 'fullday' && this.value) {
            const arrivalDate = new Date(this.value);
            const hour = arrivalDate.getHours();
            const minute = arrivalDate.getMinutes();

            let checkoutDate = new Date(arrivalDate);
            if (
                (hour < 23) ||
                (hour === 23 && minute < 59) ||
                (hour === 0 && minute <= 1)
            ) {
                checkoutDate.setDate(checkoutDate.getDate() + 1);
                checkoutDate.setHours(12, 0, 0, 0);
            } else {
                checkoutDate.setHours(12, 0, 0, 0);
            }
            const durationMs = checkoutDate.getTime() - arrivalDate.getTime();
            const durationHours = Math.ceil(durationMs / (1000 * 60 * 60));
            durationHoursInput.value = durationHours;
        }
    });
    
    // Handle deposit type change
    modal.querySelector('#deposit_type').addEventListener('change', function() {
        const depositAmountGroup = modal.querySelector('#deposit_amount_group');
        const depositAmountInput = modal.querySelector('#deposit_amount');
        
        if (this.value === 'no_deposit' || this.value === 'id_card') {
            depositAmountGroup.style.display = 'none';
            depositAmountInput.removeAttribute('required');
            depositAmountInput.value = '0';
        } else {
            depositAmountGroup.style.display = 'block';
            depositAmountInput.setAttribute('required', 'required');
            depositAmountInput.value = '';
        }
    });

    // Handle form submission
    modal.querySelector('#bookingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            room_id: roomId
        };
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        performAction('book_room', data, function(response) {
            if (response.success) {
                alert('Room booked successfully!');
                modal.remove();
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    });
    
    // Handle close button
    modal.querySelector('.close').addEventListener('click', function() {
        closeModal(modal.querySelector('.close'));
    });
    
    // Set default arrival time to current time
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    modal.querySelector('#arrival_time').value = now.toISOString().slice(0, 16);
    
    // Initialize deposit amount group visibility
    const depositAmountGroup = modal.querySelector('#deposit_amount_group');
    depositAmountGroup.style.display = 'none';
    
    // Clear form data when modal is opened (prevent history)
    setTimeout(() => {
        const form = modal.querySelector('#bookingForm');
        if (form) {
            form.reset();
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.value = '';
                input.removeAttribute('value');
            });
            modal.querySelector('#arrival_time').value = now.toISOString().slice(0, 16);
        }
    }, 100);
    
    return modal;
}

function closeModal(element) {
    const modal = element.closest('.modal');
    const form = modal.querySelector('form');
    if (form) {
        form.reset();
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.value = '';
            input.removeAttribute('value');
        });
    }
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