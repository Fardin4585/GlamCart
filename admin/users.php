<?php
/**
 * GlamCart - User Management
 * Admin page to manage all users
 */

require_once '../connection.php';

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

// Handle user status toggle
if (isset($_POST['toggle_status'])) {
    $user_id = (int)$_POST['user_id'];
    $new_status = $_POST['new_status'];
    
    $sql = "UPDATE users SET user_status = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "User status updated successfully!";
        log_admin_action("Updated user status", "users", $user_id);
    } else {
        $error_message = "Failed to update user status.";
    }
    $stmt->close();
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Don't allow admin to delete themselves
    if ($user_id == $_SESSION['user_id']) {
        $error_message = "You cannot delete your own account.";
    } else {
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success_message = "User deleted successfully!";
            log_admin_action("Deleted user", "users", $user_id);
        } else {
            $error_message = "Failed to delete user.";
        }
        $stmt->close();
    }
}

// Get users with pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM users";
$result = $conn->query($count_sql);
$total_users = $result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $per_page);

// Get users
$sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - GlamCart Admin</title>
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
        
        .users-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .users-header h1 {
            margin: 0;
        }
        
        .btn-add {
            background: var(--success);
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .badge-success {
            background: var(--success);
            color: white;
        }
        
        .badge-secondary {
            background: var(--gray-medium);
            color: white;
        }
        
        .badge-warning {
            background: var(--warning);
            color: white;
        }
        
        .badge-danger {
            background: var(--danger);
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
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-header">
                <h2><i class="fas fa-magic"></i> GlamCart Admin</h2>
                                 <p>Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['user_f_name']) ?></p>
            </div>
            
            <nav>
                <ul class="admin-nav">
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                    <li><a href="add_product.php"><i class="fas fa-plus"></i> Add Product</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="brands.php"><i class="fas fa-copyright"></i> Brands</a></li>
                    <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <?php if (has_admin_permission('manage_admins')): ?>
                    <li><a href="manage_admins.php"><i class="fas fa-user-shield"></i> Manage Admins</a></li>
                    <?php endif; ?>
                    <li><a href="../index.php"><i class="fas fa-home"></i> Back to Site</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Admin Content -->
        <main class="admin-content">
            <div class="admin-header">
                <div class="users-header">
                    <h1>User Management</h1>
                    <a href="add_user.php" class="btn-add">
                        <i class="fas fa-plus"></i> Add New User
                    </a>
                </div>
            </div>
            
            <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success_message) ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error_message) ?>
            </div>
            <?php endif; ?>
            
            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h3>All Users (<?= $total_users ?> total)</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($users)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <div class="user-avatar">
                                                <?= strtoupper(substr($user['user_f_name'], 0, 1) . substr($user['user_l_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($user['user_f_name'] . ' ' . $user['user_l_name']) ?></strong>
                                                <?php if ($user['user_id'] == $_SESSION['user_id']): ?>
                                                <br><small class="text-muted">(You)</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['user_email']) ?></td>
                                    <td><?= htmlspecialchars($user['user_phone_no'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($user['user_address'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php 
                                        $status = $user['user_status'] ?? 'active';
                                        $status_class = $status === 'active' ? 'success' : 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $status_class ?>">
                                            <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $created_at = $user['created_at'] ?? $user['user_entry_date'] ?? 'N/A';
                                        if ($created_at !== 'N/A') {
                                            echo date('M j, Y', strtotime($created_at));
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            
                                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to change the user status?')">
                                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                <input type="hidden" name="new_status" value="<?= ($status === 'active') ? 'inactive' : 'active' ?>">
                                                <button type="submit" name="toggle_status" class="btn btn-<?= ($status === 'active') ? 'warning' : 'success' ?> btn-sm">
                                                    <i class="fas fa-<?= ($status === 'active') ? 'pause' : 'play' ?>"></i>
                                                    <?= ($status === 'active') ? 'Deactivate' : 'Activate' ?>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Delete
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
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" class="btn btn-sm">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="btn btn-sm">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <div class="text-center">
                        <i class="fas fa-users" style="font-size: 4rem; color: var(--gray-medium); margin-bottom: 1rem;"></i>
                        <h3>No users found</h3>
                        <p>No users have registered yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>
