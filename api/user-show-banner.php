<?php

require_once 'controllers/BannerController.php';
require_once 'require/header.php';

$bannerController = new BannerController();
 $bannerController->showBannersToUsers();