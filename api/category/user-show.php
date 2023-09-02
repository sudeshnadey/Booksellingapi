<?php

require_once '../controllers/CategoryController.php';
require_once '../require/header.php';

$adminController = new CategoryController();
 $adminController->showCategorysToUsers();