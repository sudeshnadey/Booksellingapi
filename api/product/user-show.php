<?php

require_once '../controllers/ProductController.php';
require_once '../require/header.php';

$adminController = new ProductController();
 $adminController->showshowProductsToUsers();