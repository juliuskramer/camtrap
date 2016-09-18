<?php
// k�mmert sich um die UMTS Verbindung und h�lt sie am Leben

include 'connection.config.php';


$signalstrength = array();
$host = 'www.google.de';


function connect(){
$stick = checkStick($stickPort);

if (isset($stick)){
	//hole die signalast�rke
	exec('comgt -d '.$stickPort.' sig | cut -d\':\' -f 2', $signalstrength);
	exec("wvdial congstar &");
	//verbinde dich
	 $i=0;
	 while ($signalstrength[0]<3) {
	 	sleep(2);
	 	exec('comgt -d '.$stickPort.' sig | cut -d\':\' -f 2', $signalstrength);
	 	$i++;
	 	
	 	if ($i>5) {
	 		//keine sichere Verbindung, signalst�rke zu gering!
	 		break;
	 	}
	 }
	

		
	$up = ping($host);
// wenn verbindung besteht
	if( $up ) {
        exec('/etc/init.d/ntp restart',$return);
        $date=date('Ymd\_H\-i');
        rename('/cameratrap/log/log_temp.txt','/cameratrap/log/atmegalog_'.$date.'.txt');
        rename('/cameratrap/log/mainlog.txt','/cameratrap/log/mainlog_'.$date.'.txt');
        
	}
	// ansonsten: neu verbinden
	else {
		exec("ifup ppp0");
        
	}
}

	//kein Stick erkannt
else {

	
	
}
}

//function f�r check ob UMTS stick verf�gbar

function checkStick($port) {
	$command='comgt -d'.$port.' | grep Error';
	$answer;
	exec($command,$answer);
	
	return $answer;
	
}

/* function to check if connection is available */

function ping($host,$port=80,$timeout=6)
{
        $fsock = fsockopen($host, $port, $errno, $errstr, $timeout);
        if ( ! $fsock )
        {
                return FALSE;
        }
        else
        {
                return TRUE;
        }
}

?>