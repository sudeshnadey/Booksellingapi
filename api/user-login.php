<?php
require_once 'require/header.php';
require_once 'controllers/UserController.php';

$userController = new UserController();
$userController->login();