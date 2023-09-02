<?php

require_once '../controllers/CategoryController.php';
require_once '../require/header.php';

$catController = new CategoryController();
$catController->addCategory();
