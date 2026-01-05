<?php
/**
 * Admin Dashboard
 * Main admin panel with statistics and quick actions
 */

require_once '../config/db.php';
requireAdmin();

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total products
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$stats['total_products'] = $result->fetch_assoc()['count'];

// Products added this week
$result = $conn->query("SELECT COUNT(*) as count FROM products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['products_this_week'] = $result->fetch_assoc()['count'];

// Total orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
$stats['total_orders'] = $result->fetch_assoc()['count'];

// Orders today
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
$stats['orders_today'] = $result->fetch_assoc()['count'];

// Total revenue
$result = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE status != 'cancelled'");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Revenue this month
$result = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE status != 'cancelled' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
$revenue_this_month = $result->fetch_assoc()['total'] ?? 0;
$revenue_last_month = 0;
if ($revenue_this_month > 0) {
    $result = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE status != 'cancelled' AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))");
    $revenue_last_month = $result->fetch_assoc()['total'] ?? 0;
    $revenue_change = $revenue_last_month > 0 ? (($revenue_this_month - $revenue_last_month) / $revenue_last_month) * 100 : 0;
} else {
    $revenue_change = 0;
}

// Total customers
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
$stats['total_customers'] = $result->fetch_assoc()['count'];

// Customers this week
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['customers_this_week'] = $result->fetch_assoc()['count'];

// Get recent orders
$recent_orders = $conn->query("SELECT o.*, u.first_name, u.last_name, u.email 
                               FROM orders o 
                               JOIN users u ON o.user_id = u.id 
                               ORDER BY o.created_at DESC 
                               LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bella Italia</title>
    <link rel="stylesheet" href="/college-pro/assets/css/style.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        
        <div class="admin-main">
            <!-- Header -->
            <div class="admin-header">
                <div class="admin-header-greeting">
                    Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </div>
                <div class="admin-header-profile">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
            </div>
            
            <!-- Content -->
            <div class="admin-content">
                <!-- Page Header -->
                <div class="admin-page-header">
                    <h1>Dashboard</h1>
                    <a href="/college-pro/admin/dashboard.php" class="admin-btn-refresh">Refresh Data</a>
                </div>
                
                <!-- Summary Cards -->
                <div class="admin-stats-grid">
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon">📦</div>
                        <div class="admin-stat-content">
                            <h3><?php echo $stats['total_products']; ?></h3>
                            <p>Total Products</p>
                            <div class="admin-stat-change">+<?php echo $stats['products_this_week']; ?> this week</div>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon">🛒</div>
                        <div class="admin-stat-content">
                            <h3><?php echo $stats['total_orders']; ?></h3>
                            <p>Total Orders</p>
                            <div class="admin-stat-change">+<?php echo $stats['orders_today']; ?> today</div>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon">💰</div>
                        <div class="admin-stat-content">
                            <h3>$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                            <p>Total Revenue</p>
                            <div class="admin-stat-change"><?php echo $revenue_change > 0 ? '+' : ''; ?><?php echo number_format($revenue_change, 1); ?>% this month</div>
                        </div>
                    </div>
                    
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon">👥</div>
                        <div class="admin-stat-content">
                            <h3><?php echo $stats['total_customers']; ?></h3>
                            <p>Customers</p>
                            <div class="admin-stat-change">+<?php echo $stats['customers_this_week']; ?> this week</div>
                        </div>
                    </div>
                </div>
                
                <!-- Two Column Layout -->
                <div class="admin-two-col">
                    <!-- Recent Orders -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2>Recent Orders</h2>
                            <a href="/college-pro/admin/orders.php" class="admin-btn-view-all">View All</a>
                        </div>
                        <?php if (count($recent_orders) > 0): ?>
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): 
                                        $status_class = 'pending';
                                        $status_text = ucfirst(str_replace('_', ' ', $order['status']));
                                        if ($order['status'] === 'delivered') {
                                            $status_class = 'delivered';
                                        } elseif ($order['status'] === 'preparing') {
                                            $status_class = 'preparing';
                                        } elseif ($order['status'] === 'pending') {
                                            $status_class = 'pending';
                                        } elseif ($order['status'] === 'on_the_way') {
                                            $status_class = 'processing';
                                        }
                                        
                                        // Format order ID as BI-XXXXX
                                        $order_id_formatted = 'BI-' . str_pad($order['id'], 5, '0', STR_PAD_LEFT);
                                    ?>
                                        <tr>
                                            <td class="admin-order-id"><?php echo $order_id_formatted; ?></td>
                                            <td class="admin-order-customer">
                                                <?php echo htmlspecialchars($order['first_name'] . ' ' . substr($order['last_name'], 0, 1) . '.'); ?>
                                            </td>
                                            <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                                            <td>
                                                <span class="admin-badge admin-badge-<?php echo $status_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No orders yet.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Actions & Sales Overview -->
                    <div>
                        <!-- Quick Actions -->
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h2>Quick Actions</h2>
                            </div>
                            <div class="admin-quick-actions">
                                <a href="/college-pro/admin/add_product.php" class="admin-quick-btn admin-quick-btn-orange">Add Product</a>
                                <a href="/college-pro/admin/orders.php" class="admin-quick-btn admin-quick-btn-green">View Orders</a>
                                <a href="/college-pro/admin/reports.php" class="admin-quick-btn admin-quick-btn-white">Generate Report</a>
                                <a href="#" class="admin-quick-btn admin-quick-btn-white">Settings</a>
                            </div>
                        </div>
                        
                        <!-- Sales Overview -->
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h2>Sales Overview</h2>
                            </div>
                            <div class="admin-chart-placeholder">
                                <h3>Sales Chart</h3>
                                <p>Weekly Revenue Graph</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/college-pro/assets/js/script.js"></script>
</body>
</html>
