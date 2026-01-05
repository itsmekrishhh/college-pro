<?php
/**
 * Place Order Handler
 * Processes the order and saves to database
 */

require_once '../config/db.php';
requireCustomer();

// Check if checkout data and cart exist
if (!isset($_SESSION['checkout']) || !isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: /college-pro/cart/cart.php');
    exit();
}

$conn = getDBConnection();

// Start transaction
$conn->begin_transaction();

try {
    $checkout = $_SESSION['checkout'];
    $user_id = $_SESSION['user_id'];
    
    // Get cart items and calculate total
    $cart_items = [];
    $subtotal = 0;
    
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $item_total = $product['price'] * $quantity;
        $subtotal += $item_total;
        
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'item_total' => $item_total
        ];
    }
    
    $delivery_fee = $checkout['delivery_type'] === 'delivery' ? 5.00 : 0;
    $total = $subtotal + $delivery_fee;
    
    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, delivery_address, delivery_type, delivery_instructions, status) 
                           VALUES (?, ?, ?, ?, ?, 'pending')");
    $address = $checkout['delivery_type'] === 'delivery' ? $checkout['address'] : 'Collection';
    $instructions = $checkout['instructions'] ?? '';
    $stmt->bind_param("idsss", $user_id, $total, $address, $checkout['delivery_type'], $instructions);
    $stmt->execute();
    $order_id = $conn->insert_id;
    
    // Insert order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($cart_items as $item) {
        $stmt->bind_param("iiid", $order_id, $item['product']['id'], $item['quantity'], $item['product']['price']);
        $stmt->execute();
    }
    
    // Insert payment record
    $payment_method = $checkout['payment_method'] ?? 'cash_on_delivery';
    $payment_status = $payment_method === 'cash_on_delivery' ? 'pending' : 'pending';
    $stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, payment_status) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $order_id, $payment_method, $payment_status);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Store order ID in session
    $_SESSION['order_id'] = $order_id;
    
    // Clear cart and checkout data
    unset($_SESSION['cart']);
    unset($_SESSION['checkout']);
    
    // Redirect to thank you page
    header('Location: /college-pro/thank_you.php');
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Order placement error: " . $e->getMessage());
    header('Location: /college-pro/cart/payment.php?error=1');
    exit();
}

$conn->close();

?>

