<?php

require_once 'controllers/CategoryController.php';
require_once 'require/header.php';

$adminController = new CategoryController();

if(isset($_GET['type'])){
    $adminController->showCategorysByType();

}else{
    $adminController->showCategorysToUsers();

}
