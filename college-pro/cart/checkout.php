<?php
/**
 * Checkout Page
 * Customer details and delivery information
 */

require_once '../config/db.php';
requireCustomer();

$error = '';

// Check if cart is not empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: /college-pro/cart/cart.php');
    exit();
}

$conn = getDBConnection();

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get cart items
$cart_items = [];
$subtotal = 0;

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

$delivery_fee = 5.00;
$total = $subtotal + $delivery_fee;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $delivery_type = $_POST['delivery_type'] ?? 'delivery';
    $address = trim($_POST['address'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    
    if (empty($name) || empty($email) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } elseif ($delivery_type === 'delivery' && empty($address)) {
        $error = 'Please provide delivery address.';
    } else {
        // Store checkout data in session
        $_SESSION['checkout'] = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'delivery_type' => $delivery_type,
            'address' => $address,
            'instructions' => $instructions,
            'subtotal' => $subtotal,
            'delivery_fee' => $delivery_fee,
            'total' => $total
        ];
        
        header('Location: /college-pro/cart/payment.php');
        exit();
    }
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Bella Italia</title>
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
        <h1 style="margin: 2rem 0;">Checkout</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="grid grid-2">
            <!-- Checkout Form -->
            <div>
                <div class="card">
                    <h2>Customer Details</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone *</label>
                            <input type="tel" id="phone" name="phone" required 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label>Delivery Type *</label>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="delivery_type" value="delivery" checked onchange="toggleAddress()">
                                    Delivery
                                </label>
                                <label>
                                    <input type="radio" name="delivery_type" value="collection" onchange="toggleAddress()">
                                    Collection
                                </label>
                            </div>
                        </div>

                        <div class="form-group" id="address-group">
                            <label for="address">Delivery Address *</label>
                            <textarea id="address" name="address" rows="4" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="instructions">Delivery Instructions (Optional)</label>
                            <textarea id="instructions" name="instructions" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Proceed to Payment</button>
                    </form>
                </div>

                <a href="/college-pro/cart/cart.php" class="btn btn-secondary" style="margin-top: 1rem;">← Back to Cart</a>
            </div>

            <!-- Order Summary -->
            <div>
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <?php foreach ($cart_items as $item): ?>
                        <div style="padding: 1rem 0; border-bottom: 1px solid #ccc;">
                            <div style="display: flex; justify-content: space-between;">
                                <span><?php echo htmlspecialchars($item['product']['name']); ?> x <?php echo $item['quantity']; ?></span>
                                <span>$<?php echo number_format($item['item_total'], 2); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
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

    <script>
        function toggleAddress() {
            const deliveryType = document.querySelector('input[name="delivery_type"]:checked').value;
            const addressGroup = document.getElementById('address-group');
            const addressInput = document.getElementById('address');
            
            if (deliveryType === 'collection') {
                addressGroup.style.display = 'none';
                addressInput.removeAttribute('required');
            } else {
                addressGroup.style.display = 'block';
                addressInput.setAttribute('required', 'required');
            }
        }
    </script>
    <script src="/college-pro/assets/js/script.js"></script>
</body>
</html>

