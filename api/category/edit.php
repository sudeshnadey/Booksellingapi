<?php

require_once '../controllers/CategoryController.php';
require_once '../require/header.php';
include '../require/auth-admin.php';

$adminController = new CategoryController();
 $adminController->editCategory();