<?php
/**
 * Admin Orders Management Page
 * View and manage orders
 */

require_once '../config/db.php';
requireAdmin();

$conn = getDBConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: /college-pro/admin/orders.php?updated=1');
    exit();
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$view_order_id = isset($_GET['view']) ? (int)$_GET['view'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query
$where = "WHERE 1=1";
$params = [];
$types = '';

if (!empty($status)) {
    $where .= " AND o.status = ?";
    $params[] = $status;
    $types .= 's';
}

if (!empty($search)) {
    $where .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR o.id = ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $search_id = is_numeric($search) ? (int)$search : 0;
    $params[] = $search_id;
    $types .= 'sssi';
}

// Get total count
$count_query = "SELECT COUNT(*) as total FROM orders o JOIN users u ON o.user_id = u.id $where";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_orders = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $per_page);

// Get orders
$query = "SELECT o.*, u.first_name, u.last_name, u.email, 
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          $where ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get order statistics
$stats = [];
$stats_result = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) as preparing,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM orders");
$stats = $stats_result->fetch_assoc();

// Get order details if viewing
$order_details = null;
$order_items = [];
if ($view_order_id > 0) {
    $stmt = $conn->prepare("SELECT o.*, u.first_name, u.last_name, u.email, u.phone, p.payment_method, p.payment_status 
                           FROM orders o 
                           JOIN users u ON o.user_id = u.id 
                           LEFT JOIN payments p ON o.id = p.order_id 
                           WHERE o.id = ?");
    $stmt->bind_param("i", $view_order_id);
    $stmt->execute();
    $order_details = $stmt->get_result()->fetch_assoc();
    
    if ($order_details) {
        $stmt = $conn->prepare("SELECT oi.*, p.name as product_name FROM order_items oi 
                               JOIN products p ON oi.product_id = p.id 
                               WHERE oi.order_id = ?");
        $stmt->bind_param("i", $view_order_id);
        $stmt->execute();
        $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Bella Italia Admin</title>
    <link rel="stylesheet" href="/college-pro/assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <a href="/college-pro/index.php" class="logo">Bella Italia - Admin</a>
                <ul class="nav-links">
                    <li><a href="/college-pro/admin/dashboard.php">Dashboard</a></li>
                    <li><a href="/college-pro/admin/products.php">Products</a></li>
                    <li><a href="/college-pro/admin/orders.php">Orders</a></li>
                    <li><a href="/college-pro/admin/customers.php">Customers</a></li>
                    <li><a href="/college-pro/admin/reports.php">Reports</a></li>
                    <li><a href="/college-pro/index.php">View Site</a></li>
                    <li><a href="/college-pro/auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-header">
            <h1>Order Management</h1>
        </div>

        <!-- Order Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['pending']; ?></h3>
                <p>Pending</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['preparing']; ?></h3>
                <p>Preparing</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['delivered']; ?></h3>
                <p>Delivered</p>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="filters">
            <form method="GET" action="">
                <div class="grid grid-3">
                    <div class="filter-group">
                        <label>Search</label>
                        <input type="text" name="search" placeholder="Search by name, email, or order ID..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="preparing" <?php echo $status === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                            <option value="on_the_way" <?php echo $status === 'on_the_way' ? 'selected' : ''; ?>>On the Way</option>
                            <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="grid grid-2">
            <!-- Orders Table -->
            <div>
                <div class="card">
                    <h2>Orders (<?php echo $total_orders; ?>)</h2>
                    <?php if (count($orders) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?><br>
                                            <small><?php echo htmlspecialchars($order['email']); ?></small>
                                        </td>
                                        <td><?php echo $order['items_count']; ?> item(s)</td>
                                        <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo str_replace('_', '-', $order['status']); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="?view=<?php echo $order['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status ? '&status=' . $status : ''; ?>" class="btn btn-small">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?><?php echo $status ? '&status=' . $status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <span class="current"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?php echo $i; ?><?php echo $status ? '&status=' . $status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?><?php echo $status ? '&status=' . $status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>No orders found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Detail Panel -->
            <div>
                <?php if ($order_details): ?>
                    <div class="card">
                        <h2>Order Details #<?php echo $order_details['id']; ?></h2>
                        
                        <h3 style="margin-top: 1.5rem;">Customer Information</h3>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($order_details['first_name'] . ' ' . $order_details['last_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order_details['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order_details['phone'] ?? 'N/A'); ?></p>
                        
                        <h3 style="margin-top: 1.5rem;">Order Information</h3>
                        <p><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order_details['created_at'])); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge badge-<?php echo str_replace('_', '-', $order_details['status']); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $order_details['status'])); ?>
                            </span>
                        </p>
                        <p><strong>Delivery Type:</strong> <?php echo ucfirst($order_details['delivery_type']); ?></p>
                        <?php if ($order_details['delivery_type'] === 'delivery'): ?>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($order_details['delivery_address']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($order_details['delivery_instructions'])): ?>
                            <p><strong>Instructions:</strong> <?php echo htmlspecialchars($order_details['delivery_instructions']); ?></p>
                        <?php endif; ?>
                        
                        <h3 style="margin-top: 1.5rem;">Order Items</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align: right; font-weight: bold;">Total:</td>
                                    <td style="font-weight: bold;">$<?php echo number_format($order_details['total_price'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <h3 style="margin-top: 1.5rem;">Payment Information</h3>
                        <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order_details['payment_method'])); ?></p>
                        <p><strong>Payment Status:</strong> <?php echo ucfirst($order_details['payment_status']); ?></p>
                        
                        <h3 style="margin-top: 1.5rem;">Update Status</h3>
                        <form method="POST" action="">
                            <input type="hidden" name="order_id" value="<?php echo $order_details['id']; ?>">
                            <div class="form-group">
                                <select name="status" required>
                                    <option value="pending" <?php echo $order_details['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="preparing" <?php echo $order_details['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                    <option value="on_the_way" <?php echo $order_details['status'] === 'on_the_way' ? 'selected' : ''; ?>>On the Way</option>
                                    <option value="delivered" <?php echo $order_details['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $order_details['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-primary btn-block">Update Status</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <p>Select an order to view details.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 Bella Italia. All rights reserved.</p>
        </div>
    </footer>

    <script src="/college-pro/assets/js/script.js"></script>
</body>
</html>

