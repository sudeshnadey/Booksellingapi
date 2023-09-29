<?php


require_once 'require/header.php';
include 'require/auth-admin.php';
require_once './config/db-connect.php';
require_once './require/image-upload.php';
require_once './require/url.php';


try {
    $pdo = createDatabaseConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle database connection error
    $response = array(
        'error' => 'Database connection failed'
    );
    http_response_code(500);
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize the input data
    $title = $_POST['title'] ?? '';
    $link = $_POST['link'] ?? '';
    $description = $_POST['description'] ?? '';
    $courseId = $_POST['course_id'] ?? '';
    $day_no = $_POST['day_no'] ?? '';

    // Validate the input data
    if (empty($title) || empty($link) || empty($courseId) ||empty($day_no)) {
        $response = array(
            'error' => 'Missing required fields'
        );
        http_response_code(400);
        echo json_encode($response);
        exit;
    }

    // Perform database insertion
    try {
        $stmt = $pdo->prepare('INSERT INTO videos (course_id, title, link, description,day_no) VALUES (?, ?, ?, ?,?)');
        $stmt->execute([$courseId, $title, $link, $description,$day_no]);

        $response = array(
            'message' => 'Video created successfully'
        );
        echo json_encode($response);
    } catch (PDOException $e) {
        // Handle database insert error
        $response = array(
            'error' => 'Failed to create the video'
        );
        http_response_code(500);
        echo json_encode($response);
    }
}

// Update an existing video
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['id'])) {
    $videoId = $_GET['id'];

    $data=json_decode(file_get_contents('php://input'),true);

    // Validate and sanitize the input data
    $title = $data['title'] ?? '';
    $link = $data['link'] ?? '';
    $description = $data['description'] ?? '';
    $courseId = $data['course_id'] ?? '';
    $day_no = $data['day_no'] ?? '';

    
    if (empty($title) || empty($link) || empty($courseId) ||empty($day_no)) {
        $response = array(
            'error' => 'Missing required fields'
        );
        http_response_code(400);
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare('UPDATE videos SET course_id=?, title=?, link=?, description=?,day_no=? WHERE id=?');
        $stmt->execute([$courseId, $title, $link, $description, $day_no,$videoId]);

        $response = array(
            'message' => 'Video updated successfully'
        );
        echo json_encode($response);
    } catch (PDOException $e) {
        // Handle database update error
        $response = array(
            'error' => 'Failed to update the video'
        );
        http_response_code(500);
        echo json_encode($response);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $videoId = $_GET['id'];

    try {
        $stmt = $pdo->prepare('SELECT * FROM videos WHERE id = ?');
        $stmt->execute([$videoId]);
        $video = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($video) {
            echo json_encode($video);
        } else {
            $response = array(
                'error' => 'Video not found'
            );
            http_response_code(404);
            echo json_encode($response);
        }
    } catch (PDOException $e) {
        // Handle database query error
        $response = array(
            'error' => 'Failed to fetch the video'
        );
        http_response_code(500);
        echo json_encode($response);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $videoId = $_GET['id'];

    try {
        $stmt = $pdo->prepare('DELETE FROM videos WHERE id = ?');
        $stmt->execute([$videoId]);

        $response = array(
            'message' => 'Video deleted successfully'
        );
        echo json_encode($response);
    } catch (PDOException $e) {
        // Handle database delete error
        $response = array(
            'error' => 'Failed to delete the video'
        );
        http_response_code(500);
        echo json_encode($response);
    }
}