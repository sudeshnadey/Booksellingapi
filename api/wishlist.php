<?php
require_once 'require/header.php';
include 'require/auth-user.php';
include 'require/login-user.php';
include 'require/url.php';

require_once './config/db-connect.php';

try {
    $pdo = createDatabaseConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    http_response_code(500);
    exit;
}

$userId = getUser()->id; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;

    $user_id = $userId;
    $item_id = $data['item_id'];
    $type = $data['type'];

    $sql = "INSERT INTO wishlist (user_id, item_id, type) VALUES (?, ?, ?)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $item_id, $type]);

        http_response_code(201);
        echo json_encode(['message' => 'Wishlist item created successfully']);
        exit();
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}

// Retrieve wishlist items for a user
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $userId;

    $sql = "SELECT * FROM wishlist WHERE user_id = ?";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);

        $wishlistItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($wishlistItems);
        exit();
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}

// Update a wishlist item
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $user_id = $_GET['user_id'];
    $item_id = $_GET['item_id'];

    $data = json_decode(file_get_contents('php://input'), true);
    $new_type = $data['type'];

    $sql = "UPDATE wishlist SET type = ? WHERE user_id = ? AND item_id = ?";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_type, $user_id, $item_id]);

        http_response_code(200);
        echo json_encode(['message' => 'Wishlist item updated successfully']);
        exit();
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}

// Delete a wishlist item
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $user_id = $userId;
    $item_id = $_GET['item_id'];

    $sql = "DELETE FROM wishlist WHERE user_id = ? AND item_id = ?";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $item_id]);

        http_response_code(200);
        echo json_encode(['message' => 'Wishlist item deleted successfully']);
        exit();
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}