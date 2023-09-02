<?php
require '../models/SubCategory.php';
require_once '../config/db-connect.php';

class SubCategoryController
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
            if (!isset($_POST['categoryId']) || empty($_POST['categoryId'])) {
                // Handle invalid name (name is missing or empty)
                $response = array(
                    'status' => 'error',
                    'message' => 'Invalid Category Id'
                );
                echo json_encode($response);
                return;
            }
            $name = $_POST['name'];
            $categoryId = $_POST['categoryId'];
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


            $image_path = '../images/' . $fileName;

            move_uploaded_file($fileTmpPath, $image_path);

            $category = new SubCategory($name, $fileName, $description,$categoryId);
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

        if (!isset($_POST['name']) || empty($_POST['name'])) {
            // Handle invalid name (name is missing or empty)
            $response = array(
                'status' => 'error',
                'message' => 'Invalid name'
            );
            echo json_encode($response);
            return;
        }
        if (!isset($_POST['categoryId']) || empty($_POST['categoryId'])) {
            // Handle invalid name (name is missing or empty)
            $response = array(
                'status' => 'error',
                'message' => 'Invalid Category Id'
            );
            echo json_encode($response);
            return;
        }
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            // Handle invalid name (name is missing or empty)
            $response = array(
                'status' => 'error',
                'message' => 'Invalid Category Id'
            );
            echo json_encode($response);
            return;
        }

        $name = $_POST['name'];
        $description = $_POST['description'];
        $id = $_POST['id'];
        $categoryId = $_POST['categoryId'];

        // Retrieve the uploaded file
        $file = $_FILES['image'];
        // if ($requestData) {
        if (!isset($name)) {
            echo 'Name Field required';
            return;
        }

        $fileName = $file['name'];
        $fileTmpPath = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];


        $image_path = '../images/' . $fileName;

        move_uploaded_file($fileTmpPath, $image_path);
        $category = SubCategory::getById($id, $pdo);

        // $category = new category($name, $fileName, $description);
        if ($category) {
            $category->name = $name;
            $category->image = $fileName;
            $category->categoryId = $categoryId;
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

        $categoryId = $_POST['categoryId'];

        $category = SubCategory::getById($categoryId, $pdo);
        // echo json_encode($categoryId);
        if ($category && $category->delete($categoryId)) {

            http_response_code(200);
            echo json_encode(array('status' => 'success', 'data' => 'successfully deleted'));
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 'failed', 'data' => 'error while deleted'));
        }
    }

    public function showCategorys()
    {
        try {
            $pdo = createDatabaseConnection();

            $categorys = SubCategory::getAll($pdo);

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
            echo json_encode(['error' => 'An error occurred while fetching banners.']);
        }
    }

    public function showCategorysToUsers()
    {
        try {
            $pdo = createDatabaseConnection();

            $categorys = SubCategory::getAll($pdo);

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
