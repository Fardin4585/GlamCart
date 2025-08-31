<?php
/**
 * GlamCart - Admin Management
 * Super admin page to manage admin users
 */

require_once '../connection.php';
session_start();

// Check if user is logged in and is admin
if (!is_logged_in()) {
    header('Location: ../admin_login.php');
    exit;
}

// Check if user is admin
if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}

// Check if user has permission to manage admins (super_admin only)
if (!has_admin_permission('manage_admins')) {
    header('Location: index.php');
    exit;
}

$errors = [];
$success_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_admin'])) {
        $user_id = (int)$_POST['user_id'];
        $admin_role = $_POST['admin_role'];
        $permissions = $_POST['permissions'] ?? '';
        
        // Validate input
        if ($user_id <= 0) {
            $errors[] = "Please select a valid user";
        }
        
        if (!in_array($admin_role, ['super_admin', 'admin', 'moderator'])) {
            $errors[] = "Invalid admin role";
        }
        
        // Check if user already exists in admin table
        $check_sql = "SELECT admin_id FROM admin_users WHERE user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "This user is already an admin";
        }
        $check_stmt->close();
        
        if (empty($errors)) {
            $sql = "INSERT INTO admin_users (user_id, admin_role, permissions) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $user_id, $admin_role, $permissions);
            
            if ($stmt->execute()) {
                $success_message = "Admin user added successfully!";
                log_admin_action("Added admin user", "admin_users", $conn->insert_id);
            } else {
                $errors[] = "Failed to add admin user: " . $conn->error;
            }
            $stmt->close();
        }
    }
    
    if (isset($_POST['update_admin'])) {
        $admin_id = (int)$_POST['admin_id'];
        $admin_role = $_POST['admin_role'];
        $permissions = $_POST['permissions'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Don't allow deactivating yourself
        if ($admin_id == $_SESSION['admin_id'] && !$is_active) {
            $errors[] = "You cannot deactivate your own admin account";
        }
        
        if (empty($errors)) {
            $sql = "UPDATE admin_users SET admin_role = ?, permissions = ?, is_active = ? WHERE admin_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $admin_role, $permissions, $is_active, $admin_id);
            
            if ($stmt->execute()) {
                $success_message = "Admin user updated successfully!";
                log_admin_action("Updated admin user", "admin_users", $admin_id);
            } else {
                $errors[] = "Failed to update admin user: " . $conn->error;
            }
            $stmt->close();
        }
    }
    
    if (isset($_POST['delete_admin'])) {
        $admin_id = (int)$_POST['admin_id'];
        
        // Don't allow deleting yourself
        if ($admin_id == $_SESSION['admin_id']) {
            $errors[] = "You cannot delete your own admin account";
        } else {
            $sql = "DELETE FROM admin_users WHERE admin_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $admin_id);
            
            if ($stmt->execute()) {
                $success_message = "Admin user removed successfully!";
                log_admin_action("Removed admin user", "admin_users", $admin_id);
            } else {
                $errors[] = "Failed to remove admin user: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Get all admin users
$admins = [];
$sql = "SELECT au.*, u.user_f_name, u.user_l_name, u.user_email 
        FROM admin_users au 
        JOIN users u ON au.user_id = u.user_id 
        ORDER BY au.created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }
}

// Get all users for dropdown (excluding existing admins)
$users = [];
$sql = "SELECT u.user_id, u.user_f_name, u.user_l_name, u.user_email 
        FROM users u 
        LEFT JOIN admin_users au ON u.user_id = au.user_id 
        WHERE au.user_id IS NULL 
        ORDER BY u.user_f_name, u.user_l_name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management - GlamCart Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 250px;
            background: var(--primary);
            color: white;
            padding: 1rem;
        }
        
        .admin-content {
            flex: 1;
            padding: 2rem;
            background: #f8f9fa;
        }
        
        .admin-header {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 2rem;
        }
        
        .admin-nav {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
        }
        
        .admin-nav li {
            margin-bottom: 0.5rem;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 0.75rem 1rem;
            display: block;
            border-radius: 4px;
            transition: background 0.3s;
        }
        
        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255,255,255,0.1);
        }
        
                 .admin-nav i {
             margin-right: 0.5rem;
             width: 20px;
         }
         
         .admin-header p, .admin-header small {
             color: rgba(255, 255, 255, 0.9);
         }
        
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .badge-super_admin {
            background: #dc3545;
            color: white;
        }
        
        .badge-admin {
            background: var(--primary);
            color: white;
        }
        
        .badge-moderator {
            background: var(--warning);
            color: white;
        }
        
        .badge-inactive {
            background: var(--gray-medium);
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-header">
                <h2><i class="fas fa-magic"></i> GlamCart Admin</h2>
                <p>Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?></p>
                <small>Role: <?= ucfirst(str_replace('_', ' ', $_SESSION['admin_role'])) ?></small>
            </div>
            
            <nav>
                <ul class="admin-nav">
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                    <li><a href="add_product.php"><i class="fas fa-plus"></i> Add Product</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="brands.php"><i class="fas fa-copyright"></i> Brands</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="manage_admins.php" class="active"><i class="fas fa-user-shield"></i> Manage Admins</a></li>
                    <li><a href="../index.php"><i class="fas fa-home"></i> Back to Site</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Admin Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Admin Management</h1>
                <p>Manage admin users and their permissions</p>
            </div>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success_message) ?>
            </div>
            <?php endif; ?>
            
            <div class="form-grid">
                <!-- Add New Admin -->
                <div class="form-section">
                    <h3><i class="fas fa-plus"></i> Add New Admin</h3>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="user_id" class="form-label">Select User *</label>
                            <select id="user_id" name="user_id" class="form-select" required>
                                <option value="">Choose a user...</option>
                                <?php foreach ($users as $user): ?>
                                <option value="<?= $user['user_id'] ?>">
                                    <?= htmlspecialchars($user['user_f_name'] . ' ' . $user['user_l_name']) ?> 
                                    (<?= htmlspecialchars($user['user_email']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_role" class="form-label">Admin Role *</label>
                            <select id="admin_role" name="admin_role" class="form-select" required>
                                <option value="moderator">Moderator</option>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="permissions" class="form-label">Custom Permissions</label>
                            <textarea id="permissions" name="permissions" class="form-control" rows="3" 
                                      placeholder="Enter custom permissions (optional)"></textarea>
                        </div>
                        
                        <button type="submit" name="add_admin" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Admin
                        </button>
                    </form>
                </div>
                
                <!-- Current Admins -->
                <div class="form-section">
                    <h3><i class="fas fa-users"></i> Current Admins</h3>
                    
                    <?php if (!empty($admins)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($admin['user_f_name'] . ' ' . $admin['user_l_name']) ?></strong>
                                        <?php if ($admin['admin_id'] == $_SESSION['admin_id']): ?>
                                        <br><small class="text-muted">(You)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($admin['user_email']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $admin['admin_role'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $admin['admin_role'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $admin['is_active'] ? 'success' : 'inactive' ?>">
                                            <?= $admin['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-primary btn-sm" onclick="editAdmin(<?= $admin['admin_id'] ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            
                                            <?php if ($admin['admin_id'] != $_SESSION['admin_id']): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this admin?')">
                                                <input type="hidden" name="admin_id" value="<?= $admin['admin_id'] ?>">
                                                <button type="submit" name="delete_admin" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Remove
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p>No admin users found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Edit Admin Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit Admin User</h3>
            <form method="POST" id="editForm">
                <input type="hidden" name="admin_id" id="edit_admin_id">
                
                <div class="form-group">
                    <label for="edit_admin_role" class="form-label">Admin Role</label>
                    <select id="edit_admin_role" name="admin_role" class="form-select" required>
                        <option value="moderator">Moderator</option>
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_permissions" class="form-label">Custom Permissions</label>
                    <textarea id="edit_permissions" name="permissions" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1"> Active
                    </label>
                </div>
                
                <button type="submit" name="update_admin" class="btn btn-primary">Update Admin</button>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script>
        // Modal functionality
        const modal = document.getElementById('editModal');
        const span = document.getElementsByClassName('close')[0];
        
        span.onclick = function() {
            modal.style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        function editAdmin(adminId) {
            // You would typically fetch admin data via AJAX here
            // For now, we'll just show the modal
            document.getElementById('edit_admin_id').value = adminId;
            modal.style.display = 'block';
        }
    </script>
    
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 2rem;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
    </style>
</body>
</html>
