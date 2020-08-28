<?php
include("../boot/appbootstrap.php");
include("../src/Controllers/MainController.php");

(new \App\Controllers\MainController())->index();

