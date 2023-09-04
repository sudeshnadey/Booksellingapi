<?php

require_once 'controllers/BannerController.php';
require_once 'require/header.php';
include 'require/auth-admin.php';

$bannerController = new BannerController();
$bannerController->addBanner();
