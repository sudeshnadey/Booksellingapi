<?php
use Firebase\JWT\JWT;

$headers = apache_request_headers();
$token = $headers['token'] ?? null;
$secretKey = 'S35001_A4M1n'; // Secret key used for signing the token
try {

    if(!$token){
        http_response_code(401);
        echo json_encode('Un Authenticated');
        exit;
    }


    $decoded = JWT::decode($token, $secretKey);

    if($decoded['user'] !='user'){
        http_response_code(403);
        echo json_encode('Un Authorized'); 
        exit ;
    }

} catch (Exception $e) {
    http_response_code(403);
    echo json_encode('Un Authorized'); 
    exit ;
}
