<?php

require_once '../controllers/SubCategoryController.php';
require_once '../require/header.php';
include '../require/auth-admin.php';

$adminController = new SubCategoryController();
 $adminController->editCategory();