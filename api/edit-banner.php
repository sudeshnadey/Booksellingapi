<?php

require_once 'controllers/BannerController.php';
require_once 'require/header.php';
include 'require/auth-admin.php';

$adminController = new BannerController();
 $adminController->editBanner();