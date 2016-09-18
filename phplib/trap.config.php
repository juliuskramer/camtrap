<?php

define('TRAPID','develop');
define('VERSION','0.1');

$trapid = TRAPID;
//$base='/home/felix/workspace/camtrap';
define('BASEDIR','/home/felix/git/juliuskramer/camtrap/');
define('LOGDIR','/home/felix/git/juliuskramer/camtrap/images');
define('IMAGEDIR','/home/felix/git/juliuskramer/camtrap/images');
define('UPDATEDIR','/home/felix/git/juliuskramer/camtrap/_updates');
//$imagedir = $base.'/images';


define('THUMB_QUALITY',60);
define('THUMB_WIDTH',1200);
define('THUMB_HEIGHT',800);

define('FTP_SERVER','w00ceed8.kasserver.com');
define('FTP_USER','f00acfe5');
define('FTP_PASS','b3HCQDEHMrehPVuc');
define('FTP_PATH','/'.TRAPID);

define('UPDATE_PATH','http://trap.fokusnatur.de/photos/_updates');



$lastconnection = date('Y-m-d H:i:s');

$filesoncamera;
$filesonpi;

$cameramodel;
$manufacturer;
$serialnumer;
$iso;
$fnumber;
$shutterspeed;
$exposureprogram;
$batterylevel;
$focusmode;
$focallength;
$capturemode;
$exposurecompensation;

?>