<?php
require_once 'require/header.php';
include 'require/auth-user.php';
require_once './config/db-connect.php';


$pdo=createDatabaseConnection();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_GET['user_id'];

    $stmt = $db->prepare('SELECT * FROM addresses WHERE user_id = ?');
    $stmt->execute([$userId]);
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['data' => $addresses]);
    http_response_code(200);
}

// Create a new address
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'];
    $addressLine1 = $data['address_line1'];
    $addressLine2 = $data['address_line2'];
    $city = $data['city'];
    $state = $data['state'];
    $postalCode = $data['postal_code'];
    $area = $data['area'];
    $landmark = $data['landmark'];
    $isDelivery = $data['is_delivery'];

    $stmt = $db->prepare('INSERT INTO addresses (user_id, address_line1, address_line2, city, state, postal_code, area, landmark, is_delivery) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $addressLine1, $addressLine2, $city, $state, $postalCode, $area, $landmark, $isDelivery]);

    // Return the newly created address if needed
    $addressId = $db->lastInsertId();
    $stmt = $db->prepare('SELECT * FROM addresses WHERE id = ?');
    $stmt->execute([$addressId]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['message' => 'Address created successfully', 'data' => $address]);
    http_response_code(201);
}

// Update an existing address
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $addressId = $_GET['id'];
    $addressLine1 = $data['address_line1'];
    $addressLine2 = $data['address_line2'];
    $city = $data['city'];
    $state = $data['state'];
    $postalCode = $data['postal_code'];
    $area = $data['area'];
    $landmark = $data['landmark'];
    $isDelivery = $data['is_delivery'];

    $stmt = $db->prepare('UPDATE addresses SET address_line1 = ?, address_line2 = ?, city = ?, state = ?, postal_code = ?, area = ?, landmark = ?, is_delivery = ? WHERE id = ?');
    $stmt->execute([$addressLine1, $addressLine2, $city, $state, $postalCode, $area, $landmark, $isDelivery, $addressId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'Address updated successfully']);
        http_response_code(200);
    } else {
        echo json_encode(['error' => 'Address not found']);
        http_response_code(404);
    }
}

// Delete an address
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $addressId = $_GET['id'];

    $stmt = $db->prepare('DELETE FROM addresses WHERE id = ?');
    $stmt->execute([$addressId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'Address deleted successfully']);
        http_response_code(200);
    } else {
        echo json_encode(['error' => 'Address not found']);
        http_response_code(404);
    }
}