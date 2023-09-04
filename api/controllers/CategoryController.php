<?php
require './models/Category.php';
require_once './config/db-connect.php';

class CategoryController
{


    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    public function addCategory()
    {

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validate name
            if (!isset($_POST['name']) || empty($_POST['name'])) {
                // Handle invalid name (name is missing or empty)
                $response = array(
                    'status' => 'error',
                    'message' => 'Invalid name'
                );
                echo json_encode($response);
                return;
            }
            $name = $_POST['name'];
            $description = $_POST['description'];

            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                // Handle invalid file upload (file is missing or has an error)
                $response = array(
                    'status' => 'error',
                    'message' => 'Invalid file upload'
                );
                echo json_encode($response);
                return;
            }
            // Retrieve the uploaded file
            $file = $_FILES['image'];


            $fileName = $file['name'];
            $fileTmpPath = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];


            $image_path = 'images/' . $fileName;

            move_uploaded_file($fileTmpPath, $image_path);

            $category = new Category($name, $fileName, $description);
            $category->save();
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'data' => $category));
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 'failed', 'message' => 'please enter data to store'));
        }
    }


    public function editCategory()
    {
        $pdo = createDatabaseConnection();

        $name = $_POST['name'];
        $description = $_POST['description'];
        $categoryId = $_POST['categoryId'];

        // Retrieve the uploaded file
        $file = $_FILES['image'] ?? null;
        // if ($requestData) {
        if (!isset($name)) {
            echo 'Name Field required';
            return;
        }

        $category = Category::getById($categoryId, $pdo);

        if ($file != null) {
            $fileName = $file['name'];
            $fileTmpPath = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];
    
    
            $image_path = 'images/' . $fileName;
    
            move_uploaded_file($fileTmpPath, $image_path);
            $category->image = $fileName;

        }else{
            $category->image = null;

        }
     

        // $category = new category($name, $fileName, $description);
        if ($category) {
            $category->id = $categoryId;
            $category->name = $name;
            $category->description = $description;
            $category->save();

            http_response_code(200);
            echo json_encode(array('status' => 'success', 'data' => $category));
        } else {
            http_response_code(404);
            echo json_encode(array('status' => 'failed', 'data' => 'resource not found'));
        }
    }

    public function deleteCategory()
    {
        $pdo = createDatabaseConnection();

        $categoryId = $_POST['categoryId'] ?? null;

        $category = Category::getById($categoryId, $pdo);
        // echo json_encode($categoryId);
        if ($category && $category->delete($categoryId)) {

            http_response_code(200);
            echo json_encode(array('status' => 'success', 'data' => 'successfully deleted'));
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 'failed', 'data' => 'error while deleting'));
        }
    }

    public function showCategorys()
    {
        try {
            $pdo = createDatabaseConnection();

            $categorys = Category::getAll($pdo);

            // Convert the banner objects to JSON format
            $jsonData = json_encode($categorys);

            http_response_code(200);
            header('Content-Type: application/json');

            // Output the JSON data
            echo $jsonData;
        } catch (PDOException $e) {
            // Example: Logging the error
            error_log('Error fetching categories: ' . $e->getMessage());

            // Return an error response
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'An error occurred while fetching categories.']);
        }
    }

    public function showCategorysToUsers()
    {
        try {
            $pdo = createDatabaseConnection();

            $categorys = Category::getAll($pdo);

            // Convert the banner objects to JSON format
            $jsonData = json_encode($categorys);

            http_response_code(200);
            header('Content-Type: application/json');

            // Output the JSON data
            echo $jsonData;
        } catch (PDOException $e) {
            // Example: Logging the error
            error_log('Error fetching categories: ' . $e->getMessage());

            // Return an error response
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'An error occurred while fetching categories.']);
        }
    }
}
