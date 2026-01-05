<?php
/**
 * Thank You Page
 * Order confirmation and details
 */

require_once 'config/db.php';
requireCustomer();

// Get order ID from session
if (!isset($_SESSION['order_id'])) {
    header('Location: /college-pro/index.php');
    exit();
}

$order_id = $_SESSION['order_id'];
unset($_SESSION['order_id']);

$conn = getDBConnection();

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.first_name, u.last_name, u.email, u.phone, p.payment_method, p.payment_status 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       LEFT JOIN payments p ON o.id = p.order_id 
                       WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: /college-pro/index.php');
    exit();
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.name as product_name FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate estimated delivery time (45 minutes)
$estimated_delivery = date('H:i', strtotime($order['created_at'] . ' +45 minutes'));

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - Bella Italia</title>
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
                    <li><a href="/college-pro/customer/dashboard.php">Profile</a></li>
                    <li><a href="/college-pro/auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div style="text-align: center; margin: 3rem 0;">
            <div style="font-size: 4rem; color: #388e3c; margin-bottom: 1rem;">✓</div>
            <h1>Thank You for Your Order!</h1>
            <p style="font-size: 1.2rem; margin-top: 1rem;">Your order has been placed successfully.</p>
        </div>

        <!-- Order Details Card -->
        <div class="card" style="max-width: 800px; margin: 0 auto 2rem;">
            <h2>Order Details</h2>
            
            <div style="margin: 1.5rem 0;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                    <strong>Order ID:</strong>
                    <span>#<?php echo $order_id; ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                    <strong>Status:</strong>
                    <span class="badge badge-<?php echo str_replace('_', '-', $order['status']); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                    <strong>Order Date:</strong>
                    <span><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                    <strong>Estimated Delivery:</strong>
                    <span><?php echo $estimated_delivery; ?></span>
                </div>
            </div>

            <hr style="margin: 2rem 0; border: 1px solid #ccc;">

            <h3 style="margin-bottom: 1rem;">Items Ordered</h3>
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
                        <td style="font-weight: bold;">$<?php echo number_format($order['total_price'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>

            <hr style="margin: 2rem 0; border: 1px solid #ccc;">

            <div style="margin: 1.5rem 0;">
                <h3 style="margin-bottom: 1rem;">Delivery Information</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></p>
                <p><strong>Delivery Type:</strong> <?php echo ucfirst($order['delivery_type']); ?></p>
                <?php if ($order['delivery_type'] === 'delivery'): ?>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?></p>
                <?php endif; ?>
                <?php if (!empty($order['delivery_instructions'])): ?>
                    <p><strong>Instructions:</strong> <?php echo htmlspecialchars($order['delivery_instructions']); ?></p>
                <?php endif; ?>
                <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                <p><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
            </div>
        </div>

        <!-- Order Tracking Placeholder -->
        <div class="card" style="max-width: 800px; margin: 0 auto 2rem; text-align: center;">
            <h2>Track Your Order</h2>
            <p>You can track your order status in your profile dashboard.</p>
            <a href="/college-pro/customer/dashboard.php?tab=orders" class="btn btn-primary">View My Orders</a>
        </div>

        <!-- Action Buttons -->
        <div style="text-align: center; margin: 3rem 0;">
            <a href="/college-pro/customer/dashboard.php?tab=orders" class="btn btn-primary">View My Orders</a>
            <a href="/college-pro/products.php" class="btn btn-secondary" style="margin-left: 1rem;">Continue Shopping</a>
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

