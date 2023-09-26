<?php

require_once 'require/header.php';
require_once './config/db-connect.php';
include 'require/auth-admin.php';

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
    case 'POST':
        // Create about us data
        createAboutUs();
        break;
    case 'PUT':
        // Update about us data
        updateAboutUs();
        break;
    case 'DELETE':
        // Delete about us data
        deleteAboutUs();
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
function createAboutUs()
{
    global $db;
    $data = json_decode(file_get_contents('php://input'), true);
    $about = $data['about'] ?? '';
    $terms = $data['terms'] ?? '';

    try {

        $stmt = $db->prepare('DELETE FROM aboutus');
        $stmt->execute();
        
        $stmt = $db->prepare('INSERT INTO aboutus (about, terms) VALUES (:about, :terms)');
        $stmt->bindParam(':about', $about);
        $stmt->bindParam(':terms', $terms);
        $stmt->execute();

        $id = $db->lastInsertId();
        http_response_code(201);
        echo json_encode(array('id' => $id, 'message' => 'About Us data created.'));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array('message' => 'Error occurred while creating About Us data.'));
    }
}

// Update about us data
function updateAboutUs()
{
    global $db;
    $data = json_decode(file_get_contents('php://input'), true);
    $about = $data['about'] ?? '';
    $terms = $data['terms'] ?? '';

    try {
        $stmt = $db->prepare('UPDATE aboutus SET about = :about, terms = :terms');
        $stmt->bindParam(':about', $about);
        $stmt->bindParam(':terms', $terms);
        $stmt->execute();

        http_response_code(200);
        echo json_encode(array('message' => 'About Us data updated.'));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array('message' => 'Error occurred while updating About Us data.'));
    }
}

// Delete about us data
function deleteAboutUs()
{
    global $db;
    try {
        $stmt = $db->prepare('DELETE FROM aboutus');
        $stmt->execute();

        http_response_code(200);
        echo json_encode(array('message' => 'About Us data deleted.'));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array('message' => 'Error occurred while deleting About Us data.'));
    }
}