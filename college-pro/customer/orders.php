<?php
/**
 * Customer Orders Page
 * Redirects to dashboard orders tab
 */

require_once '../config/db.php';
requireCustomer();

header('Location: /college-pro/customer/dashboard.php?tab=orders');
exit();

?>

