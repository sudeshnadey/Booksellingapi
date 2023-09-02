<?php
require_once 'require/header.php';

require_once 'controllers/BannerController.php';

$bannerController = new BannerController();
$bannerController->addBanner();
