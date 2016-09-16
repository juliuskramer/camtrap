<?php
require_once 'db.config.php';
require_once 'trap.config.php';



function writeTrapSettings(){
	
	global $dbhost, $dbuser, $dbpass, $dbname;
	
	$mysqli = mysqli_connect($dbhost, $dbuser, $dbpass);
	if (!$mysqli) {
		die('Verbindung schlug fehl: ' . mysql_error());
	}
	
	$mysqli->select_db($dbname);	
	
	$trapid=TRAPID;
	
	global $lastconnection;
	global $filesoncamera;
	global $filesonpi;
	
	global $allsettings;


	$cameramodel=$allsettings['cameramodel']['current'];
	$manufacturer=$allsettings['manufacturer']['current'];
	$serialnumber=substr($allsettings['serialnumber']['current'],0,7);
	$iso=$allsettings['iso']['current'];
	$fnumber=$allsettings['f-number']['current'];
	$shutterspeed=$allsettings['shutterspeed2']['current'];
	$exposureprogram=$allsettings['expprogram']['current'];
	$batterylevel=$allsettings['batterylevel']['current'];
	$focusmode=$allsettings['focusmode']['current'];
	$focallength=$allsettings['focallength']['current'];
	$capturemode=$allsettings['capturemode']['current'];
	$exposurecompensation=$allsettings['exposurecompensation']['current'];
	
	$sql="INSERT INTO d01f765f.cameratraps(cameramodel, manufacturer, serialnumber, iso, fnumber, shutterspeed, exposureprogram, focusmode, batterylevel, focallength, capturemode, exposurecompensation, numberoffiles,  trapid, lastconnection) 
			VALUES ('{$cameramodel}','{$manufacturer}','{$serialnumber}','{$iso}','{$fnumber}','{$shutterspeed}','{$exposureprogram}','{$focusmode}','{$batterylevel}','{$focallength}','{$capturemode}','{$exposurecompensation}','{$filesoncamera}','{$trapid}','{$lastconnection}')
			";
	
	$result = $mysqli->query($sql,MYSQLI_USE_RESULT);
	
	echo $mysqli->affected_rows.' Datensatz geändert';
	
	mysqli_close($mysqli);	

}

function getNewSettings(){
	global $dbhost, $dbuser, $dbpass, $dbname;
	$trapid=TRAPID;
	$mysqli = mysqli_connect($dbhost, $dbuser, $dbpass);
	if (!$mysqli) {
		die('Verbindung schlug fehl: ' . mysql_error());
	}
	
	$mysqli->select_db($dbname);
	
	$sql="SELECT * FROM set_cameratraps WHERE trapid = '{$trapid}'";
	
	if ($result = $mysqli->query($sql, MYSQLI_USE_RESULT)){		
		$row = $result->fetch_assoc();
		//$row['expprogram']=$row['exposureprogram'];
		//$row['f-number']=$row['fnumber'];
	$result->close();
	mysqli_close($mysqli);

	return ($row);
	}		
}


/*
 * Schreibt die veränderlichen Einstellungen in die Tabelle "settings" und löscht vorher alle Einträge.
 * Sollte bei Neuinstallation einer Kamera ausgeführt werden
 * 
 * @param $settingname
 * @param $append um an den lesbaren Namen etwas anzufügen, z.B. "Sekunde"
 * 
 */

function writeNewEditableSettingsToDB($settingname,$append){
	global $dbhost, $dbuser, $dbpass, $dbname;
	global $allsettings;
	$trapid=TRAPID;
	$mysqli = mysqli_connect($dbhost, $dbuser, $dbpass);
	if (!$mysqli) {
		die('Verbindung schlug fehl: ' . mysql_error());
	}
	
	$mysqli->select_db($dbname);
	$sql="DELETE FROM settings WHERE trapid='{$trapid}' AND setting='{$settingname}'";
	$result = $mysqli->query($sql,MYSQLI_USE_RESULT);
	
	echo $mysqli->affected_rows.' gelöscht';
	
	
	
	if (array_key_exists($settingname,$allsettings))
	{
	$choices=array();
	$choices=$allsettings[$settingname]['choices'];
	
	foreach ($choices as $key=>$choice){
		
		$name=explode(' ',$choice)[1].$append;		
		$sql=" INSERT INTO d01f765f.settings(setting,value,name,trapid) VALUES ('{$settingname}','{$key}','{$name}','{$trapid}') ;";	

		$result = $mysqli->query($sql,MYSQLI_USE_RESULT);
		
		echo $mysqli->affected_rows.' Datensatz geändert';
	
	}
	}



	mysqli_close($mysqli);
	
	
	
}



?>