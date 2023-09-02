<?php

require_once 'controllers/BannerController.php';
require_once 'require/header.php';

$adminController = new BannerController();
 $adminController->deleteBanner();