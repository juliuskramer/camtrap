<?php
require_once 'db.config.php';
require_once 'trap.config.php';
include 'connectionhandler.php';
include 'cameraconnector.php';

//connection aufbauen
//connect();

//Verbinde mit der Kamera
echo getCamera();
//download the files from the camera
//downloadImages();
//




?>