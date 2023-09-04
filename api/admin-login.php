<?php
require_once 'require/header.php';
require_once 'controllers/AdminController.php';

$adminController = new AdminController();
$adminController->login();