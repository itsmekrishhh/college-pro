<?php
/**
 * Product Details Page
 * Shows detailed information about a single product
 */

require_once 'config/db.php';

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: /college-pro/products.php');
    exit();
}

$conn = getDBConnection();

// Get product details
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                       JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: /college-pro/products.php');
    exit();
}

// Get related products (same category, different product)
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                       JOIN categories c ON p.category_id = c.id 
                       WHERE p.category_id = ? AND p.id != ? AND p.availability = 'available' 
                       LIMIT 4");
$stmt->bind_param("ii", $product['category_id'], $product_id);
$stmt->execute();
$related_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Bella Italia</title>
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
                    <?php if (isLoggedIn()): ?>
                        <li><a href="/college-pro/cart/cart.php">Cart (<span class="cart-count">0</span>)</a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="/college-pro/admin/dashboard.php">Admin</a></li>
                        <?php else: ?>
                            <li><a href="/college-pro/customer/dashboard.php">Profile</a></li>
                        <?php endif; ?>
                        <li><a href="/college-pro/auth/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/college-pro/auth/login.php">Login</a></li>
                        <li><a href="/college-pro/auth/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="grid grid-2" style="margin: 2rem 0;">
            <!-- Product Image -->
            <div>
                <div class="card-img" style="height: 400px; font-size: 1.5rem;">Product Image Placeholder</div>
            </div>

            <!-- Product Info -->
            <div>
                <div class="card">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p style="color: #666; margin-bottom: 1rem;">Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
                    <div class="price" style="font-size: 2rem; margin: 1.5rem 0;">$<?php echo number_format($product['price'], 2); ?></div>
                    
                    <h3 style="margin-top: 2rem;">Description</h3>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>

                    <h3 style="margin-top: 2rem;">Ingredients</h3>
                    <p>Fresh ingredients: Italian mozzarella, premium flour, authentic herbs and spices, locally sourced vegetables.</p>

                    <?php if ($product['availability'] === 'available'): ?>
                        <form method="POST" action="/college-pro/cart/cart.php" style="margin-top: 2rem;">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <div class="form-group">
                                <label for="quantity">Quantity</label>
                                <div class="quantity-control">
                                    <button type="button" class="btn btn-small" onclick="decreaseQuantity()">-</button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="10" style="width: 80px;">
                                    <button type="button" class="btn btn-small" onclick="increaseQuantity()">+</button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-error">This product is currently unavailable.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Customer Reviews Placeholder -->
        <section style="margin: 3rem 0;">
            <h2>Customer Reviews</h2>
            <div class="card">
                <p>No reviews yet. Be the first to review this product!</p>
            </div>
        </section>

        <!-- Related Products -->
        <?php if (count($related_products) > 0): ?>
            <section style="margin: 3rem 0;">
                <h2>Related Products</h2>
                <div class="grid grid-4">
                    <?php foreach ($related_products as $related): ?>
                        <div class="card product-card">
                            <div class="card-img">Image Placeholder</div>
                            <h3><?php echo htmlspecialchars($related['name']); ?></h3>
                            <div class="price">$<?php echo number_format($related['price'], 2); ?></div>
                            <a href="/college-pro/product_details.php?id=<?php echo $related['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 Bella Italia. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function increaseQuantity() {
            const input = document.getElementById('quantity');
            if (parseInt(input.value) < 10) {
                input.value = parseInt(input.value) + 1;
            }
        }

        function decreaseQuantity() {
            const input = document.getElementById('quantity');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }
    </script>
    <script src="/college-pro/assets/js/script.js"></script>
</body>
</html>

