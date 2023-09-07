<?php

require_once 'controllers/SubSubCategoryController.php';
require_once 'require/header.php';
include 'require/auth-admin.php';

$adminController = new SubSubCategoryController();
 $adminController->showCategorys();