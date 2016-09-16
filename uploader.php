<?php
require_once 'trap.config.php';

function uploadImagesToFtp(){
	$ftp=ftp_connect(FTP_SERVER) or die("No connection to ftp possible");
	if (@ftp_login($ftp,FTP_USER,FTP_PASS)){
		echo "FTP Verbunden";
		ftp_chdir($ftp,FTP_PATH);
		
		$images = glob(IMAGEDIR.'/*.JPG');
		
		foreach ($images as $file){
			$file_name=basename($file);
			
			$upload = ftp_nb_put($ftp, $file_name,$file, FTP_BINARY);
			
				while ($upload == FTP_MOREDATA) {
					$upload = ftp_nb_continue($ftp);
				}
				if($upload != FTP_FINISHED) {
					echo "... ERROR ...";				
				}
				else {
					echo "hochgeladen";
				}
			}
		
	}
	else
	{
		echo "Falscher Login"; 
	}
	
	ftp_close($ftp);
}

?>