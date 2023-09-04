<?php

require_once 'controllers/ProductController.php';
require_once 'require/header.php';
include 'require/auth-admin.php';
$adminController = new ProductController();
 $adminController->deleteProduct();