<?php

require_once __DIR__ . '/../../vendor/autoload.php';
include('./models/User.php');
include './require/login-user.php';
include './require/url.php';

use Firebase\JWT\JWT;
use Ramsey\Uuid\Uuid;

class UserController
{
    private $secretKey = 'S35001_A4M1n';

    public function login()
    {
        // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $jsonPayload = file_get_contents('php://input');
                $requestData = json_decode($jsonPayload, true);
                // echo json_encode($requestData);
                // if ($requestData !== null) {
                    $username = $requestData['phone'] ?? null;
                    $password = $requestData['password'] ?? null;

                    if ($username == null || $password == null) {
                        http_response_code(401);
                        echo json_encode(['message' => "Invalid username or password."]);
                        exit;
                    }

                    // Retrieve admin by username
                    $user = User::getByUsername($username);
                 

                    if ($user !== null && password_verify($password, $user->getPassword())) {

                        $tokenId = base64_encode(random_bytes(32));
                        $issuedAt = time();
                        $expirationTime = $issuedAt + 3600; // Token expires in 1 hour
                        $jwt = JWT::encode(['user' => 'user', 'phone' => $user->getPhone()], $this->secretKey, 'HS256');

                        $pdo=createDatabaseConnection();
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
                        $stmt->bindParam(':id', $user->id);
                        $stmt->execute();
                    
                        $user2 = $stmt->fetch(PDO::FETCH_ASSOC);
                        unset($user2['password']);
                         $user2['photo']=$user2['photo']?imageUrl().$user2['photo']:null;
               
                        // Return the JWT token
                        echo json_encode(['token' => $jwt, 'user' => $user2]);
                        return;
                    }else{
                        http_response_code(401);
                        echo json_encode(['message' => "Incorrect username or password."]);
                        exit;
                    }
                // }
            } catch (Exception $e) {
                // Handle the exception
                http_response_code(500);
                echo json_encode(['message' => 'An error occurred.'.$e->getMessage()]);
                exit;
            }
        }

        // Invalid login credentials or request
        // http_response_code(401);
        // echo json_encode(['message' => "Invalid username or password."]);
        // exit;
    // }


    public function registerUser()
    {
        // Retrieve the JSON data from the request body
        $data = $_POST;

        // Decode the JSON data
        // $data = json_decode($jsonData, true);

        // Validate the required fields
        if (empty($data['name'])  || empty($data['password']) || empty($data['phone'])) {
            return ['success' => false, 'message' => 'Missing required fields.'];
        }

        // Validate email format (optional)
        // if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        //     return ['success' => false, 'message' => 'Invalid email format.'];
        // }

        // Validate phone number format (optional)
        // You can use regular expressions or other validation techniques here

        // Register the user
        $user = User::registerUser($data['name'],password_hash($data['password'],PASSWORD_DEFAULT), $data['phone']);

        if ($user) {
            return ['success' => true, 'message' => 'User registered successfully.'];
        } else {
            return ['success' => false, 'message' => 'Failed to register user.'];
        }
    }
    public function editUser()
    {
        // Retrieve the JSON data from the request body
        $data = $_POST;

        // Decode the JSON data
        // $data = json_decode($jsonData, true);
        $data['id']=getUser()->id??'';

        // Validate the required fields

        if (empty($data['id'])) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Un Authorized.'];
        }
        if (empty($data['name'])  || empty($data['email']) || empty($data['phone'])) {
            http_response_code(400);

            return ['success' => false, 'message' => 'Missing required fields.'];
        }

        $photo = $_FILES['photo']??'';

        $fileName=null;
        if (!empty($photo)) {
            $originalFileName = $photo['name'];
            $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
            
            $fileName = Uuid::uuid4()->toString() . '.' . $fileExtension;
            $fileTmpPath = $photo['tmp_name'];
            $destination = 'images/' . $fileName;
            move_uploaded_file($fileTmpPath, $destination);

        }
        $user = User::updateUser($data['name'],$data['email'], $data['phone'],$data['id'], $fileName);

        if ($user) {
            return ['success' => true, 'message' => 'Profile Updated successfully.'.$user];
        } else {
            http_response_code(400);
            return ['success' => false, 'message' => 'Failed to update user.'.$user];
        }
    }
}
