<?php

require_once 'require/header.php';
require_once 'require/auth-user.php';
require_once 'require/login-user.php';
header('Content-Type: application/json');

// Include the database connection file
require_once 'config/db-connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data from the request
    $adminId = getUser()->id ?? '';
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    // Validate the input data
    if (empty($adminId) || empty($currentPassword) || empty($newPassword)) {
        $response = [
            'success' => false,
            'message' => 'Please provide all required fields.',
        ];
        http_response_code(400);

        echo json_encode($response);
        exit;
    }
    $pdo = createDatabaseConnection();
    // Prepare the SELECT statement to retrieve the admin from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :admin_id");
    $stmt->bindValue(':admin_id', $adminId);
    $stmt->execute();

    // Fetch the admin from the database
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the current password
    if (password_verify($currentPassword, $admin['password'])) {
        // Validate the new password (e.g., minimum length)
        if (strlen($newPassword) < 8) {
            $response = [
                'success' => false,
                'message' => 'New password should be at least 8 characters long.',
            ];
            http_response_code(400);

            echo json_encode($response);
            exit;
        }

        // Generate a new hashed password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Prepare the UPDATE statement to update the admin's password in the database
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :admin_id");
        $stmt->bindValue(':password', $hashedPassword);
        $stmt->bindValue(':admin_id', $adminId);
        $stmt->execute();

        // Return a success response
        $response = [
            'success' => true,
            'message' => 'Password changed successfully.',
        ];
        http_response_code(200);

        echo json_encode($response);
    } else {
        // Return an error response
        $response = [
            'success' => false,
            'message' => 'Invalid current password.',
        ];
        http_response_code(400);
        echo json_encode($response);
    }
}
