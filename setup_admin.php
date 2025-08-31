<?php
/**
 * GlamCart - Admin Setup Script
 * This script helps set up the admin system and create the first admin user
 */

require_once 'connection.php';

echo "<h1>GlamCart Admin Setup</h1>";

// Check if admin_users table exists
$table_exists = false;
$result = $conn->query("SHOW TABLES LIKE 'admin_users'");
if ($result && $result->num_rows > 0) {
    $table_exists = true;
    echo "<p>✅ admin_users table already exists</p>";
} else {
    echo "<p>❌ admin_users table does not exist</p>";
}

// Check if admin_logs table exists
$logs_table_exists = false;
$result = $conn->query("SHOW TABLES LIKE 'admin_logs'");
if ($result && $result->num_rows > 0) {
    $logs_table_exists = true;
    echo "<p>✅ admin_logs table already exists</p>";
} else {
    echo "<p>❌ admin_logs table does not exist</p>";
}

// Create tables if they don't exist
if (!$table_exists || !$logs_table_exists) {
    echo "<h2>Creating Admin Tables...</h2>";
    
    // Create admin_users table
    if (!$table_exists) {
        $sql = "CREATE TABLE IF NOT EXISTS `admin_users` (
            `admin_id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `admin_role` enum('super_admin','admin','moderator') NOT NULL DEFAULT 'admin',
            `permissions` text DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`admin_id`),
            UNIQUE KEY `user_id` (`user_id`),
            KEY `admin_role` (`admin_role`),
            KEY `is_active` (`is_active`),
            CONSTRAINT `admin_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        if ($conn->query($sql)) {
            echo "<p>✅ admin_users table created successfully</p>";
        } else {
            echo "<p>❌ Error creating admin_users table: " . $conn->error . "</p>";
        }
    }
    
    // Create admin_logs table
    if (!$logs_table_exists) {
        $sql = "CREATE TABLE IF NOT EXISTS `admin_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `admin_id` int(11) NOT NULL,
            `admin_name` varchar(255) NOT NULL,
            `action` varchar(255) NOT NULL,
            `table_name` varchar(100) DEFAULT NULL,
            `record_id` int(11) DEFAULT NULL,
            `details` text DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`log_id`),
            KEY `admin_id` (`admin_id`),
            KEY `action` (`action`),
            KEY `created_at` (`created_at`),
            CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`admin_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        if ($conn->query($sql)) {
            echo "<p>✅ admin_logs table created successfully</p>";
        } else {
            echo "<p>❌ Error creating admin_logs table: " . $conn->error . "</p>";
        }
    }
}

// Get all users
echo "<h2>Available Users</h2>";
$users = [];
$sql = "SELECT user_id, user_f_name, user_l_name, user_email FROM users ORDER BY user_f_name, user_l_name";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Action</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
        echo "<tr>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['user_f_name'] . ' ' . $row['user_l_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_email']) . "</td>";
        echo "<td>";
        
        // Check if user is already admin
        $check_sql = "SELECT admin_id, admin_role FROM admin_users WHERE user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $row['user_id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $admin_data = $check_result->fetch_assoc();
            echo "<strong>Already Admin (" . ucfirst($admin_data['admin_role']) . ")</strong>";
            echo "<br><a href='?change_role=" . $row['user_id'] . "&current_role=" . $admin_data['admin_role'] . "' style='color: orange;'>Change Role</a> | ";
            echo "<a href='?remove_admin=" . $row['user_id'] . "' style='color: red;' onclick='return confirm(\"Are you sure you want to remove admin access?\")'>Remove Admin</a>";
        } else {
            echo "<a href='?make_admin=" . $row['user_id'] . "&role=super_admin' style='color: red;'>Make Super Admin</a> | ";
            echo "<a href='?make_admin=" . $row['user_id'] . "&role=admin' style='color: blue;'>Make Admin</a> | ";
            echo "<a href='?make_admin=" . $row['user_id'] . "&role=moderator' style='color: green;'>Make Moderator</a>";
        }
        
        $check_stmt->close();
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found in the database.</p>";
}

// Handle making user admin
if (isset($_GET['make_admin']) && isset($_GET['role'])) {
    $user_id = (int)$_GET['make_admin'];
    $role = $_GET['role'];
    
    if (in_array($role, ['super_admin', 'admin', 'moderator'])) {
        // Check if user exists
        $check_sql = "SELECT user_id FROM users WHERE user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Insert admin user
            $sql = "INSERT INTO admin_users (user_id, admin_role) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $user_id, $role);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✅ User successfully made " . ucfirst(str_replace('_', ' ', $role)) . "!</p>";
            } else {
                echo "<p style='color: red;'>❌ Error: " . $conn->error . "</p>";
            }
            $stmt->close();
        } else {
            echo "<p style='color: red;'>❌ User not found!</p>";
        }
        $check_stmt->close();
    } else {
        echo "<p style='color: red;'>❌ Invalid role!</p>";
    }
}

// Handle changing admin role
if (isset($_GET['change_role']) && isset($_GET['current_role'])) {
    $user_id = (int)$_GET['change_role'];
    $current_role = $_GET['current_role'];
    
    echo "<h3>Change Admin Role</h3>";
    echo "<p>Current role: <strong>" . ucfirst(str_replace('_', ' ', $current_role)) . "</strong></p>";
    echo "<p>Select new role:</p>";
    echo "<p>";
    echo "<a href='?update_role=" . $user_id . "&new_role=super_admin' style='color: red; margin-right: 10px;'>Super Admin</a>";
    echo "<a href='?update_role=" . $user_id . "&new_role=admin' style='color: blue; margin-right: 10px;'>Admin</a>";
    echo "<a href='?update_role=" . $user_id . "&new_role=moderator' style='color: green; margin-right: 10px;'>Moderator</a>";
    echo "<a href='setup_admin.php' style='color: gray;'>Cancel</a>";
    echo "</p>";
}

// Handle updating admin role
if (isset($_GET['update_role']) && isset($_GET['new_role'])) {
    $user_id = (int)$_GET['update_role'];
    $new_role = $_GET['new_role'];
    
    if (in_array($new_role, ['super_admin', 'admin', 'moderator'])) {
        $sql = "UPDATE admin_users SET admin_role = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_role, $user_id);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Admin role updated to " . ucfirst(str_replace('_', ' ', $new_role)) . "!</p>";
        } else {
            echo "<p style='color: red;'>❌ Error updating role: " . $conn->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: red;'>❌ Invalid role!</p>";
    }
}

// Handle removing admin access
if (isset($_GET['remove_admin'])) {
    $user_id = (int)$_GET['remove_admin'];
    
    $sql = "DELETE FROM admin_users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Admin access removed successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error removing admin access: " . $conn->error . "</p>";
    }
    $stmt->close();
}

echo "<h2>Current Admin Users</h2>";
$sql = "SELECT au.*, u.user_f_name, u.user_l_name, u.user_email 
        FROM admin_users au 
        JOIN users u ON au.user_id = u.user_id 
        ORDER BY au.created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['user_f_name'] . ' ' . $row['user_l_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_email']) . "</td>";
        echo "<td>" . ucfirst(str_replace('_', ' ', $row['admin_role'])) . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Active' : 'Inactive') . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No admin users found.</p>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Make sure you have at least one Super Admin user (red link above)</li>";
echo "<li>Use the <strong>Admin Login</strong> page: <a href='admin_login.php' style='color: red; font-weight: bold;'>admin_login.php</a></li>";
echo "<li>Only admin users can access the admin login page</li>";
echo "<li>Regular users should use: <a href='login.php'>login.php</a></li>";
echo "<li>Access the admin panel at: <a href='admin/'>admin/</a></li>";
echo "<li>You can manage other admin users from the 'Manage Admins' section</li>";
echo "</ol>";

echo "<h2>Admin System Features</h2>";
echo "<ul>";
echo "<li>✅ <strong>Separate Admin Login:</strong> Only admin users can login to admin_login.php</li>";
echo "<li>✅ <strong>Role Management:</strong> Change admin roles or remove admin access</li>";
echo "<li>✅ <strong>Access Control:</strong> Only admins can access admin pages</li>";
echo "<li>✅ <strong>Secure Logout:</strong> Admins are redirected to admin login after logout</li>";
echo "</ul>";

echo "<p><strong>Security Note:</strong> Delete this file (setup_admin.php) after setting up your admin users!</p>";
?>


