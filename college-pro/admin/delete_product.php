<?php
/**
 * Delete Product Handler
 * Deletes a product from the database
 */

require_once '../config/db.php';
requireAdmin();

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: /college-pro/admin/products.php');
    exit();
}

$conn = getDBConnection();

// Delete product
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->close();

$conn->close();

header('Location: /college-pro/admin/products.php?deleted=1');
exit();

?>

