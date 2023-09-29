<?php
require_once 'require/header.php';
include 'require/auth-user.php';
require './require/url.php';

require_once './config/db-connect.php';


 function getCategoriesWithBooks($pdo)
    {
        $type = $_GET['type'] ?? '';

        if(empty($type)){
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'type required']);
            exit;
        }
        $query = "SELECT  c.id as category_id,c.name as category_name,c.image as category_image,
         b.*  FROM categories c LEFT JOIN courses b 
         ON c.id = b.category_id WHERE b.lang=:lang AND b.type=:type  ORDER BY b.created_at DESC";

        $lang = $_GET['lang'] ?? 'in';

        $statement = $pdo->prepare($query);
        $statement->bindParam(':lang', $lang);
        $statement->bindParam(':type', $type);
        $statement->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $categories = [];

        foreach ($results as $row) {
            $categoryId = $row['category_id'];

            if (!isset($categories[$categoryId])) {
                $categories[$categoryId] = [
                    'category_id' => $row['category_id'],
                    'category_name' => $row['category_name'],
                    'courses' => []
                ];
            }

     
            if (isset($row['id'])  && count($categories[$categoryId]['courses']) < 5) {

                $q2 = "SELECT * FROM images WHERE type=? AND item_id=?";
                $st = $pdo->prepare($q2);
                $st->execute(['course', $row['id']]);
                $image = $st->fetch(PDO::FETCH_ASSOC);
                $img= $image && $image['name']? imageUrl() . $image['name']:null;


                $categories[$categoryId]['courses'][] = [
                    'course_id' => $row['id'],
                    'name' => $row['name'],
                    'description' => $row['description']??null,
                    'image' =>  $img,

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