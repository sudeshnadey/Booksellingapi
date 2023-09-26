<?php
require_once 'require/header.php';
include 'require/auth-user.php';
require './require/url.php';

require_once './config/db-connect.php';


 function getCategoriesWithBooks($pdo)
    {
        $query = "SELECT  c.id as category_id,c.name as category_name,c.image as category_image,
        b.photo as news_image, b.id as
         news_id,b.title as title,b.description  FROM categories c LEFT JOIN news b 
         ON c.id = b.category_id WHERE b.lang=:lang  ORDER BY b.created_at DESC";

        $lang = $_GET['lang'] ?? 'in';

        $statement = $pdo->prepare($query);
        $statement->bindParam(':lang', $lang);
        $statement->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $categories = [];

        foreach ($results as $row) {
            $categoryId = $row['category_id'];

            if (!isset($categories[$categoryId])) {
                $categories[$categoryId] = [
                    'category_id' => $row['category_id'],
                    'category_name' => $row['category_name'],
                    'image' =>  $row['category_image'] ? imageUrl() . $row['category_image'] : null,
                    'news' => []
                ];
            }

     
            if (isset($row['news_id'])  && count($categories[$categoryId]['news']) < 5) {
                $categories[$categoryId]['books'][] = [
                    'news_id' => $row['news_id'],
                    'title' => $row['title'],
                    'description' => $row['description']??null,
                    'image' => $row['news_image'] ?? null,
                    'comments' => []

                    // Add any other book properties you want to include
                ];
            }
        }

        return array_values($categories);
    }


    try{
        $pdo=createDatabaseConnection();
       $news= getCategoriesWithBooks($pdo);
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