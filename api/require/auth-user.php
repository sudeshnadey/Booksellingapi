<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require 'models/User.php';
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$headers = apache_request_headers();
 $token = $headers['token'] ?? null;
$secretKey = 'S35001_A4M1n'; // Secret key used for signing the token
try {

    if(!$token){
        http_response_code(401);
        echo json_encode('Un Authenticated');
        exit;
    }


    $decoded = \Firebase\JWT\JWT::decode($token,new Key($secretKey,'HS256'));

    $user = User::getByUsername($decoded->phone??null);

    if($decoded->user !='user' || $user == null){
        http_response_code(403);
        echo json_encode('Un Authorized'); 
        exit ;
    }

} catch (ExpiredException $e) {
    http_response_code(403);
    echo json_encode('Token Expired'); 
    exit ;

} catch (Exception $e) {
    http_response_code(403);
    // echo $e;
    echo json_encode('Un Authorized'); 
    exit ;
}
