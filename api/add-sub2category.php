<?php

require_once 'controllers/SubSubCategoryController.php';
require_once 'require/header.php';
include 'require/auth-admin.php';

$catController = new SubSubCategoryController();
$catController->addCategory();
