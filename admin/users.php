<?php
require_once "../includes/config.php";

if (!hasPermission(["superuser"])) {
    header("Location: ../index.php");
    exit;
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["action"])) {
        switch ($_POST["action"]) {
            case "add_user":
                $username = $_POST["username"];
                $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
                $full_name = $_POST["full_name"];
                $role = $_POST["role"];
                
                $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $password, $full_name, $role]);
                $success = "User added successfully!";
                break;
        }
    }
}

// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY role, full_name");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - KIA SERVICED APARTMENT</title>
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
        <h1 style="color: var(--primary-pink); margin-bottom: 2rem;">Manage Users</h1>

        <div style="background: var(--dark-gray); padding: 1rem; border-radius: 10px; margin-bottom: 2rem;">
            <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
            <a href="users.php" class="btn btn-danger">Manage Users</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div style="background: var(--dark-gray); padding: 2rem; border-radius: 10px; margin-bottom: 2rem;">
            <h2 style="color: var(--primary-pink); margin-bottom: 1rem;">Add New User</h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Full Name:</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Role:</label>
                        <select name="role" class="form-control" required>
                            <option value="">Select Role</option>
                            <option value="cashier">Cashier</option>
                            <option value="admin">Admin</option>
                            <option value="superuser">Superuser</option>
                        </select>
                    </div>
                </div>
                
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <h2 style="color: var(--primary-pink); margin: 1rem;">All Users</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user["id"]; ?></td>
                        <td><?php echo htmlspecialchars($user["username"]); ?></td>
                        <td><?php echo htmlspecialchars($user["full_name"]); ?></td>
                        <td><?php echo ucfirst($user["role"]); ?></td>
                        <td><?php echo formatDateTime($user["created_at"]); ?></td>
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
