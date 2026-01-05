<?php
/**
 * Admin Reports Page
 * Generate and view reports
 */

require_once '../config/db.php';
requireAdmin();

$conn = getDBConnection();

// Get date range filter
$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : 'all';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Build date filter
$date_where = "WHERE 1=1";
if ($date_range === 'today') {
    $date_where = "WHERE DATE(o.created_at) = CURDATE()";
} elseif ($date_range === 'week') {
    $date_where = "WHERE WEEK(o.created_at) = WEEK(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE())";
} elseif ($date_range === 'month') {
    $date_where = "WHERE MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE())";
} elseif ($date_range === 'year') {
    $date_where = "WHERE YEAR(o.created_at) = YEAR(CURDATE())";
} elseif ($date_range === 'custom' && $start_date && $end_date) {
    $date_where = "WHERE DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'";
}

// Get summary statistics
$summary_query = "SELECT 
    COUNT(DISTINCT o.id) as total_orders,
    COALESCE(SUM(o.total_price), 0) as total_revenue,
    COUNT(DISTINCT o.user_id) as total_customers,
    COALESCE(AVG(o.total_price), 0) as avg_order_value
    FROM orders o 
    $date_where AND o.status != 'cancelled'";
$summary_result = $conn->query($summary_query);
$summary = $summary_result->fetch_assoc();

// Get product performance
$product_query = "SELECT p.name, 
                  SUM(oi.quantity) as total_sold,
                  SUM(oi.quantity * oi.price) as revenue
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.id
                  JOIN orders o ON oi.order_id = o.id
                  $date_where AND o.status != 'cancelled'
                  GROUP BY p.id, p.name
                  ORDER BY total_sold DESC
                  LIMIT 10";
$product_performance = $conn->query($product_query)->fetch_all(MYSQLI_ASSOC);

// Get daily order counts for last 30 days
$daily_orders_query = "SELECT DATE(created_at) as order_date, COUNT(*) as order_count, SUM(total_price) as daily_revenue
                       FROM orders
                       WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND status != 'cancelled'
                       GROUP BY DATE(created_at)
                       ORDER BY order_date DESC";
$daily_orders = $conn->query($daily_orders_query)->fetch_all(MYSQLI_ASSOC);

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Bella Italia Admin</title>
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
            <h1>Reports</h1>
            <div style="display: flex; gap: 1rem;">
                <button onclick="window.print()" class="btn btn-primary">Print Report</button>
                <button onclick="exportToCSV()" class="btn btn-primary">Export CSV</button>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="filters">
            <form method="GET" action="">
                <div class="grid grid-3">
                    <div class="filter-group">
                        <label>Date Range</label>
                        <select name="date_range" onchange="toggleCustomDates(this.value)">
                            <option value="all" <?php echo $date_range === 'all' ? 'selected' : ''; ?>>All Time</option>
                            <option value="today" <?php echo $date_range === 'today' ? 'selected' : ''; ?>>Today</option>
                            <option value="week" <?php echo $date_range === 'week' ? 'selected' : ''; ?>>This Week</option>
                            <option value="month" <?php echo $date_range === 'month' ? 'selected' : ''; ?>>This Month</option>
                            <option value="year" <?php echo $date_range === 'year' ? 'selected' : ''; ?>>This Year</option>
                            <option value="custom" <?php echo $date_range === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                        </select>
                    </div>
                    <div class="filter-group" id="custom-dates" style="display: <?php echo $date_range === 'custom' ? 'block' : 'none'; ?>;">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="filter-group" id="custom-dates-end" style="display: <?php echo $date_range === 'custom' ? 'block' : 'none'; ?>;">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Apply Filter</button>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $summary['total_orders']; ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <h3>$<?php echo number_format($summary['total_revenue'], 2); ?></h3>
                <p>Total Revenue</p>
            </div>
            <div class="stat-card">
                <h3>$<?php echo number_format($summary['avg_order_value'], 2); ?></h3>
                <p>Average Order Value</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $summary['total_customers']; ?></h3>
                <p>Total Customers</p>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-2" style="margin: 2rem 0;">
            <div class="chart-placeholder">
                <h3>Revenue Trend</h3>
                <p>Chart visualization would go here</p>
            </div>
            <div class="chart-placeholder">
                <h3>Sales by Category</h3>
                <p>Chart visualization would go here</p>
            </div>
        </div>

        <!-- Product Performance -->
        <div class="card" style="margin: 2rem 0;">
            <h2>Top Selling Products</h2>
            <?php if (count($product_performance) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Total Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($product_performance as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo $product['total_sold']; ?> units</td>
                                <td>$<?php echo number_format($product['revenue'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No data available for selected period.</p>
            <?php endif; ?>
        </div>

        <!-- Daily Orders -->
        <div class="card" style="margin: 2rem 0;">
            <h2>Daily Orders (Last 30 Days)</h2>
            <?php if (count($daily_orders) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Order Count</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daily_orders as $daily): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($daily['order_date'])); ?></td>
                                <td><?php echo $daily['order_count']; ?></td>
                                <td>$<?php echo number_format($daily['daily_revenue'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No data available for selected period.</p>
            <?php endif; ?>
        </div>

        <div class="card" style="margin: 2rem 0; text-align: center; padding: 2rem;">
            <p><strong>Print Preview Note:</strong> Use your browser's print function (Ctrl+P) to print this report.</p>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 Bella Italia. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function toggleCustomDates(value) {
            const customDates = document.getElementById('custom-dates');
            const customDatesEnd = document.getElementById('custom-dates-end');
            if (value === 'custom') {
                customDates.style.display = 'block';
                customDatesEnd.style.display = 'block';
            } else {
                customDates.style.display = 'none';
                customDatesEnd.style.display = 'none';
            }
        }

        function exportToCSV() {
            alert('CSV export functionality would be implemented here.');
        }
    </script>
    <script src="/college-pro/assets/js/script.js"></script>
</body>
</html>

