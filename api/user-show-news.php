<?php
require_once 'require/header.php';
include 'require/auth-user.php';
require './require/url.php';

require_once './config/db-connect.php';



 function getAllNewsByCategory($pdo)
    {
        $cate_id = $_GET['category'];
        $lang = $_GET['lang'] ?? 'in';
        if ($cate_id) {


            $query = "SELECT id,title,description,created_at,added_by,photo as image,lang FROM news WHERE category_id = :categoryId AND lang=:lang ORDER BY created_at DESC ";

            $statement = $pdo->prepare($query);
            $statement->bindParam(':categoryId', $cate_id);
            $statement->bindParam(':lang', $lang);
            $statement->execute();
            $books = $statement->fetchAll(PDO::FETCH_ASSOC);

            $fbooks = array_map(function ($data) use ($pdo) {
     
                $data['image'] = !empty($data['image']) ? imageUrl().$data['image'] : null;

                return $data;
            }, $books);

            return $fbooks;
        } else {
            return [];
        }
    }

    try{
        $pdo=createDatabaseConnection();
       $news= getAllNewsByCategory($pdo);
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