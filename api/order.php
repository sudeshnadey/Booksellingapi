
<?php
require_once 'require/header.php';
include 'require/auth-user.php';
include 'require/login-user.php';
require_once './config/db-connect.php';

try {
    $db = createDatabaseConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    http_response_code(500);
    exit;
}

$userId = getUser()->id;
// Retrieve all orders for a user
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $stmt = $db->prepare('SELECT * FROM orders WHERE user_id = ?');
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['data' => $orders]);
    http_response_code(200);
}

// Create a new order and order details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $addressId = $data['address_id'];
    $paymentMethod = $data['payment_method'];
    $status = 'pending';
    $items = $data['items'];

    // Calculate the total price
    $totalPrice = 0;
    foreach ($items as $item) {
        $productId = $item['item_id'];
        $quantity = $item['quantity'];

        // Retrieve the price of the product from the database
        $stmt = $db->prepare('SELECT price FROM books WHERE id = ?');
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        $price = $product['price'];
        $totalPrice += ($price * $quantity);
    }

    // Create the order
    $stmt = $db->prepare('INSERT INTO orders (user_id, address_id, payment_method, status, total_price) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $addressId, $paymentMethod, $status, $totalPrice]);

    // Get the newly created order ID
    $orderId = $db->lastInsertId();

    // Create the order details for each item
    foreach ($items as $item) {
        $productId = $item['item_id'];
        $quantity = $item['quantity'];
        $type = $item['type'] ?? 'book';

        $stmt = $db->prepare('INSERT INTO order_details (order_id, item_id, quantity,type) VALUES (?, ?, ?,?)');
        $stmt->execute([$orderId, $productId, $quantity, $type]);

        
        $stmt = $db->prepare('UPDATE books SET quantity = quantity - ? WHERE id = ?');
        $stmt->execute([$quantity, $productId]);

    }

    $stmt = $db->prepare('DELETE FROM cart WHERE user_id = ?');
    $stmt->execute([$userId]);
    // Return the newly created order if needed
    $stmt = $db->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['message' => 'Order created successfully', 'data' => $order]);
    http_response_code(200);
}

// Update an existing order
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $orderId = $_GET['id'];
    $quantity = $data['quantity'];
    $addressId = $data['address_id'];
    $paymentMethod = $data['payment_method'];
    $status = $data['status'];

    $stmt = $db->prepare('UPDATE orders SET quantity = ?, address_id = ?, payment_method = ?, status = ? WHERE id = ?');
    $stmt->execute([$quantity, $addressId, $paymentMethod, $status, $orderId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'Order updated successfully']);
        http_response_code(200);
    } else {
        echo json_encode(['error' => 'Order not found']);
        http_response_code(404);
    }
}

// Delete an order
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $orderId = $_GET['id'];
    $status = 'pending';
    $stmt = $db->prepare('SELECT id FROM orders WHERE id = ? AND user_id=? AND status=?');
    $stmt->execute([$orderId, $userId, $status]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt_2 = $db->prepare('DELETE FROM order_details WHERE order_id = ? ');
    $stmt = $db->prepare('DELETE FROM orders WHERE id = ? AND user_id=?');
    if ($order) {
        $stmt_2->execute([$orderId]);
        $stmt->execute([$orderId, $userId]);


        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Order deleted successfully']);
            http_response_code(200);
        }else {
            echo json_encode(['error' => 'Error while deleting order']);
            http_response_code(404);
        }
    } else {
        echo json_encode(['error' => 'Order not found or Order Status ']);
        http_response_code(404);
    }
}
