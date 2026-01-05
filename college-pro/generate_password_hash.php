<?php
/**
 * Password Hash Generator
 * Run this file once to generate password hashes for database.sql
 * 
 * Usage: Open http://localhost/college-pro/generate_password_hash.php in your browser
 * Copy the generated hashes to database.sql
 */

// Generate hashes
$admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
$customer_hash = password_hash('customer123', PASSWORD_DEFAULT);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Password Hash Generator</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        pre { background: #f5f5f5; padding: 15px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>Password Hash Generator</h1>
    <p>Copy these hashes to update the password fields in database.sql:</p>
    
    <h2>Admin Password (admin123):</h2>
    <pre><?php echo $admin_hash; ?></pre>
    
    <h2>Customer Password (customer123):</h2>
    <pre><?php echo $customer_hash; ?></pre>
    
    <p><strong>Note:</strong> Update the INSERT statements in database.sql with these hashes.</p>
    <p>Or better yet, just register new users through the website!</p>
</body>
</html>

