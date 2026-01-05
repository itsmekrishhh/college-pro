<?php
/**
 * Admin Sidebar Navigation
 * Include this file in all admin pages
 */

$current_page = basename($_SERVER['PHP_SELF']);
$page_map = [
    'dashboard.php' => 'dashboard',
    'products.php' => 'products',
    'add_product.php' => 'products',
    'edit_product.php' => 'products',
    'delete_product.php' => 'products',
    'orders.php' => 'orders',
    'customers.php' => 'customers',
    'reports.php' => 'reports'
];

$active_page = $page_map[$current_page] ?? 'dashboard';
?>
<div class="admin-sidebar">
    <div class="admin-sidebar-brand">
        <h1>Bella Italia</h1>
        <p>Admin Panel</p>
    </div>
    
    <nav class="admin-nav">
        <div class="admin-nav-section">
            <h3>Navigation</h3>
            <ul class="admin-nav-links">
                <li>
                    <a href="/college-pro/admin/dashboard.php" class="<?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
                        <span class="admin-nav-icon">⚏</span>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="/college-pro/admin/products.php" class="<?php echo $active_page === 'products' ? 'active' : ''; ?>">
                        <span class="admin-nav-icon">📦</span>
                        Products
                    </a>
                </li>
                <li>
                    <a href="/college-pro/admin/orders.php" class="<?php echo $active_page === 'orders' ? 'active' : ''; ?>">
                        <span class="admin-nav-icon">🛒</span>
                        Orders
                    </a>
                </li>
                <li>
                    <a href="/college-pro/admin/customers.php" class="<?php echo $active_page === 'customers' ? 'active' : ''; ?>">
                        <span class="admin-nav-icon">👥</span>
                        Customers
                    </a>
                </li>
                <li>
                    <a href="/college-pro/admin/reports.php" class="<?php echo $active_page === 'reports' ? 'active' : ''; ?>">
                        <span class="admin-nav-icon">📊</span>
                        Reports
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="admin-sidebar-footer">
        <a href="/college-pro/auth/logout.php">
            <span class="admin-nav-icon">→</span>
            Logout
        </a>
    </div>
</div>

