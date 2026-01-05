<?php
/**
 * Payment Page
 * Payment method selection and confirmation
 */

require_once '../config/db.php';
requireCustomer();

// Check if checkout data exists
if (!isset($_SESSION['checkout'])) {
    header('Location: /college-pro/cart/checkout.php');
    exit();
}

$checkout = $_SESSION['checkout'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? 'cash_on_delivery';
    $_SESSION['checkout']['payment_method'] = $payment_method;
    header('Location: /college-pro/cart/place_order.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Bella Italia</title>
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
        <h1 style="margin: 2rem 0;">Payment</h1>

        <div class="grid grid-2">
            <!-- Payment Method -->
            <div>
                <div class="card">
                    <h2>Select Payment Method</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="radio-group">
                                <input type="radio" name="payment_method" value="cash_on_delivery" checked>
                                <span><strong>Cash on Delivery</strong> (Available)</span>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="radio-group">
                                <input type="radio" name="payment_method" value="card" disabled>
                                <span><strong>Credit/Debit Card</strong> (Coming Soon)</span>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="radio-group">
                                <input type="radio" name="payment_method" value="esewa" disabled>
                                <span><strong>eSewa</strong> (Coming Soon)</span>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="radio-group">
                                <input type="radio" name="payment_method" value="khalti" disabled>
                                <span><strong>Khalti</strong> (Coming Soon)</span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Confirm Payment</button>
                    </form>
                </div>

                <a href="/college-pro/cart/checkout.php" class="btn btn-secondary" style="margin-top: 1rem;">← Back to Checkout</a>
            </div>

            <!-- Order Summary -->
            <div>
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <div style="margin-bottom: 1rem;">
                        <strong>Customer:</strong> <?php echo htmlspecialchars($checkout['name']); ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($checkout['email']); ?><br>
                        <strong>Phone:</strong> <?php echo htmlspecialchars($checkout['phone']); ?><br>
                        <strong>Delivery Type:</strong> <?php echo ucfirst($checkout['delivery_type']); ?><br>
                        <?php if ($checkout['delivery_type'] === 'delivery'): ?>
                            <strong>Address:</strong> <?php echo htmlspecialchars($checkout['address']); ?><br>
                        <?php endif; ?>
                        <?php if (!empty($checkout['instructions'])): ?>
                            <strong>Instructions:</strong> <?php echo htmlspecialchars($checkout['instructions']); ?><br>
                        <?php endif; ?>
                    </div>
                    <div class="order-summary-row">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($checkout['subtotal'], 2); ?></span>
                    </div>
                    <div class="order-summary-row">
                        <span>Delivery Fee</span>
                        <span>$<?php echo number_format($checkout['delivery_fee'], 2); ?></span>
                    </div>
                    <div class="order-summary-row">
                        <span>Total</span>
                        <span>$<?php echo number_format($checkout['total'], 2); ?></span>
                    </div>
                </div>
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

