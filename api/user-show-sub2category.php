<?php

require_once 'controllers/SubSubCategoryController.php';
require_once 'require/header.php';

$adminController = new SubSubCategoryController();
$adminController->showCategorysToUsers();
