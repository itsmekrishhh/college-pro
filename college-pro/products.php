<?php
/**
 * Products Listing Page
 * Displays all products with search, filter, and sorting options
 */

require_once 'config/db.php';

$conn = getDBConnection();

// Get filter parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query
$where = "WHERE p.availability = 'available'";
$params = [];
$types = '';

if ($category_id > 0) {
    $where .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

if (!empty($search)) {
    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

// Sort options
$order_by = "ORDER BY p.created_at DESC";
switch ($sort) {
    case 'price_low':
        $order_by = "ORDER BY p.price ASC";
        break;
    case 'price_high':
        $order_by = "ORDER BY p.price DESC";
        break;
    case 'name':
        $order_by = "ORDER BY p.name ASC";
        break;
    case 'newest':
    default:
        $order_by = "ORDER BY p.created_at DESC";
        break;
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM products p $where";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_products = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $per_page);

// Get products
$query = "SELECT p.*, c.name as category_name FROM products p 
          JOIN categories c ON p.category_id = c.id 
          $where $order_by LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get categories for filter
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories = $conn->query($categories_query)->fetch_all(MYSQLI_ASSOC);

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Bella Italia</title>
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
        <h1 style="margin: 2rem 0;">Our Menu</h1>

        <!-- Filters -->
        <div class="filters">
            <form method="GET" action="">
                <div class="grid grid-3">
                    <div class="filter-group">
                        <label>Search</label>
                        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Sort By</label>
                        <select name="sort">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Apply Filters</button>
                <a href="/college-pro/products.php" class="btn btn-secondary" style="margin-top: 1rem;">Clear</a>
            </form>
        </div>

        <!-- Products Grid -->
        <?php if (count($products) > 0): ?>
            <div class="grid grid-3">
                <?php foreach ($products as $product): ?>
                    <div class="card product-card">
                        <div class="card-img">Image Placeholder</div>
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['category_name']); ?></p>
                        <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                        <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
                        <a href="/college-pro/product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>&sort=<?php echo $sort; ?>">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>&sort=<?php echo $sort; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>&sort=<?php echo $sort; ?>">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="card">
                <p>No products found. Try adjusting your filters.</p>
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

