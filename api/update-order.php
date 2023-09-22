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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $orderId = $data['id'];

    $status = $data['status'];

    $stmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $orderId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'Order updated successfully']);
        http_response_code(200);
    } else {
        echo json_encode(['error' => 'Order not found']);
        http_response_code(404);
    }
}


