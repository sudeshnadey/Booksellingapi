<?php
require './models/Book.php';
require_once './config/db-connect.php';
require_once './require/image-upload.php';

use Ramsey\Uuid\Uuid;

class ProductController
{


    public function addProduct()
    {

        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            try {


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
                $categoryId = $_POST['categoryId'] ?? null;
                $description = $_POST['description'];
                $lang = $_POST['lang'] ?? null;
                $mrp = $_POST['mrp'];
                $quantity = $_POST['quantity'];
                $discount = $_POST['discount'];
                $filec = $_FILES['content'] ?? null;
                $fileb = $_FILES['barcode'] ?? null;

                if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    // Handle invalid file upload (file is missing or has an error)
                    $response = array(
                        'status' => 'error',
                        'message' => 'Product Image Required'
                    );
                    echo json_encode($response);
                    return;
                }


                // Retrieve the uploaded file
                // $file = $_FILES['image'];

                // $originalFileName = $file['name'];
                // $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);

                // $fileName = Uuid::uuid4()->toString() . '.' . $fileExtension;
                // $fileTmpPath = $file['tmp_name'];
                // $image_path = 'images/' . $fileName;

                // move_uploaded_file($fileTmpPath, $image_path);

                $product = new Book($name, $description, $categoryId, $lang, $mrp, $discount, $quantity);
                $product->delivery_price = $_POST['delivery_price'] ?? 0;

                if ($fileb !== null) {
                    $originalFileName = $fileb['name'];
                    $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);

                    $fileName = Uuid::uuid4()->toString() . '.' . $fileExtension;
                    $fileTmpPath = $fileb['tmp_name'];
                    $image_path = 'images/' . $fileName;
                    move_uploaded_file($fileTmpPath, $image_path);
                    $product->barcode = $fileName;
                } else {
                    $product->barcode = null;
                }
                if ($filec !== null) {
                    $originalFileName = $filec['name'];
                    $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
                    
                    $fileName = Uuid::uuid4()->toString() . '.' . $fileExtension;
                    $fileTmpPath = $filec['tmp_name'];
                    $image_path = 'images/' . $fileName;
                    move_uploaded_file($fileTmpPath, $image_path);
                    $product->sample = $fileName;
                } else {
                    $product->sample = null;
                }

              $product->save();
                uploadImages('book',$product->id);
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'data' => $product));
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(array('status' => 'failed', 'message' => $e->getMessage()));
                exit;
            }
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 'failed', 'message' => 'please enter data to store'));
        }
    }


    public function editProduct()
    {
        $pdo = createDatabaseConnection();

        try {
            if (!isset($_POST['name']) || empty($_POST['name'])) {
                // Handle invalid name (name is missing or empty)
                $response = array(
                    'status' => 'error',
                    'message' => 'Invalid name'
                );
                echo json_encode($response);
                return;
            }

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

            $mrp = $_POST['mrp'];
            $quantity = $_POST['quantity'];
            $discount = $_POST['discount'];
            $type = $_POST['type'];
            $lang = $_POST['lang'];

            $file = $_FILES['image'] ?? null;
            $filec = $_FILES['content'] ?? null;
            $fileb = $_FILES['barcode'] ?? null;

            if (!isset($name)) {
                http_response_code(404);
                echo json_encode(array('status' => 'failed', 'data' => 'Name Field required'));
                return;
            }


            $product = Book::getById($id, $pdo);

            if ($file !== null) {
                $fileName = $file['name'];
                $fileTmpPath = $file['tmp_name'];
                $image_path = 'images/' . $fileName;
                move_uploaded_file($fileTmpPath, $image_path);
                $product->image = $fileName;
            } else {
                $product->image = null;
            }
            if ($fileb !== null) {
                $fileName = Uuid::uuid4()->toString();
                log($fileName);
                $fileTmpPath = $fileb['tmp_name'];
                $image_path = 'images/' . $fileName;
                move_uploaded_file($fileTmpPath, $image_path);
                $product->barcode = $fileName;
            } else {
                $product->barcode = null;
            }
            if ($filec !== null) {
                $fileName = Uuid::uuid4()->toString();
                $fileTmpPath = $filec['tmp_name'];
                $image_path = 'images/' . $fileName;
                move_uploaded_file($fileTmpPath, $image_path);
                $product->sample = $fileName;
            } else {
                $product->sample = null;
            }
            // $category = new category($name, $fileName, $description);
            if ($product) {
                $product->id = $id;
                $product->name = $name;
                $product->categoryId = $categoryId;
                $product->mrp = $mrp;
                $product->quantity = $quantity;
                $product->discount = $discount;
                $product->description = $description;
                $product->type = $type;
                $product->lang = $lang;

                $product->delivery_price = $_POST['delivery_price'] ?? 0;
                $product->save();

                http_response_code(200);
                echo json_encode(array('status' => 'success', 'data' => $product));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'failed', 'data' => 'resource not found'));
            }
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode(array('status' => 'failed', 'data' => $e));
        }
    }

    public function deleteProduct()
    {
        $pdo = createDatabaseConnection();

        $id = $_POST['id'] ?? null;

        $category = Book::getById($id, $pdo);
        // echo json_encode($id);
        if ($category && $category->delete($id)) {

            http_response_code(200);
            echo json_encode(array('status' => 'success', 'data' => 'successfully deleted'));
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 'failed', 'data' => 'error while deleting'));
        }
    }

    public function showProducts()
    {
        try {
            $pdo = createDatabaseConnection();

            $products = Book::getAll($pdo);

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

            $products = Book::getAll($pdo);

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
