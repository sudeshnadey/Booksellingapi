<?php
require_once '../require/header.php';

require_once '../controllers/ProductController.php';

$catController = new ProductController();
$catController->addProduct();
