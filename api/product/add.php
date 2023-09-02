<?php

require_once '../controllers/ProductController.php';

$catController = new ProductController();
$catController->addProduct();
