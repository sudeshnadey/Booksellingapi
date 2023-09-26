
<?php
require_once 'require/header.php';
include 'require/auth-user.php';
include 'require/login-user.php';
include 'require/url.php';

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

// Add a book to the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $data = json_decode(file_get_contents('php://input'), true);
    $data = $_POST;
    $bookId = $data['item_id'];
    $quantity = $data['quantity'];
    $type = $data['type'] ?? 'book';

    // Check if the item already exists in the cart for the user
    $stmt = $db->prepare('SELECT * FROM cart WHERE user_id = ? AND item_id = ? AND type = ?');
    $stmt->execute([$userId, $bookId, $type]);
    $existingItem = $stmt->fetch();

    if ($existingItem) {
        // Item already exists, update the quantity by adding the new quantity to the previous quantity
        $newQuantity = $existingItem['quantity'] + $quantity;
        $stmt = $db->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
        $stmt->execute([$newQuantity, $existingItem['id']]);
        echo json_encode(['message' => 'Item quantity updated successfully']);
        http_response_code(200);
    } else {
        // Item doesn't exist, insert a new record
        $stmt = $db->prepare('INSERT INTO cart (user_id, item_id, quantity, type) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $bookId, $quantity, $type]);
        echo json_encode(['message' => 'Item added to cart successfully']);
        http_response_code(200);
    }

    exit;
}

// Get the cart items
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $stmt = $db->prepare('SELECT c.*, b.name,b.id as book_id, b.mrp as price, (c.quantity * b.mrp) as total_price FROM cart c INNER JOIN books b ON c.item_id = b.id WHERE c.user_id = ?');
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalPrice = 0;
    foreach ($cartItems as &$item) {
        $totalPrice += $item['total_price'];
    }

    $fbooks = array_map(function ($data) use ($db) {
        $q2 = "SELECT * FROM images WHERE type=? AND item_id=?";
        $st = $db->prepare($q2);
        $st->execute(['book', $data["book_id"]]);
        $images = $st->fetchAll(PDO::FETCH_ASSOC);

        $images = array_map(function ($image) {
            return imageUrl() . $image['name'];
        }, $images);

        $data['image'] = !empty($images) ? $images[0] : null;
     
        return $data;
    }, $cartItems);
    $response = [
        'cartItems' => $fbooks,
        'totalPrice' => $totalPrice
    ];

    echo json_encode($response);
    http_response_code(200);
    exit;
}

// Update the quantity of a book in the cart
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $cartItemId = $data['id'];
    $newQuantity = $data['quantity'];

    $stmt = $db->prepare('UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$newQuantity, $cartItemId, $userId]);

    if ($stmt->rowCount() >= 0) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Cart item updated successfully']);
            http_response_code(200);
        } else {
            echo json_encode(['message' => 'No changes made to the cart item']);
            http_response_code(200);
        }
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
