<?php


require_once 'require/header.php';
include 'require/auth-admin.php';
require_once './config/db-connect.php';
require_once './require/image-upload.php';
require_once './require/url.php';

// ...

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

// Create a new test
if ($_SERVER['REQUEST_METHOD'] === 'POST'  && !isset($_GET['id'])) {
    // Validate and sanitize the input data
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $courseId = $_POST['course_id'] ?? '';
    $day_no = $_POST['day_no'] ?? '';
    $uploadedFile = $_FILES['content']??'';

    // Validate the input data
    if (empty($title)  || empty($courseId) || empty($uploadedFile) || $day_no) {
        $response = array(
            'error' => 'Missing required fields'
        );
        http_response_code(400);
        echo json_encode($response);
        exit;
    }


    // Perform database insertion
    try {

        $uploadedFile = $_FILES['content'];

        // Generate a unique file name
        $fileName = uniqid() . '_' . $uploadedFile['name'];

        // Specify the destination folder to store the uploaded file
        $destination = 'images/' . $fileName;

        // Move the uploaded file to the destination folder
        move_uploaded_file($uploadedFile['tmp_name'], $destination);

        $stmt = $pdo->prepare('INSERT INTO tests (course_id, title, link, description,day_no) VALUES (?, ?, ?, ?,?)');
        $stmt->execute([$courseId, $title, $fileName, $description,$day_no]);

        $response = array(
            'message' => 'test created successfully'
        );
        echo json_encode($response);
    } catch (PDOException $e) {
        // Handle database insert error
        $response = array(
            'error' => 'Failed to create the test'
        );
        http_response_code(500);
        echo json_encode($response);
    }
}

// Update an existing test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $pdfId = $_GET['id'];

    // Validate and sanitize the input data
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $courseId = $_POST['course_id'] ?? '';
    $day_no = $_POST['day_no'] ?? '';
    $uploadedFile = $_FILES['content'] ?? null;

    // Validate the input data
    if (empty($title) || empty($day_no) || empty($courseId) ) {
        $response = array(
            'error' => 'Missing required fields'
        );
        http_response_code(400);
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare('UPDATE tests SET course_id=?, title=?, description=?, day_no=? WHERE id=?');
        $stmt->execute([$courseId, $title, $description, $day_no, $pdfId]);

        if (is_array($uploadedFile) && isset($uploadedFile['size']) && $uploadedFile['size'] > 0) {            // Generate a unique file name
            $fileName = uniqid() . '_' . $uploadedFile['name'];

            // Specify the destination folder to store the uploaded file
            $destination = 'images/' . $fileName;

            // Move the uploaded file to the destination folder
            move_uploaded_file($uploadedFile['tmp_name'], $destination);

            // Update the link field in the database
            $stmt = $pdo->prepare('UPDATE pdfs SET link=? WHERE id=?');
            $stmt->execute([$fileName, $pdfId]);
        }

        $response = array(
            'message' => 'PDF updated successfully'
        );
        echo json_encode($response);
    } catch (PDOException $e) {
        // Handle database update error
        $response = array(
            'error' => 'Failed to update the PDF'
        );
        http_response_code(500);
        echo json_encode($response);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $testId = $_GET['id'];

    try {
        $stmt = $pdo->prepare('SELECT * FROM tests WHERE id = ?');
        $stmt->execute([$testId]);
        $test = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($test) {
            echo json_encode($test);
        } else {
            $response = array(
                'error' => 'test not found'
            );
            http_response_code(404);
            echo json_encode($response);
        }
    } catch (PDOException $e) {
        // Handle database query error
        $response = array(
            'error' => 'Failed to fetch the test'
        );
        http_response_code(500);
        echo json_encode($response);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $testId = $_GET['id'];

    try {
        $stmt = $pdo->prepare('DELETE FROM tests WHERE id = ?');
        $stmt->execute([$testId]);

        $response = array(
            'message' => 'test deleted successfully'
        );
        echo json_encode($response);
    } catch (PDOException $e) {
        // Handle database delete error
        $response = array(
            'error' => 'Failed to delete the test'
        );
        http_response_code(500);
        echo json_encode($response);
    }
}
