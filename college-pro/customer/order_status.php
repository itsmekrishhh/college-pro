<?php
/**
 * Customer Order Status Page
 * Redirects to dashboard tracking tab
 */

require_once '../config/db.php';
requireCustomer();

header('Location: /college-pro/customer/dashboard.php?tab=tracking');
exit();

?>

