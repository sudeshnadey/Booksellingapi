<?php
require './models/Banner.php';
require_once './config/db-connect.php';

class BannerController
{

    public function addBanner()
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

            $banner = new Banner($name, $fileName, $description);
            $banner->save();
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'data' => $banner));
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 'failed', 'message' => 'please enter data to store'));
        }
    }

    public function editBanner()
    {
        $pdo = createDatabaseConnection();

        $name = $_POST['name'];
        $description = $_POST['description'];
        $bannerId = $_POST['bannerId'];

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


        $image_path = 'images/' . $fileName;

        move_uploaded_file($fileTmpPath, $image_path);
        $banner = Banner::getById($bannerId, $pdo);

        // $banner = new Banner($name, $fileName, $description);
        if ($banner) {
            $banner->id = $bannerId;
            $banner->name = $name;
            $banner->image = $fileName;
            $banner->description = $description;
            $banner->save();

            http_response_code(200);
            echo json_encode(array('status' => 'success', 'data' => $banner));
        } else {
            http_response_code(404);
            echo json_encode(array('status' => 'failed', 'data' => 'resource not found'));
        }
    }

    public function deleteBanner()
    {
        $pdo = createDatabaseConnection();

        $bannerId = $_POST['bannerId'] ?? null;

        $banner = Banner::getById($bannerId, $pdo);
        // echo json_encode($bannerId);
        if ($banner && $banner->delete($bannerId)) {

            http_response_code(200);
            echo json_encode(array('status' => 'success', 'data' => 'successfully deleted'));
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 'failed', 'data' => 'error while deleting'));
        }
    }

    public function showBanners()
    {
        try {
            $pdo = createDatabaseConnection();

            $banners = Banner::getAll($pdo);

            // Convert the banner objects to JSON format
            $jsonData = json_encode($banners);

            http_response_code(200);
            header('Content-Type: application/json');

            // Output the JSON data
            echo $jsonData;
        } catch (PDOException $e) {
            // Example: Logging the error
            error_log('Error fetching banners: ' . $e->getMessage());

            // Return an error response
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'An error occurred while fetching banners.']);
        }
    }

    public function showBannersToUsers()
    {
        try {
            $pdo = createDatabaseConnection();

            $banners = Banner::getAll($pdo);

            // Convert the banner objects to JSON format
            $jsonData = json_encode($banners);

            http_response_code(200);
            header('Content-Type: application/json');

            // Output the JSON data
            echo $jsonData;
        } catch (PDOException $e) {
            // Example: Logging the error
            error_log('Error fetching banners: ' . $e->getMessage());

            // Return an error response
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'An error occurred while fetching banners.']);
        }
    }
}
