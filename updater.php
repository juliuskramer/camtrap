<?php 
require_once 'trap.config.php';

function checkForNewVersion() {
	
	$versions=file_get_contents(UPDATE_PATH.'/updates.php') or die ('Updateserver nicht erreichbar');
	
	print_r ("Current Version: ".VERSION);
	print_r ("Reading Versions now");
	if ($versions != '') {

	$versionListe = explode(PHP_EOL,$versions);
	print_r($versionListe);
	foreach ($versionListe as $version) {
		$updateDownloaded=false;
		
		if ($version > VERSION){
			
			echo 'New Update Found: v'.$version;
			
			zipOldVersion();
			$found = true;
			
			//Download The File If We Do Not Have It
			if ( !is_file(  UPDATEDIR.$version.'.zip' )) {
				echo 'Downloading New Update';
				$newUpdate = file_get_contents(UPDATE_PATH.'/'.$version.'.zip');
				if ($newUpdate != '') {
					//if ( !is_dir( $_ENV['site']['files']['includes-dir'].'/UPDATES/' ) ) mkdir ( $_ENV['site']['files']['includes-dir'].'/UPDATES/' );
					$dlHandler = fopen(UPDATE_PATH.'/'.$version.'.zip', 'w');
						if ( !fwrite($dlHandler, $newUpdate) ) { 
							echo 'Could not save new update. Operation aborted.'; 
							exit(); 
						
							fclose($dlHandler);
							echo 'Update Downloaded And Saved';
						}
						else {
						 	echo 'Update already downloaded.';
						 }
				$updateDownloaded=true;
				} else {
					echo "Updatefile nicht verügbar";
					$updateDownloaded=false;
				}
			}
			
			
			if ($updateDownloaded) {
			
			//Open The File And Do Stuff
			$zipHandle = zip_open(UPDATE_PATH.'/'.$version.'.zip');
			
			while ($aF = zip_read($zipHandle) )
			{
				$thisFileName = zip_entry_name($aF);
				$thisFileDir = dirname($thisFileName);
				 
				//Continue if its not a file
				if ( substr($thisFileName,-1,1) == '/') continue;
				 
			
				//Make the directory if we need to...
				if ( !is_dir ( UPDATE_PATH.'/'.$thisFileDir ) )
				{
					mkdir ( UPDATE_PATH.'/'.$thisFileDir );
					echo 'Created Directory '.$thisFileDir;
				}
				 
				//Overwrite the file
				if ( !is_dir(UPDATE_PATH.'/'.$thisFileName) ) {
					echo $thisFileName.'...........';
					$contents = zip_entry_read($aF, zip_entry_filesize($aF));
					$contents = str_replace("rn", "n", $contents);
					$updateThis = '';
					 
					//If we need to run commands, then do it.
					if ( $thisFileName == 'upgrade.php' )
					{
						$upgradeExec = fopen ('upgrade.php','w');
						fwrite($upgradeExec, $contents);
						fclose($upgradeExec);
						include ('upgrade.php');
						unlink('upgrade.php');
						echo' EXECUTED';
					}
					else
					{
						$updateThis = fopen(UPDATE_PATH.'/'.$thisFileName, 'w');
						fwrite($updateThis, $contents);
						fclose($updateThis);
						unset($contents);
						echo' UPDATED';
					}
				}
			}

			$updated = TRUE;
			
			}
			
			}
		
		
	}
	}

}


function zipOldVersion(){
	
	echo 'Zipping old version';
	$rootPath=BASEDIR;
	
	$oldversion='_update/oldversions/cameratrap_'.date('Y-m-d').'.zip';
	
	echo $oldversion;
	echo $rootPath;
	
	$zip= new ZipArchive();
	$zip->open($oldversion, ZipArchive::CREATE | ZipArchive::OVERWRITE);
	
	$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
	
			);
	
	foreach ($files as $name => $file) {
		if (!$file->isDir()){
			$filePath=$file->getRealPath();
			$relativePath = substr($filePath, strlen($rootPath)+1);
	
			//do not add JPG Files!
			if (!strstr($file,'.JPG')){
				$zip->addFile($filePath, $relativePath);
			}
		}
	
	}
	
	$zip->close();
	return true;
}

?>