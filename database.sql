-- KIA SERVICED APARTMENT Database Structure
CREATE DATABASE IF NOT EXISTS kia_apartment;
USE kia_apartment;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('superuser', 'admin', 'cashier') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Rooms table
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(100) NOT NULL,
    floor_number INT NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    wifi_name VARCHAR(100),
    wifi_password VARCHAR(100),
    status ENUM('ready', 'booked', 'checkin', 'checkout') DEFAULT 'ready',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    guest_name VARCHAR(100) NOT NULL,
    arrival_time DATETIME NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    duration_type ENUM('hourly', 'fullday') NOT NULL,
    duration_hours INT NOT NULL,
    price_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    deposit_type ENUM('cash', 'id_card') NOT NULL,
    deposit_amount DECIMAL(10,2) DEFAULT 0,
    notes TEXT,
    status ENUM('booked', 'checkin', 'checkout', 'cancelled', 'no_show') DEFAULT 'booked',
    checkin_time DATETIME NULL,
    checkout_time DATETIME NULL,
    extra_time_hours INT DEFAULT 0,
    extra_time_amount DECIMAL(10,2) DEFAULT 0,
    refund_amount DECIMAL(10,2) DEFAULT 0,
    refund_method VARCHAR(50) NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Transactions table for financial reports
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    transaction_type ENUM('booking', 'extra_time', 'refund') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    notes TEXT,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Shift reports table
CREATE TABLE shift_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    shift_date DATE NOT NULL,
    total_transactions INT DEFAULT 0,
    total_amount DECIMAL(10,2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default superuser (password: password)
INSERT INTO users (username, password, full_name, role) VALUES 
('superuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super User', 'superuser');

-- Insert sample rooms
INSERT INTO rooms (location, floor_number, room_number, room_type, wifi_name, wifi_password) VALUES 
('Building A', 1, '101', 'Standard', 'KIA_WiFi_101', 'kia123101'),
('Building A', 1, '102', 'Standard', 'KIA_WiFi_102', 'kia123102'),
('Building A', 2, '201', 'Deluxe', 'KIA_WiFi_201', 'kia123201'),
('Building A', 2, '202', 'Deluxe', 'KIA_WiFi_202', 'kia123202'),
('Building B', 1, '103', 'Standard', 'KIA_WiFi_103', 'kia123103'),
('Building B', 2, '203', 'Suite', 'KIA_WiFi_203', 'kia123203');
