<?php
require './models/SubSubCategory.php';
require_once './config/db-connect.php';

class SubSubCategoryController
{



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
                http_response_code(400);

                echo json_encode($response);
                return;
            }
            if (!isset($_POST['subCategoryId']) || empty($_POST['subCategoryId'])) {
                // Handle invalid name (name is missing or empty)
                $response = array(
                    'status' => 'error',
                    'message' => 'Invalid Category Id'
                );
                http_response_code(400);

                echo json_encode($response);
                return;
            }
            $name = $_POST['name'];
            $categoryId = $_POST['subCategoryId'];
            $description = $_POST['description'];
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                // Handle invalid file upload (file is missing or has an error)
                $response = array(
                    'status' => 'error',
                    'message' => 'Invalid file upload'
                );
                http_response_code(400);

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

            $category = new SubSubCategory($name, $fileName, $description,$categoryId);
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
            http_response_code(400);

            echo json_encode($response);
            return;
        }
        if (!isset($_POST['subCategoryId']) || empty($_POST['subCategoryId'])) {
            // Handle invalid name (name is missing or empty)
            $response = array(
                'status' => 'error',
                'message' => 'Invalid Category Id'
            );
            http_response_code(400);

            echo json_encode($response);
            return;
        }
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            // Handle invalid name (name is missing or empty)
            $response = array(
                'status' => 'error',
                'message' => 'Invalid Category Id'
            );
            http_response_code(400);
            echo json_encode($response);
            return;
        }

        $name = $_POST['name'];
        $description = $_POST['description'];
        $id = $_POST['id'];
        $categoryId = $_POST['subCategoryId'];

        // Retrieve the uploaded file
        $file = $_FILES['image']??null;
        // if ($requestData) {
        if (!isset($name)) {
            http_response_code(400);
            echo json_encode(array('status' => 'failed', 'data' => 'Name Field required'));
            return;
        }

        $category = SubSubCategory::getById($id, $pdo);

        if(!$category){
            http_response_code(404);
            echo json_encode(array('status' => 'failed', 'data' => 'Resource not found'));
            return;
        }
        if($file !== null){
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
            $category->id = $id;
            $category->name = $name;
            $category->categoryId = $categoryId;
            $category->description = $description;
            $category->save();

            http_response_code(200);
            echo json_encode(array('status' => 'success', 'data' => $category));
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 'failed', 'data' => 'resource not found'));
        }
    }

    public function deleteCategory()
    {
        $pdo = createDatabaseConnection();

        $categoryId = $_POST['id']??null;

        $category = SubSubCategory::getById($categoryId, $pdo);
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

            $categorys = SubSubCategory::getAll($pdo);

            // Convert the banner objects to JSON format
            http_response_code(200);

            $jsonData = json_encode($categorys);


            // Output the JSON data
            echo $jsonData;
        } catch (PDOException $e) {
          
            http_response_code(400); // Internal Server Error
            echo json_encode(['error' => 'An error occurred while fetching subcategories.']);
        }
    }

    public function showCategorysToUsers()
    {
        try {
            $pdo = createDatabaseConnection();

            $categorys = SubSubCategory::getAll($pdo);

            // Convert the banner objects to JSON format
            $jsonData = json_encode($categorys);

            http_response_code(200);
            header('Content-Type: application/json');

            // Output the JSON data
            echo $jsonData;
        } catch (PDOException $e) {
   
            http_response_code(400); // Internal Server Error
            echo json_encode(['error' => 'An error occurred while fetching subcategories.']);
        }
    }
}
