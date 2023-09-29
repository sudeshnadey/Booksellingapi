<?php

require_once 'require/header.php';
require_once './config/db-connect.php';
include 'require/auth-user.php';

try {
    $db = createDatabaseConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('message' => 'Database connection failed.'));
    exit();
}

// CRUD endpoints
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Read about us data
        readAboutUs();
        break;
   
    default:
        http_response_code(405);
        echo json_encode(array('message' => 'Method Not Allowed.'));
        break;
}

// Read about us data
function readAboutUs()
{
    global $db;
    try {
        $stmt = $db->prepare('SELECT * FROM aboutus');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(array('message' => 'About Us data not found.'));
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array('message' => 'Error occurred while reading About Us data.'));
    }
}

// Create about us data
