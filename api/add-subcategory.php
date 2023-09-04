<?php

require_once 'controllers/SubCategoryController.php';
require_once 'require/header.php';
include 'require/auth-admin.php';

$catController = new SubCategoryController();
$catController->addCategory();
