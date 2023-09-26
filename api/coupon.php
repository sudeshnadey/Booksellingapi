<?php

require_once 'require/header.php';
require_once './config/db-connect.php';
include 'require/auth-admin.php';

try {
    $pdo = createDatabaseConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('message' => 'Database connection failed.'));
    exit();
}

header('Content-Type: application/json');

// API endpoints
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Read operation - Retrieve all coupons or a specific coupon
    if (!empty($_GET['id'])) {
        $id = $_GET['id'];
        $coupon = getCoupon($pdo, $id);
        if ($coupon) {
            http_response_code(200);
            echo json_encode($coupon);
        } else {
            http_response_code(404);
            echo json_encode(array('error' => 'Coupon not found.'));
        }
    } else {
        $coupons = getAllCoupons($pdo);
        http_response_code(200);
        echo json_encode($coupons);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $data = $_POST;
        $coupon = createCoupon($pdo, $data);
        if ($coupon) {
            http_response_code(201);
            echo json_encode($coupon);
        } else {
            http_response_code(500);
            echo json_encode(array('error' => 'Failed to create coupon.'));
        } //code...
    } catch (\Throwable $th) {
        http_response_code(500);
        echo json_encode(array('error' => 'Failed to create coupon.' . $th));
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Update operation - Update an existing coupon
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $coupon = updateCoupon($pdo, $id, $data);
        if ($coupon) {
            http_response_code(200);
            echo json_encode($coupon);
        } else {
            http_response_code(404);
            echo json_encode(array('error' => 'Coupon not found.'));
        }
    } catch (\Throwable $th) {

        http_response_code(404);
        echo json_encode(array('error' => 'Coupon not found.'.$th));
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Delete operation - Delete a coupon
    $id = $_GET['id'];
    $deleted = deleteCoupon($pdo, $id);
    if ($deleted) {
        http_response_code(200);
        echo json_encode(array('success' => true));
    } else {
        http_response_code(404);
        echo json_encode(array('error' => 'Coupon not found.'));
    }
} else {
    // Invalid request method
    http_response_code(405);
    echo json_encode(array('error' => 'Method Not Allowed'));
}

// Function to retrieve all coupons
function getAllCoupons($pdo)
{
    $query = "SELECT * FROM coupons";
    $stmt = $pdo->query($query);
    $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $coupons;
}

// Function to retrieve a specific coupon
function getCoupon($pdo, $id)
{
    $query = "SELECT * FROM coupons WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
    return $coupon;
}

// Function to create a new coupon
function createCoupon($pdo, $data)
{
    $query = "INSERT INTO coupons (code, type, value, valid_from, valid_until, minimum_amount) VALUES (:code, :type, :value, :valid_from, :valid_until, :minimum_amount)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':code', $data['code'], PDO::PARAM_STR);
    $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
    $stmt->bindParam(':value', $data['value'], PDO::PARAM_STR);
    $stmt->bindParam(':valid_from', $data['valid_from'], PDO::PARAM_STR);
    $stmt->bindParam(':valid_until', $data['valid_until'], PDO::PARAM_STR);
    $stmt->bindParam(':minimum_amount', $data['minimum_amount'], PDO::PARAM_STR);
    $result = $stmt->execute();
    if ($result) {
        $couponId = $pdo->lastInsertId();
        return getCoupon($pdo, $couponId);
    } else {
        return false;
    }
}

function updateCoupon($pdo, $id, $data)
{
    $query = "UPDATE coupons SET code = :code, type = :type, value = :value, valid_from = :valid_from, valid_until = :valid_until, minimum_amount = :minimum_amount WHERE id = :id";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':code', $data['code'], PDO::PARAM_STR);
    $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
    $stmt->bindParam(':value', $data['value'], PDO::PARAM_STR);
    $stmt->bindParam(':valid_from', $data['valid_from']);
    $stmt->bindParam(':valid_until', $data['valid_until']);
    $stmt->bindParam(':minimum_amount', $data['minimum_amount'], PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $result = $stmt->execute();
    if ($result) {
        return getCoupon($pdo, $id);
    } else {
        return false;
    }
}



// Function to delete a coupon
function deleteCoupon($pdo, $id)
{
    $query = "DELETE FROM coupons WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $result = $stmt->execute();
    return $result;
}

// Close the database connection (optional)
$pdo = null;
