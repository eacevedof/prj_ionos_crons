<?php
include("../boot/appbootstrap.php");
include("../src/MainController.php");

(new MainController())->index();

