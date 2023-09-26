<?php
require_once 'require/header.php';
include 'require/auth-user.php';
require './require/url.php';

require_once './config/db-connect.php';



 function getNewsById($pdo)
    {
        $news_id = $_GET['id'];
        if ($news_id) {


            $query = "SELECT id,title,description,created_at,added_by,photo as image FROM news WHERE id = :id ORDER BY created_at DESC ";

            $statement = $pdo->prepare($query);
            $statement->bindParam(':id', $news_id);
            $statement->execute();
            $book = $statement->fetch(PDO::FETCH_ASSOC);
            $query2 = "SELECT id,user_name,comment,created_at FROM news_comment WHERE news_id = :id ORDER BY created_at DESC ";
            $statement = $pdo->prepare($query2);
            $statement->bindParam(':id', $news_id);

            $statement->execute();
            $comments = $statement->fetchAll(PDO::FETCH_ASSOC);

           
            if($book){
                $book['image'] = !empty($book['image']) ? imageUrl().$book['image'] : null;
                $book['comments'] = $comments;


            return $book;
            }else{
                return [];
            }
         
        } else {
            return [];
        }
    }

    try{
        $pdo=createDatabaseConnection();
       $news= getNewsById($pdo);
    $jsonData = json_encode($news);

    http_response_code(200);
    header('Content-Type: application/json');

    // Output the JSON data
    echo $jsonData;
} catch (PDOException $e) {
    // Example: Logging the error
    error_log('Error fetching categories: ' . $e->getMessage());

    // Return an error response
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'An error occurred while fetching categories.'.$e->getMessage()]);
}