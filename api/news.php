<?php

use Ramsey\Uuid\Uuid;

require_once 'require/header.php';
include 'require/auth-admin.php';
require_once './config/db-connect.php';
require_once './require/image-upload.php';
try {
  $pdo = createDatabaseConnection();
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

// Set the response content type to JSON
header('Content-Type: application/json');

// Handle the API requests
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestMethod) {
  case 'POST':
    createNewsItem();
    break;
  case 'GET':
    if (isset($_GET['id'])) {
      getNewsItem($_GET['id']);
    } else {
      getAllNewsItems();
    }
    break;
  case 'PUT':
  case 'PATCH':
   // updateNewsItem();
    break;
  case 'DELETE':
    deleteNewsItem();
    break;
  default:
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => 'Invalid request method']);
    break;
}

// Create a new news item
function createNewsItem() {

  if($_POST['update']){
    updateNewsItem();
    return;
  }
  global $pdo;

  $title = $_POST['title']??'';
  $description = $_POST['description']??'';
  $posted_by = $_POST['added_by']??'';
  $category_id = $_POST['category_id']??'';
  $lang = $_POST['lang'] ?? 'in'; 

  // Validate input
  if (empty($title) || empty($description) ||empty($category_id)) {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'Title and description are required']);
    return;
  }
  
  // Validate file upload
  if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'Photo upload failed']);
    return;
  }
  
  $photo = $_FILES['photo'];

  $originalFileName = $photo['name'];
  $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
  
  $fileName = Uuid::uuid4()->toString() . '.' . $fileExtension;
  $fileTmpPath = $photo['tmp_name'];
  $destination = 'images/' . $fileName;
  move_uploaded_file($fileTmpPath, $destination);
  
  $sql = "INSERT INTO news (title, description, photo, created_at, added_by,category_id,lang) 
  VALUES (?, ?, ?, NOW(), ?,?,?)";
  
  try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $description, $fileName, $posted_by,$category_id,$lang]);
    $lastInsertedId = $pdo->lastInsertId();
    $response = ['id' => $lastInsertedId, 'message' => 'News item created successfully'];
    echo json_encode($response);
  } catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
  }
}

// Get a specific news item by ID
function getNewsItem($id) {
  global $pdo;

  $sql = "SELECT * FROM news WHERE id = ?";
  
  try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $newsItem = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($newsItem) {
      echo json_encode($newsItem);
    } else {
      http_response_code(404); // Not Found
      echo json_encode(['message' => 'News item not found']);
    }
  } catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
  }
}

// Get all news items
function getAllNewsItems() {
  global $pdo;

  $sql = "SELECT * FROM news";
  
  try {
    $stmt = $pdo->query($sql);
    $newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $news = array_map(function($data) {
      $data['image'] = !empty($data['image']) ? imageUrl() .$data['image'] : null;
      return $data;
    },$newsItems);
    echo json_encode($news);
  } catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
  }
}

// Update a news item
function updateNewsItem() {
  global $pdo;

  $title = $_POST['title'] ?? '';
  $description = $_POST['description'] ?? '';
  $posted_by = $_POST['added_by'] ?? '';
  $category_id = $_POST['category_id'] ?? '';
  $news_id = $_POST['news_id'] ?? null; 
  $lang = $_POST['lang'] ?? 'in'; 

  // Validate input
  if (empty($title) || empty($description) || empty($category_id) ||  empty($news_id)) {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'Title, description, and category ID are required']);
    return;
  }

  // Check if an image is uploaded
  $image_path = null;
  if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $photo = $_FILES['photo'];

    $originalFileName = $photo['name'];
    $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);

    $fileName = Uuid::uuid4()->toString() . '.' . $fileExtension;
    $fileTmpPath = $photo['tmp_name'];
    $image_path = 'images/' . $fileName;
    move_uploaded_file($fileTmpPath, $image_path);
  }

  // Prepare the update query
  $sql = "UPDATE news SET title = ?, description = ?, added_by = ?, category_id = ?,lang=?";
  $params = [$title, $description, $posted_by, $category_id,$lang];

  // Append the photo field to the query and parameters if an image is uploaded
  if ($image_path) {
    $sql .= ", photo = ?";
    $params[] = $image_path;
  }

  $sql .= " WHERE id = ?";

  // Append the news ID to the parameters
  $params[] = $news_id;

  try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $response = ['id' => $news_id, 'message' => 'News item updated successfully'];
    echo json_encode($response);
  } catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
  }
}

// Delete a news item
function deleteNewsItem() {
  global $pdo;

  $id = $_GET['id'];
  
  $sql = "DELETE FROM news WHERE id = ?";
  
  try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    echo json_encode(['message' => 'News item deleted successfully']);
  } catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
  }
}
?>