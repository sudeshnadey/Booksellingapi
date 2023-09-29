<?php
require_once 'require/header.php';
include 'require/auth-user.php';
require './require/url.php';

require_once './config/db-connect.php';



 function getAllNewsByCategory($pdo)
    {

        $type = $_GET['type'] ?? '';
        $cate_id = $_GET['category'];

        if(empty($type) || empty($cate_id)){
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'type and category required']);
            exit;
        }
        $lang = $_GET['lang'] ?? 'in';
        if ($cate_id) {


            $query = "SELECT * FROM courses WHERE category_id = :categoryId AND lang=:lang AND type=:type ORDER BY created_at DESC ";

            $statement = $pdo->prepare($query);
            $statement->bindParam(':categoryId', $cate_id);
            $statement->bindParam(':lang', $lang);
            $statement->bindParam(':type', $type);
            $statement->execute();
            $courses = $statement->fetchAll(PDO::FETCH_ASSOC);

            $fcourses = array_map(function ($data) use ($pdo) {
     
                $q2 = "SELECT * FROM images WHERE type=? AND item_id=?";
                $st = $pdo->prepare($q2);
                $st->execute(['course', $data['id']]);
                $image = $st->fetch(PDO::FETCH_ASSOC);
                $data['image']= $image && $image['name']? imageUrl() . $image['name']:null;

                return $data;
            }, $courses);

            return $fcourses;
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