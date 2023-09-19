<?php
require_once 'require/header.php';
include 'require/auth-user.php';
require_once './config/db-connect.php';

function addBookRating($bookId, $userId, $rating,$comment) {
  

    try {
        $pdo = createDatabaseConnection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $type='book';
        // Insert the user rating into the database
        $query = "INSERT INTO reviews (item_id, user_id, rating,comment, created_at, type)
                  VALUES (:bookId, :userId, :rating,:comment, NOW(), :type)";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':bookId', $bookId);
        $statement->bindParam(':userId', $userId);
        $statement->bindParam(':rating', $rating);
        $statement->bindParam(':comment', $comment);
        $statement->bindParam(':type', $type);
        $statement->execute();

        // Check if the rating was successfully inserted
        if ($statement->rowCount() > 0) {
            $response = [
                'success' => true,
                'message' => 'Rating added successfully.'
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to add rating.'
            ];
        }
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Usage example: call the function with the required parameters
addBookRating($_POST['book_id'], $_POST['user_id'], $_POST['rating'],$_POST['comment']??'');