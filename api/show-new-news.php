<?php
require_once 'require/header.php';
include 'require/auth-user.php';
require './require/url.php';

require_once './config/db-connect.php';


 function getNewReleases($pdo)
{

 

$query = "SELECT  c.id as category_id,c.name as category_name,c.image as category_image,
b.photo as news_image, b.id as
 news_id,b.title as title,b.description  FROM categories c LEFT JOIN news b 
 ON c.id = b.category_id WHERE b.lang=:lang  ORDER BY b.created_at DESC";
    $filt = $_GET['filter'] ?? '';

    if (!($filt == 'all')) {
        $query .= ' LIMIT 5';
    }

    $lang = $_GET['lang'] ?? 'in';

    $statement = $pdo->prepare($query);
    $statement->bindParam(':lang', $lang);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    $fbooks = array_map(function ($data) use ($pdo) {
    

        $data['image'] = !empty($data['news_image']) ? imageUrl().$data['news_image'] : null;
        $data['comments'] = [];

        return $data;
    }, $results);
    return $fbooks;
}

try{
    $pdo=createDatabaseConnection();
   $news= getNewReleases($pdo);
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