<?php

require_once __DIR__ . '/../../vendor/autoload.php';
include('./models/Admin.php');
include('./models/User.php');

use Firebase\JWT\JWT;

class AdminController
{
    private $secretKey = 'S35001_A4M1n'; 

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $jsonPayload = file_get_contents('php://input');
            $requestData = json_decode($jsonPayload, true);

            if ($requestData !== null) {
                $username = $requestData['emailid'];
                $password = $requestData['password'];

                // Retrieve admin by username
              $admin = Admin::getByUsername($username);

                if ($admin !== null && password_verify($password, $admin->getPassword())) {
                    // Admin login successful

                    // Create and sign the JWT token
                    $tokenId = base64_encode(random_bytes(32));
                    $issuedAt = time();
                    $expirationTime = $issuedAt + 3600; // Token expires in 1 hour

              

                    $jwt = JWT::encode(['user'=>'admin','username'=>$admin->getUsername()], $this->secretKey, 'HS256');

                    $id=$admin->getId();
                    $pdo=createDatabaseConnection();
                    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = :id");
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                
                    $user2 = $stmt->fetch(PDO::FETCH_ASSOC);
                    unset($user2['password']);
                    // Return the JWT token
                    echo json_encode(['token' => $jwt,'user'=>$user2]);
                    return;
                }
            }
        }

        // Invalid login credentials or request
        http_response_code(401);
        echo "Invalid username or password.";
    }


    public function showUsers()
    {
        try {
            $pdo = createDatabaseConnection();

            $users = User::getAll($pdo);

            // Convert the banner objects to JSON format
            $jsonData = json_encode($users);

            http_response_code(200);
            header('Content-Type: application/json');

            // Output the JSON data
            echo $jsonData;
            return;
        } catch (PDOException $e) {
            // Example: Logging the error
            error_log('Error fetching categories: ' . $e->getMessage());

            // Return an error response
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'An error occurred while fetching users.'.$e->getMessage()]);
            return;
        }
    }
}
