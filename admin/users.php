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
            case "edit_user":
                $edit_id = intval($_POST["edit_id"]);
                $edit_username = $_POST["edit_username"];
                $edit_password = $_POST["edit_password"];
                // Update username
                $stmt = $pdo->prepare("UPDATE users SET username=? WHERE id=?");
                $stmt->execute([$edit_username, $edit_id]);
                // Update password jika diisi
                if (!empty($edit_password)) {
                    $new_hash = password_hash($edit_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
                    $stmt->execute([$new_hash, $edit_id]);
                }
                $success = "User updated successfully!";
                break;
        }
    }
}

// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY role, full_name");
$users = $stmt->fetchAll();

// Untuk form edit
$editUser = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$edit_id]);
    $editUser = $stmt->fetch();
}

// Helper for date
if (!function_exists('formatDateTime')) {
    function formatDateTime($dt) {
        if (!$dt) return "";
        return date("Y-m-d H:i", strtotime($dt));
    }
}
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
                        <th>Action</th>
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
                        <td>
                            <a href="users.php?edit_id=<?php echo $user["id"]; ?>" class="btn btn-sm btn-primary">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($editUser): ?>
        <div style="background: var(--dark-gray); padding: 2rem; border-radius: 10px; margin:2rem auto; max-width:400px;">
            <h2 style="color: var(--primary-pink); margin-bottom: 1rem;">Edit User</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="edit_id" value="<?php echo $editUser["id"]; ?>">
                <div class="form-group">
                    <label>Username (ID):</label>
                    <input type="text" name="edit_username" class="form-control" value="<?php echo htmlspecialchars($editUser["username"]); ?>" required>
                </div>
                <div class="form-group">
                    <label>Password Baru:</label>
                    <input type="password" name="edit_password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                </div>
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>

    </div>

    <footer class="footer">
        <p>&copy; 2024 KIA SERVICED APARTMENT - Copyright by Riruuu Rabofezu</p>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>