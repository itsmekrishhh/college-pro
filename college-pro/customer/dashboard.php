<?php
/**
 * Customer Dashboard
 * Profile page with tabs for Orders, Order Tracking, Addresses, and Reports
 */

require_once '../config/db.php';
requireCustomer();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get active tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'orders';

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get orders for Orders tab
$stmt = $conn->prepare("SELECT o.*, COUNT(oi.id) as items_count 
                       FROM orders o 
                       LEFT JOIN order_items oi ON o.id = oi.order_id 
                       WHERE o.user_id = ? 
                       GROUP BY o.id 
                       ORDER BY o.created_at DESC 
                       LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get current active order for tracking
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND status NOT IN ('delivered', 'cancelled') ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_order = $stmt->get_result()->fetch_assoc();

// Get all orders for reports
$stmt = $conn->prepare("SELECT o.*, SUM(oi.quantity * oi.price) as order_total 
                       FROM orders o 
                       LEFT JOIN order_items oi ON o.id = oi.order_id 
                       WHERE o.user_id = ? 
                       GROUP BY o.id 
                       ORDER BY o.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$all_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate reports statistics
$total_orders = count($all_orders);
$total_spent = 0;
foreach ($all_orders as $order) {
    $total_spent += $order['total_price'];
}
$avg_order_value = $total_orders > 0 ? $total_spent / $total_orders : 0;

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bella Italia</title>
    <link rel="stylesheet" href="/college-pro/assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <a href="/college-pro/index.php" class="logo">Bella Italia</a>
                <ul class="nav-links">
                    <li><a href="/college-pro/index.php">Home</a></li>
                    <li><a href="/college-pro/products.php">Products</a></li>
                    <li><a href="/college-pro/cart/cart.php">Cart</a></li>
                    <li><a href="/college-pro/customer/dashboard.php">Profile</a></li>
                    <li><a href="/college-pro/auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
            <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            <p>Phone: <?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></p>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <a href="?tab=orders" class="tab <?php echo $tab === 'orders' ? 'active' : ''; ?>">Orders</a>
            <a href="?tab=tracking" class="tab <?php echo $tab === 'tracking' ? 'active' : ''; ?>">Order Tracking</a>
            <a href="?tab=addresses" class="tab <?php echo $tab === 'addresses' ? 'active' : ''; ?>">Addresses</a>
            <a href="?tab=reports" class="tab <?php echo $tab === 'reports' ? 'active' : ''; ?>">Reports</a>
        </div>

        <!-- Orders Tab -->
        <div class="tab-content <?php echo $tab === 'orders' ? 'active' : ''; ?>">
            <h2>Recent Orders</h2>
            <?php if (count($orders) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                <td><?php echo $order['items_count']; ?> item(s)</td>
                                <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo str_replace('_', '-', $order['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/college-pro/customer/dashboard.php?tab=tracking&order_id=<?php echo $order['id']; ?>" class="btn btn-small">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="card">
                    <p>No orders yet. Start shopping!</p>
                    <a href="/college-pro/products.php" class="btn btn-primary">Browse Products</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Order Tracking Tab -->
        <div class="tab-content <?php echo $tab === 'tracking' ? 'active' : ''; ?>">
            <h2>Order Tracking</h2>
            <?php 
            $track_order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
            if ($track_order_id > 0 && $current_order && $current_order['id'] == $track_order_id) {
                $track_order = $current_order;
            } elseif ($current_order) {
                $track_order = $current_order;
            } else {
                $track_order = null;
            }
            ?>
            
            <?php if ($track_order): ?>
                <?php
                $conn = getDBConnection();
                $stmt = $conn->prepare("SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                $stmt->bind_param("i", $track_order['id']);
                $stmt->execute();
                $track_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $conn->close();
                
                $statuses = ['pending', 'preparing', 'on_the_way', 'delivered'];
                $current_status_index = array_search($track_order['status'], $statuses);
                ?>
                <div class="card">
                    <h3>Order #<?php echo $track_order['id']; ?></h3>
                    <p>Total: $<?php echo number_format($track_order['total_price'], 2); ?></p>
                    <p>Date: <?php echo date('F j, Y g:i A', strtotime($track_order['created_at'])); ?></p>
                    
                    <!-- Status Steps -->
                    <div class="status-steps" style="margin: 2rem 0;">
                        <?php foreach ($statuses as $index => $status): ?>
                            <div class="status-step <?php 
                                echo $index < $current_status_index ? 'completed' : '';
                                echo $index == $current_status_index ? 'active' : '';
                            ?>">
                                <?php echo $index + 1; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
                        <span>Placed</span>
                        <span>Preparing</span>
                        <span>On the way</span>
                        <span>Delivered</span>
                    </div>
                    
                    <p><strong>Current Status:</strong> 
                        <span class="badge badge-<?php echo str_replace('_', '-', $track_order['status']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $track_order['status'])); ?>
                        </span>
                    </p>
                    
                    <h4 style="margin-top: 2rem;">Order Items</h4>
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
                            <?php foreach ($track_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="card">
                    <p>No active orders to track.</p>
                    <a href="/college-pro/products.php" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Addresses Tab -->
        <div class="tab-content <?php echo $tab === 'addresses' ? 'active' : ''; ?>">
            <h2>Saved Addresses</h2>
            <div class="card">
                <p>Address management feature coming soon. For now, you can enter your address during checkout.</p>
                <a href="/college-pro/products.php" class="btn btn-primary">Order Now</a>
            </div>
        </div>

        <!-- Reports Tab -->
        <div class="tab-content <?php echo $tab === 'reports' ? 'active' : ''; ?>">
            <h2>Order Reports</h2>
            
            <!-- Summary Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $total_orders; ?></h3>
                    <p>Total Orders</p>
                </div>
                <div class="stat-card">
                    <h3>$<?php echo number_format($total_spent, 2); ?></h3>
                    <p>Total Spent</p>
                </div>
                <div class="stat-card">
                    <h3>$<?php echo number_format($avg_order_value, 2); ?></h3>
                    <p>Average Order Value</p>
                </div>
            </div>

            <!-- Chart Placeholder -->
            <div class="chart-placeholder">
                <p>Monthly Order Chart Placeholder</p>
                <p style="color: #999; font-size: 0.9rem;">Chart visualization would go here</p>
            </div>

            <!-- Order Reports Table -->
            <h3 style="margin-top: 2rem;">Order History</h3>
            <?php if (count($all_orders) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $conn = getDBConnection();
                        foreach ($all_orders as $order): 
                            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?");
                            $stmt->bind_param("i", $order['id']);
                            $stmt->execute();
                            $item_count = $stmt->get_result()->fetch_assoc()['count'];
                        ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo $item_count; ?> item(s)</td>
                                <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo str_replace('_', '-', $order['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php $conn->close(); ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="card">
                    <p>No order history available.</p>
                </div>
            <?php endif; ?>
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

