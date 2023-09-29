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

// Create a new pdf
if ($_SERVER['REQUEST_METHOD'] === 'POST'  && !isset($_GET['id'])) {
    // Validate and sanitize the input data
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $courseId = $_POST['course_id'] ?? '';
    $day_no = $_POST['day_no'] ?? '';
    $uploadedFile = $_FILES['content']??null;

    // Validate the input data
    if (empty($title)  || empty($courseId) || empty($uploadedFile) || empty($day_no)) {
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

        $stmt = $pdo->prepare('INSERT INTO pdfs (course_id, title, link, description,day_no) VALUES (?, ?, ?, ?,?)');
        $stmt->execute([$courseId, $title, $fileName, $description,$day_no]);

        $response = array(
            'message' => 'pdf created successfully'
        );
        echo json_encode($response);
    } catch (PDOException $e) {
        // Handle database insert error
        $response = array(
            'error' => 'Failed to create the pdf'
        );
        http_response_code(500);
        echo json_encode($response);
    }
}

// ...

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $pdfId = $_GET['id'];

    // Validate and sanitize the input data
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $courseId = $_POST['course_id'] ?? '';
    $day_no = $_POST['day_no'] ?? '';
    $uploadedFile = $_FILES['content'] ?? null;

    // Validate the input data
    if (empty($title) || empty($day_no) || empty($courseId)) {
        $response = array(
            'error' => 'Missing required fields'
        );
        http_response_code(400);
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare('UPDATE pdfs SET course_id=?, title=?, description=?, day_no=? WHERE id=?');
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
    $pdfId = $_GET['id'];

    try {
        $stmt = $pdo->prepare('SELECT * FROM pdfs WHERE id = ?');
        $stmt->execute([$pdfId]);
        $pdf = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pdf) {
            echo json_encode($pdf);
        } else {
            $response = array(
                'error' => 'pdf not found'
            );
            http_response_code(404);
            echo json_encode($response);
        }
    } catch (PDOException $e) {
        // Handle database query error
        $response = array(
            'error' => 'Failed to fetch the pdf'
        );
        http_response_code(500);
        echo json_encode($response);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $pdfId = $_GET['id'];

    try {
        $stmt = $pdo->prepare('DELETE FROM pdfs WHERE id = ?');
        $stmt->execute([$pdfId]);

        $response = array(
            'message' => 'pdf deleted successfully'
        );
        echo json_encode($response);
    } catch (PDOException $e) {
        // Handle database delete error
        $response = array(
            'error' => 'Failed to delete the pdf'
        );
        http_response_code(500);
        echo json_encode($response);
    }
}
