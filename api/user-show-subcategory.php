<?php

require_once 'controllers/SubCategoryController.php';
require_once 'require/header.php';

$adminController = new SubCategoryController();
$adminController->showCategorysToUsers();
