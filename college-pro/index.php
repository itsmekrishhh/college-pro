<?php
/**
 * Bella Italia - Home Page
 * Main landing page with hero, categories, featured products, and special offers
 */

require_once 'config/db.php';

// Get featured products (first 6 products)
$conn = getDBConnection();
$featured_query = "SELECT p.*, c.name as category_name FROM products p 
                   JOIN categories c ON p.category_id = c.id 
                   WHERE p.availability = 'available' 
                   ORDER BY p.created_at DESC LIMIT 6";
$featured_products = $conn->query($featured_query)->fetch_all(MYSQLI_ASSOC);

// Get categories
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories = $conn->query($categories_query)->fetch_all(MYSQLI_ASSOC);

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bella Italia - Authentic Italian Food</title>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Welcome to Bella Italia</h1>
            <p>Authentic Italian Cuisine Delivered to Your Door</p>
            <a href="/college-pro/products.php" class="btn btn-primary">Order Now</a>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <!-- Categories Sidebar and Content Area -->
        <div class="content-area">
            <!-- Categories Sidebar -->
            <aside class="sidebar">
                <h3>Categories</h3>
                <ul>
                    <?php foreach ($categories as $category): ?>
                        <li><a href="/college-pro/products.php?category=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <!-- Main Content -->
            <main>
                <!-- Featured Products -->
                <section>
                    <h2>Featured Products</h2>
                    <div class="grid grid-3">
                        <?php foreach ($featured_products as $product): ?>
                            <div class="card product-card">
                                <div class="card-img">Image Placeholder</div>
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p><?php echo htmlspecialchars($product['description']); ?></p>
                                <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
                                <a href="/college-pro/product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Special Offers -->
                <section style="margin-top: 3rem;">
                    <h2>Special Offers</h2>
                    <div class="card">
                        <h3>Weekend Special</h3>
                        <p>Get 20% off on all pizzas this weekend! Use code: WEEKEND20</p>
                        <p><strong>Valid until: Sunday 11:59 PM</strong></p>
                    </div>
                </section>

                <!-- About Us -->
                <section style="margin-top: 3rem; margin-bottom: 3rem;">
                    <h2>About Bella Italia</h2>
                    <div class="card">
                        <p>Bella Italia brings you the authentic taste of Italy right to your doorstep. 
                           We specialize in traditional Italian cuisine including delicious pizzas, creamy carbonara, 
                           decadent tiramisu, and hearty lasagna.</p>
                        <p style="margin-top: 1rem;">Our chefs use only the finest ingredients to create 
                           mouth-watering dishes that will transport you straight to Italy. Order now and 
                           experience the authentic Italian dining experience!</p>
                    </div>
                </section>
            </main>
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

