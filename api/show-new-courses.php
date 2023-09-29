<?php
require_once 'require/header.php';
include 'require/auth-user.php';
require './require/url.php';

require_once './config/db-connect.php';


 function getNewReleases($pdo)
{

 

$query = "SELECT  c.id as category_id,c.name as category_name,
b.*  FROM categories c LEFT JOIN courses b 
 ON c.id = b.category_id WHERE b.lang=:lang  AND b.type=:type ORDER BY b.created_at DESC";
    $filt = $_GET['filter'] ?? '';

    if (!($filt == 'all')) {
        $query .= ' LIMIT 5';
    }

    $lang = $_GET['lang'] ?? 'in';
    $type = $_GET['type'] ?? '';
    if(empty($type)){
        http_response_code(400);
        echo json_encode(['success'=>false,'message'=>' type required']);
        exit;
    }
    $statement = $pdo->prepare($query);
    $statement->bindParam(':lang', $lang);
    $statement->bindParam(':type', $type);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    $fbooks = array_map(function ($data) use ($pdo) {
    
        $q2 = "SELECT * FROM images WHERE type=? AND item_id=?";
        $st = $pdo->prepare($q2);
        $st->execute(['course', $data['id']]);
        $image = $st->fetch(PDO::FETCH_ASSOC);
        $data['image']= $image && $image['name']? imageUrl() . $image['name']:null;
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