<?php
$mysqli;

function opendb() {
	$mysqli = mysqli_connect($dbhost, $dbuser, $dbpass);
        if (!$mysqli) {
            die('Verbindung schlug fehl: ' . mysql_error());
        }

        $mysqli->select_db($dbname);
}

function closedb() {
	mysqli_close($mysqli);	
}


function writeCameraSettings(){

	opendb();
	$sql="INSERT INTO d01f765f.cameratraps(id, cameramodel, manufacturer, serialnumber, iso, fnumber, shutterspeed, exposureprogram, focusmode, batterylevel, focallength, capturemode, exposurecompensation, lastconnection, numberoffiles, signalstrength, trapid) 
			VALUES (NULL,'{$cameramodel}','{$manufacturer}','{$serialnumber}','{$iso}','{$fnumber}','{$shutterspeed}','{$exposureprogram}','{$focusmode}','{$batterylevel}','{$focallength}','{$capturemode}','{$exposurecompensation}','{$lastconnection}','{$filesoncamera}','{$signalstrength}','{$trapid}')
			";
	closedb();

}



?>