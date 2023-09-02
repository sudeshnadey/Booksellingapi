<?php

require_once __DIR__ . '/../../vendor/autoload.php';
include('./models/Admin.php');

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
                    // $expirationTime = $issuedAt + 3600; // Token expires in 1 hour

                    // $payload = [
                    //     'iat' => $issuedAt,
                    //     // 'exp' => $expirationTime,
                    //     'jti' => $tokenId,
                    //     'data' => [
                    //         'adminId' => $admin->getId(),
                    //         'username' => $admin->getUsername()
                    //     ]
                    // ];

                    $jwt = JWT::encode(['user'=>'admin','username'=>$admin->getUsername()], $this->secretKey, 'HS256');

                    // Return the JWT token
                    echo json_encode(['token' => $jwt]);
                    return;
                }
            }
        }

        // Invalid login credentials or request
        http_response_code(401);
        echo "Invalid username or password.";
    }
}
