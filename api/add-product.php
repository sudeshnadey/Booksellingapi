<?php

require_once 'controllers/ProductController.php';
include 'require/auth-admin.php';
require_once 'require/header.php';

$catController = new ProductController();
$catController->addProduct();
