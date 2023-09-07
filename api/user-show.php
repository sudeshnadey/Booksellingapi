<?php
require_once 'require/header.php';
require_once 'controllers/AdminController.php';

$AdminController = new AdminController();
$AdminController->showUsers();