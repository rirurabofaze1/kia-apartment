<?php
require_once "../includes/config.php";

if (!hasPermission(["admin", "superuser"])) {
    header("Location: ../index.php");
    exit;
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["action"])) {
        switch ($_POST["action"]) {
            case "add_room":
                $stmt = $pdo->prepare("INSERT INTO rooms (location, floor_number, room_number, room_type, wifi_name, wifi_password) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST["location"],
                    $_POST["floor_number"],
                    $_POST["room_number"],
                    $_POST["room_type"],
                    $_POST["wifi_name"],
                    $_POST["wifi_password"]
                ]);
                $success = "Room added successfully!";
                break;
                
            case "edit_room":
                $stmt = $pdo->prepare("UPDATE rooms SET location = ?, floor_number = ?, room_number = ?, room_type = ?, wifi_name = ?, wifi_password = ? WHERE id = ?");
                $stmt->execute([
                    $_POST["location"],
                    $_POST["floor_number"],
                    $_POST["room_number"],
                    $_POST["room_type"],
                    $_POST["wifi_name"],
                    $_POST["wifi_password"],
                    $_POST["room_id"]
                ]);
                $success = "Room updated successfully!";
                break;
                
            case "delete_room":
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE room_id = ? AND status IN (\"booked\", \"checkin\")");
                $stmt->execute([$_POST["room_id"]]);
                if ($stmt->fetch()["count"] > 0) {
                    $error = "Cannot delete room with active bookings!";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
                    $stmt->execute([$_POST["room_id"]]);
                    $success = "Room deleted successfully!";
                }
                break;
        }
    }
}

// Get all rooms
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY location, floor_number, room_number");
$rooms = $stmt->fetchAll();

// Get room for editing
$edit_room = null;
if (isset($_GET["edit"])) {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$_GET["edit"]]);
    $edit_room = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - KIA SERVICED APARTMENT</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
        <h1 style="color: var(--primary-pink); margin-bottom: 2rem;">Manage Rooms</h1>

        <div style="background: var(--dark-gray); padding: 1rem; border-radius: 10px; margin-bottom: 2rem;">
            <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
            <a href="rooms.php" class="btn btn-info">Manage Rooms</a>
            <a href="bookings.php" class="btn btn-info">All Bookings</a>
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
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div style="background: var(--dark-gray); padding: 2rem; border-radius: 10px; margin-bottom: 2rem;">
            <h2 style="color: var(--primary-pink); margin-bottom: 1rem;">
                <?php echo $edit_room ? "Edit Room" : "Add New Room"; ?>
            </h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $edit_room ? "edit_room" : "add_room"; ?>">
                <?php if ($edit_room): ?>
                    <input type="hidden" name="room_id" value="<?php echo $edit_room["id"]; ?>">
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label>Location:</label>
                        <input type="text" name="location" class="form-control" 
                               value="<?php echo $edit_room ? htmlspecialchars($edit_room["location"]) : ""; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Floor Number:</label>
                        <input type="number" name="floor_number" class="form-control" min="1"
                               value="<?php echo $edit_room ? $edit_room["floor_number"] : ""; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Room Number:</label>
                        <input type="text" name="room_number" class="form-control"
                               value="<?php echo $edit_room ? htmlspecialchars($edit_room["room_number"]) : ""; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Room Type:</label>
                        <select name="room_type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="Standard" <?php echo ($edit_room && $edit_room["room_type"] == "Standard") ? "selected" : ""; ?>>Standard</option>
                            <option value="Deluxe" <?php echo ($edit_room && $edit_room["room_type"] == "Deluxe") ? "selected" : ""; ?>>Deluxe</option>
                            <option value="Suite" <?php echo ($edit_room && $edit_room["room_type"] == "Suite") ? "selected" : ""; ?>>Suite</option>
                            <option value="VIP" <?php echo ($edit_room && $edit_room["room_type"] == "VIP") ? "selected" : ""; ?>>VIP</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>WiFi Name:</label>
                        <input type="text" name="wifi_name" class="form-control"
                               value="<?php echo $edit_room ? htmlspecialchars($edit_room["wifi_name"]) : ""; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>WiFi Password:</label>
                        <input type="text" name="wifi_password" class="form-control"
                               value="<?php echo $edit_room ? htmlspecialchars($edit_room["wifi_password"]) : ""; ?>" required>
                    </div>
                </div>
                
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_room ? "Update Room" : "Add Room"; ?>
                    </button>
                    <?php if ($edit_room): ?>
                        <a href="rooms.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-container">
            <h2 style="color: var(--primary-pink); margin: 1rem;">All Rooms</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Location</th>
                        <th>Floor</th>
                        <th>Room Number</th>
                        <th>Type</th>
                        <th>WiFi Name</th>
                        <th>WiFi Password</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                    <tr>
                        <td><?php echo $room["id"]; ?></td>
                        <td><?php echo htmlspecialchars($room["location"]); ?></td>
                        <td><?php echo $room["floor_number"]; ?></td>
                        <td><?php echo htmlspecialchars($room["room_number"]); ?></td>
                        <td><?php echo htmlspecialchars($room["room_type"]); ?></td>
                        <td><?php echo htmlspecialchars($room["wifi_name"]); ?></td>
                        <td><?php echo htmlspecialchars($room["wifi_password"]); ?></td>
                        <td>
                            <a href="rooms.php?edit=<?php echo $room["id"]; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm(\"Are you sure you want to delete this room?\");">
                                <input type="hidden" name="action" value="delete_room">
                                <input type="hidden" name="room_id" value="<?php echo $room["id"]; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 KIA SERVICED APARTMENT - Copyright by Riruuu Rabofezu</p>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>
