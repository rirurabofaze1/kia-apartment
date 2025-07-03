<?php
// KIA SERVICED APARTMENT - Installation Script
// This script helps verify the installation and setup

echo "<h1>KIA SERVICED APARTMENT - Installation Checker</h1>";

// Check PHP version
echo "<h2>System Requirements</h2>";
echo "<p>PHP Version: " . phpversion() . " ";
if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo "<span style='color: green;'>✓ OK</span>";
} else {
    echo "<span style='color: red;'>✗ Requires PHP 7.4+</span>";
}
echo "</p>";

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'session'];
echo "<h3>Required PHP Extensions:</h3>";
foreach ($required_extensions as $ext) {
    echo "<p>$ext: ";
    if (extension_loaded($ext)) {
        echo "<span style='color: green;'>✓ Loaded</span>";
    } else {
        echo "<span style='color: red;'>✗ Missing</span>";
    }
    echo "</p>";
}

// Check file permissions
echo "<h2>File Permissions</h2>";
$writable_dirs = ['exports'];
foreach ($writable_dirs as $dir) {
    echo "<p>$dir/: ";
    if (is_writable($dir)) {
        echo "<span style='color: green;'>✓ Writable</span>";
    } else {
        echo "<span style='color: red;'>✗ Not writable</span>";
    }
    echo "</p>";
}

// Test database connection
echo "<h2>Database Connection</h2>";
try {
    require_once 'includes/config.php';
    echo "<p>Database: <span style='color: green;'>✓ Connected successfully</span></p>";
    
    // Check if tables exist
    $tables = ['users', 'rooms', 'bookings', 'transactions', 'shift_reports'];
    echo "<h3>Database Tables:</h3>";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p>$table: <span style='color: green;'>✓ Exists</span></p>";
        } else {
            echo "<p>$table: <span style='color: red;'>✗ Missing</span></p>";
        }
    }
    
    // Check default user
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE username = 'superuser'");
    $result = $stmt->fetch();
    if ($result['count'] > 0) {
        echo "<p>Default superuser: <span style='color: green;'>✓ Exists</span></p>";
    } else {
        echo "<p>Default superuser: <span style='color: red;'>✗ Missing</span></p>";
    }
    
} catch (Exception $e) {
    echo "<p>Database: <span style='color: red;'>✗ Connection failed: " . $e->getMessage() . "</span></p>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>If all checks pass, you can access the system at <a href='index.php'>index.php</a></li>";
echo "<li>Default login: username = 'superuser', password = 'password'</li>";
echo "<li>Change the default password immediately after first login</li>";
echo "<li>Add your rooms and start using the system</li>";
echo "</ol>";

echo "<h2>Support</h2>";
echo "<p>If you encounter any issues, please check the README.md file for troubleshooting steps.</p>";
?>
