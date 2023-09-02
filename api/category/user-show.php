<?php

require_once '../controllers/CategoryController.php';

$adminController = new CategoryController();
 $adminController->showCategorysToUsers();