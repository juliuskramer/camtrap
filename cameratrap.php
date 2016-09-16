<?php
require_once 'trap.config.php';

include 'connectionhandler.php';
include 'cameraconnector.php';
include 'dbhandler.php';
include 'uploader.php';
include 'updater.php';


//connection aufbauen
//connect();

//Verbinde mit der Kamera
//echo getCamera();
if (getCamera()){
	getTrapSettings();
	setLowestJPGQuality();
	downloadImages();



	updateTrapSettings();
	writeTrapSettings();



	$waitedseconds;
	while (isDownloadReady()==FALSE){
		$waitedseconds++;
		sleep (1);
		if ($waitedseconds > 60) {
			break;	
			}
		}

	uploadImagesToFtp();

}



checkForNewVersion();




function initializeCamera(){
	
	try {
	writeNewEditableSettingsToDB('expprogram','');
	writeNewEditableSettingsToDB('shutterspeed2','s');
	writeNewEditableSettingsToDB('exposurecompensation','EV');
	writeNewEditableSettingsToDB('iso','');
	writeNewEditableSettingsToDB('f-number','');
	return TRUE;
	} catch (Exception $e) {
		
		print $e->getMessage();
		return FALSE;
		
	}
	
	
}




?>