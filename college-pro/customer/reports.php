<?php
/**
 * Customer Reports Page
 * Redirects to dashboard reports tab
 */

require_once '../config/db.php';
requireCustomer();

header('Location: /college-pro/customer/dashboard.php?tab=reports');
exit();

?>

