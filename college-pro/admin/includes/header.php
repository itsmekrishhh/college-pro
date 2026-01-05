<?php
/**
 * Admin Header
 * Include this at the top of admin pages
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin'; ?> - Bella Italia</title>
    <link rel="stylesheet" href="/college-pro/assets/css/style.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <div class="admin-main">
            <!-- Header -->
            <div class="admin-header">
                <div class="admin-header-greeting">
                    Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </div>
                <div class="admin-header-profile">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
            </div>
            
            <!-- Content -->
            <div class="admin-content">

