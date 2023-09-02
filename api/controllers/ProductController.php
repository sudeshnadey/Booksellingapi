<?php
require '../models/Product.php';
require_once '../config/db-connect.php';

class ProductController
{


    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    public function addProduct()
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
            if (!isset($_POST['mrp']) || empty($_POST['mrp'])) {
                // Handle invalid name (name is missing or empty)
                $response = array(
                    'status' => 'error',
                    'message' => 'Invalid MRp'
                );
                echo json_encode($response);
                return;
            }
            if (!isset($_POST['quantity']) || empty($_POST['quantity'])) {
                // Handle invalid name (name is missing or empty)
                $response = array(
                    'status' => 'error',
                    'message' => 'Quantity Required'
                );
                echo json_encode($response);
                return;
            }
            $name = $_POST['name'];
            $categoryId = $_POST['categoryId'];
            $description = $_POST['description'];
            $subCategoryId = $_POST['subCategoryId'];
            $mrp = $_POST['mrp'];
            $quantity = $_POST['quantity'];
            $discount = $_POST['discount'];

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

            $category = new Product($name, $fileName, $description, $categoryId, $subCategoryId, $mrp, $discount, $quantity);
            $category->save();
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'data' => $category));
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 'failed', 'message' => 'please enter data to store'));
        }
    }


    public function editProduct()
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
        // if (!isset($_POST['categoryId']) || empty($_POST['categoryId'])) {
        //     // Handle invalid name (name is missing or empty)
        //     $response = array(
        //         'status' => 'error',
        //         'message' => 'Invalid Category Id'
        //     );
        //     echo json_encode($response);
        //     return;
        // }
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            // Handle invalid name (name is missing or empty)
            $response = array(
                'status' => 'error',
                'message' => 'Invalid Product Id'
            );
            echo json_encode($response);
            return;
        }

        $name = $_POST['name'];
        $description = $_POST['description'];
        $id = $_POST['id'];
        $categoryId = $_POST['categoryId'];

        $subCategoryId = $_POST['subCategoryId'];
        $mrp = $_POST['mrp'];
        $quantity = $_POST['quantity'];
        $discount = $_POST['discount'];

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
        $product = Product::getById($id, $pdo);

        // $category = new category($name, $fileName, $description);
        if ($product) {
            $product->name = $name;
            $product->image = $fileName;
            $product->categoryId = $categoryId;
            $product->subCategoryId = $subCategoryId;
            $product->mrp = $mrp;
            $product->quantity = $quantity;
            $product->discount = $discount;
            $product->description = $description;
            $product->save();

            http_response_code(200);
            echo json_encode(array('status' => 'success', 'data' => $product));
        } else {
            http_response_code(404);
            echo json_encode(array('status' => 'failed', 'data' => 'resource not found'));
        }
    }

    public function deleteProduct()
    {
        $pdo = createDatabaseConnection();

        $id = $_POST['id'];

        $category = Product::getById($id, $pdo);
        // echo json_encode($id);
        if ($category && $category->delete($id)) {

            http_response_code(200);
            echo json_encode(array('status' => 'success', 'data' => 'successfully deleted'));
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 'failed', 'data' => 'error while deleted'));
        }
    }

    public function showProducts()
    {
        try {
            $pdo = createDatabaseConnection();

            $products = Product::getAll($pdo);

            // Convert the banner objects to JSON format
            $jsonData = json_encode($products);

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

    public function showshowProductsToUsers()
    {
        try {
            $pdo = createDatabaseConnection();

            $products = Product::getAll($pdo);

            // Convert the banner objects to JSON format
            $jsonData = json_encode($products);

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
