<?php
require_once 'require/header.php';
require_once 'controllers/UserController.php';

$userController = new UserController();
$response=$userController->editUser();

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);