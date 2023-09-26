<?php
require_once 'require/header.php';
include 'require/auth-user.php';
require_once './config/db-connect.php';

function addnewComment($comment, $user_name, $news_id) {
  

    try {
        $pdo = createDatabaseConnection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $type='book';
        // Insert the user rating into the database
        $query = "INSERT INTO news_comment (comment, user_name, news_id, created_at)
                  VALUES (:comment, :user_name, :news_id, NOW())";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':comment', $comment);
        $statement->bindParam(':user_name', $user_name);
        $statement->bindParam(':news_id', $news_id);
    
        $statement->execute();

        // Check if the rating was successfully inserted
        if ($statement->rowCount() > 0) {
            $response = [
                'success' => true,
                'message' => 'Comment added successfully.'
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
addnewComment($_POST['comment'], $_POST['user_name'], $_POST['news_id']);