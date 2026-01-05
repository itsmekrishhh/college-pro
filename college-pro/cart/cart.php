<?php
/**
 * Shopping Cart Page
 * Displays cart items and allows quantity updates
 */

require_once '../config/db.php';
requireCustomer();

$conn = getDBConnection();

// Handle actions (add, update, remove)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    // Initialize cart session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if ($action === 'add') {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        if ($product_id > 0 && $quantity > 0) {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
            header('Location: /college-pro/cart/cart.php');
            exit();
        }
    } elseif ($action === 'update') {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        
        if ($product_id > 0) {
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id] = $quantity;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
            header('Location: /college-pro/cart/cart.php');
            exit();
        }
    } elseif ($action === 'remove') {
        if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            header('Location: /college-pro/cart/cart.php');
            exit();
        }
    }
}

// Get cart items with product details
$cart_items = [];
$subtotal = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $item_total = $product['price'] * $quantity;
        $subtotal += $item_total;
        
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'item_total' => $item_total
        ];
    }
}

$delivery_fee = $subtotal > 0 ? 5.00 : 0;
$total = $subtotal + $delivery_fee;

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Bella Italia</title>
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
                    <li><a href="/college-pro/cart/cart.php">Cart (<span class="cart-count">0</span>)</a></li>
                    <li><a href="/college-pro/customer/dashboard.php">Profile</a></li>
                    <li><a href="/college-pro/auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1 style="margin: 2rem 0;">Shopping Cart</h1>

        <?php if (count($cart_items) > 0): ?>
            <div class="grid grid-2">
                <!-- Cart Items -->
                <div>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="card-img" style="width: 100px; height: 100px;">Image</div>
                            <div>
                                <h3><?php echo htmlspecialchars($item['product']['name']); ?></h3>
                                <p>$<?php echo number_format($item['product']['price'], 2); ?> each</p>
                            </div>
                            <div>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="10" style="width: 60px;" onchange="this.form.submit()">
                                </form>
                            </div>
                            <div>
                                <strong>$<?php echo number_format($item['item_total'], 2); ?></strong>
                            </div>
                            <div>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-small">Remove</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div>
                    <div class="order-summary">
                        <h2>Order Summary</h2>
                        <div class="order-summary-row">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="order-summary-row">
                            <span>Delivery Fee</span>
                            <span>$<?php echo number_format($delivery_fee, 2); ?></span>
                        </div>
                        <div class="order-summary-row">
                            <span>Total</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>

                        <div class="form-group" style="margin-top: 1.5rem;">
                            <label>Promo Code (Optional)</label>
                            <input type="text" placeholder="Enter promo code">
                        </div>

                        <a href="/college-pro/cart/checkout.php" class="btn btn-primary btn-block" style="margin-top: 1rem;">Proceed to Checkout</a>
                        <a href="/college-pro/products.php" class="btn btn-secondary btn-block" style="margin-top: 1rem;">Continue Shopping</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <h2>Your cart is empty</h2>
                <p>Start adding items to your cart!</p>
                <a href="/college-pro/products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
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

