
<?php
require_once 'require/header.php';
include 'require/auth-user.php';
require_once './config/db-connect.php';

try {
    $db = createDatabaseConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    http_response_code(500);
    exit;
}

// Handle the API requests

// Authenticate user (Example: using a user ID)
$userId = $_GET['user_id']; // Retrieve user ID from the request. You may use a proper authentication mechanism.

// Add a book to the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $bookId = $data['book_id'];
    $quantity = $data['quantity'];
    $user_id = $data['user_id'];

    $stmt = $db->prepare('INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $bookId, $quantity]);

    echo json_encode(['message' => 'Book added to cart successfully']);
    http_response_code(201);
    exit;
}

// Get the cart items
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $type = 'book';
    $stmt = $db->prepare('SELECT c.*, b.title, b.mrp as price FROM cart c INNER JOIN books b ON c.item_id = b.id WHERE c.user_id = ? AND type=? ');
    $stmt->execute([$userId, $type]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($cartItems);
    // $totalPrice = 0;
    // foreach ($cartItems as $item) {
    //     $totalPrice += $item['price'] * $item['quantity'];
    // }
    http_response_code(200);
    exit;
}

// Update the quantity of a book in the cart
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $cartItemId = $_GET['id'];
    $newQuantity = $data['quantity'];

    $stmt = $db->prepare('UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$newQuantity, $cartItemId, $userId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'Cart item updated successfully']);
        http_response_code(200);
    } else {
        echo json_encode(['error' => 'Cart item not found or does not belong to the user']);
        http_response_code(404);
    }
    exit;
}

// Remove a book from the cart
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $cartItemId = $_GET['id'];

    $stmt = $db->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?');
    $stmt->execute([$cartItemId, $userId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'Book removed from cart successfully']);
        http_response_code(200);
    } else {
        echo json_encode(['error' => 'Cart item not found or does not belong to the user']);
        http_response_code(404);
    }
    exit;
}

// If no matching route is found, return a 404 response
echo json_encode(['error' => '404 Not Found']);
http_response_code(404);
