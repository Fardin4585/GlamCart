<?php
/**
 * GlamCart - Admin Contact Messages
 * Manage contact form submissions
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

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    if ($action === 'mark_read') {
        $sql = "UPDATE contact_messages SET status = 'read' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: contact_messages.php');
        exit;
    } elseif ($action === 'mark_replied') {
        $sql = "UPDATE contact_messages SET status = 'replied' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: contact_messages.php');
        exit;
    }
}

// Get specific message if ID is provided
$selected_message = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM contact_messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $selected_message = $result->fetch_assoc();
            // Mark as read when viewed
            if ($selected_message['status'] === 'unread') {
                $update_sql = "UPDATE contact_messages SET status = 'read' WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                if ($update_stmt) {
                    $update_stmt->bind_param("i", $id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }
        }
        $stmt->close();
    }
}

// Get all messages with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$messages = [];
$total_messages = 0;

if ($stmt) {
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM contact_messages";
$count_result = $conn->query($count_sql);
$total_messages = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_messages / $limit);

// Get status counts
$status_counts = [];
$status_sql = "SELECT status, COUNT(*) as count FROM contact_messages GROUP BY status";
$status_result = $conn->query($status_sql);
if ($status_result) {
    while ($row = $status_result->fetch_assoc()) {
        $status_counts[$row['status']] = $row['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin Dashboard</title>
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
        
        .status-filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .status-filter {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            font-weight: 500;
        }
        
        .status-filter.all { background: #6c757d; }
        .status-filter.unread { background: #ffc107; }
        .status-filter.read { background: #17a2b8; }
        .status-filter.replied { background: #28a745; }
        
        .message-detail {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .message-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        
        .message-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .meta-item {
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .meta-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .meta-value {
            color: #212529;
            margin-top: 0.25rem;
        }
        
        .message-content {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 4px;
            border-left: 4px solid var(--primary);
        }
        
        .message-actions {
            margin-top: 1.5rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .pagination a {
            padding: 0.5rem 1rem;
            border: 1px solid #dee2e6;
            text-decoration: none;
            color: #007bff;
            border-radius: 4px;
        }
        
        .pagination a:hover {
            background: #007bff;
            color: white;
        }
        
        .pagination .current {
            background: #007bff;
            color: white;
            border-color: #007bff;
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
                    <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="contact_messages.php" class="active"><i class="fas fa-envelope"></i> Contact Messages</a></li>
                    <?php if (function_exists('has_admin_permission') && has_admin_permission('manage_admins')): ?>
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
                <h1><i class="fas fa-envelope"></i> Contact Messages</h1>
                <p>Manage customer contact form submissions</p>
            </div>
            
            <!-- Status Filters -->
            <div class="status-filters">
                <a href="contact_messages.php" class="status-filter all">
                    All Messages (<?= $total_messages ?>)
                </a>
                <a href="contact_messages.php?status=unread" class="status-filter unread">
                    Unread (<?= $status_counts['unread'] ?? 0 ?>)
                </a>
                <a href="contact_messages.php?status=read" class="status-filter read">
                    Read (<?= $status_counts['read'] ?? 0 ?>)
                </a>
                <a href="contact_messages.php?status=replied" class="status-filter replied">
                    Replied (<?= $status_counts['replied'] ?? 0 ?>)
                </a>
            </div>
            
            <?php if ($selected_message): ?>
            <!-- Message Detail View -->
            <div class="message-detail">
                <div class="message-header">
                    <h2><?= htmlspecialchars($selected_message['subject']) ?></h2>
                    <p>From: <?= htmlspecialchars($selected_message['name']) ?> (<?= htmlspecialchars($selected_message['email']) ?>)</p>
                </div>
                
                <div class="message-meta">
                    <div class="meta-item">
                        <div class="meta-label">Status</div>
                        <div class="meta-value">
                            <span class="badge badge-<?= $selected_message['status'] === 'unread' ? 'warning' : ($selected_message['status'] === 'read' ? 'info' : 'success') ?>">
                                <?= ucfirst($selected_message['status']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Received</div>
                        <div class="meta-value"><?= date('F j, Y \a\t g:i A', strtotime($selected_message['created_at'])) ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Message ID</div>
                        <div class="meta-value">#<?= $selected_message['id'] ?></div>
                    </div>
                </div>
                
                <div class="message-content">
                    <h4>Message:</h4>
                    <p><?= nl2br(htmlspecialchars($selected_message['message'])) ?></p>
                </div>
                
                <div class="message-actions">
                    <a href="mailto:<?= htmlspecialchars($selected_message['email']) ?>?subject=Re: <?= htmlspecialchars($selected_message['subject']) ?>" 
                       class="btn btn-primary">
                        <i class="fas fa-reply"></i> Reply via Email
                    </a>
                    <a href="contact_messages.php?action=mark_replied&id=<?= $selected_message['id'] ?>" 
                       class="btn btn-success">
                        <i class="fas fa-check"></i> Mark as Replied
                    </a>
                    <a href="contact_messages.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <?php else: ?>
            <!-- Messages List -->
            <div class="recent-section">
                <h3><i class="fas fa-list"></i> All Contact Messages</h3>
                <?php if (!empty($messages)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $msg): ?>
                            <tr>
                                <td>#<?= $msg['id'] ?></td>
                                <td><?= htmlspecialchars($msg['name']) ?></td>
                                <td><?= htmlspecialchars($msg['email']) ?></td>
                                <td><?= htmlspecialchars($msg['subject']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $msg['status'] === 'unread' ? 'warning' : ($msg['status'] === 'read' ? 'info' : 'success') ?>">
                                        <?= ucfirst($msg['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($msg['created_at'])) ?></td>
                                <td>
                                    <a href="contact_messages.php?id=<?= $msg['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if ($msg['status'] === 'unread'): ?>
                                    <a href="contact_messages.php?action=mark_read&id=<?= $msg['id'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-check"></i> Mark Read
                                    </a>
                                    <?php endif; ?>
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
                        <a href="?page=<?= $page - 1 ?>">&laquo; Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" <?= $i === $page ? 'class="current"' : '' ?>><?= $i ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <p>No contact messages found.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>
