<?php
require_once 'require/header.php';
require_once 'controllers/ProductController.php';
include 'require/auth-user.php';

$adminController = new ProductController();
 $adminController->showsProductsDetailUsers();