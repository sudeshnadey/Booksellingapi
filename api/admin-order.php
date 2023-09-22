
<?php
require_once 'require/header.php';
include 'require/auth-admin.php';
require_once './config/db-connect.php';

try {
    $db = createDatabaseConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    http_response_code(500);
    exit;
}

// Retrieve all orders for a user
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $stmt = $db->prepare('SELECT * FROM orders');
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['data' => $orders]);
    http_response_code(200);
}

// Create a new order and order details

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

    $stmt = $db->prepare('DELETE FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'Order deleted successfully']);
        http_response_code(200);
    } else {
        echo json_encode(['error' => 'Order not found']);
        http_response_code(404);
    }
}