<?php

require_once '../controllers/SubCategoryController.php';
require_once '../require/header.php';

$catController = new SubCategoryController();
$catController->addCategory();
