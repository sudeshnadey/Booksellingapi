<?php

require_once 'controllers/BannerController.php';

$adminController = new BannerController();
 $adminController->deleteBanner($_POST['bannerId']);