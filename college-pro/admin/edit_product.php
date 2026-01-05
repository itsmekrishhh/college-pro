<?php
/**
 * Edit Product Page
 * Form to edit existing products
 */

require_once '../config/db.php';
requireAdmin();

$error = '';
$success = '';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: /college-pro/admin/products.php');
    exit();
}

$conn = getDBConnection();

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: /college-pro/admin/products.php');
    exit();
}

// Get categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $description = trim($_POST['description'] ?? '');
    $availability = $_POST['availability'] ?? 'available';
    
    if (empty($name) || $category_id <= 0 || $price <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $conn->prepare("UPDATE products SET name = ?, category_id = ?, price = ?, description = ?, availability = ? WHERE id = ?");
        $stmt->bind_param("sidsssi", $name, $category_id, $price, $description, $availability, $product_id);
        
        if ($stmt->execute()) {
            $success = 'Product updated successfully!';
            // Refresh product data
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Failed to update product. Please try again.';
        }
        $stmt->close();
    }
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Bella Italia Admin</title>
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
            <h1>Edit Product</h1>
            <a href="/college-pro/admin/products.php" class="btn btn-secondary">← Back to Products</a>
        </div>

        <div class="card" style="max-width: 600px; margin: 2rem auto;">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($product['name']); ?>">
                </div>

                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price">Price ($) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required 
                           value="<?php echo $product['price']; ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="availability">Availability *</label>
                    <select id="availability" name="availability" required>
                        <option value="available" <?php echo $product['availability'] === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="unavailable" <?php echo $product['availability'] === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Image Upload (Placeholder)</label>
                    <div class="card-img" style="height: 150px; margin-bottom: 1rem;">Image Upload Placeholder</div>
                    <input type="file" disabled style="padding: 0.5rem;">
                    <small style="color: #666;">Image upload functionality coming soon</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Update Product</button>
            </form>
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

