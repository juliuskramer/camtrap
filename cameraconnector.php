<?php
require_once 'db.config.php';
require_once 'trap.config.php';




/*
 * Holt alle Einstellungen von der Kamera und dem Pi
 *
 * 
 * 
 */
function getTrapSettings()
{
	global $filesonpi;
	global $filesoncamera;
	$filesonpi=getImagesOnPi();
	$filesoncamera=getImagesOnCamera();
	//echo $filesoncamera;
 	getCameraSettings();
 


}


/*
 * Lädt die neuen Einstellungen aus der Tabelle set_cameratraps und ändert die Kameraeinstellungen dementsprechent
 * 
 * @param trapid
 * @return true
 * 
 * 
 */
function updateTrapSettings(){
	
	$newsettings=getNewSettings();
	global $allsettings;
	if ($newsettings['reset_raspberry']==1){
		foreach ($newsettings as $settingname => $value) {
	
			if (array_key_exists($settingname,$allsettings)){
				setCameraConfig($settingname,$value);
			}
	
		}
	return TRUE;
	}	

}


function getCamera() {
	exec("gphoto2 --auto-detect", $answer);
	if (count($answer)>2){
		$output = trim(explode("usb", $answer[count($answer)-1])[0]);
		return true;
	}
	else {
		$output ='Keine Kamera verbunden!';	
		return false;
	}	
	
	
}


/*
 *
 * zerlegt die Antwort von gphoto2 aus einzel Arrayzeilen in ein mehrdimensionales Array
 *
 * Zum Bespiel
 * /main/capturesettings/capturemode
 *Label: Bildaufnahme Modus
 *Type: RADIO
 *Current: Kontinuierlicher Langsame Geschwindigkeit
 *Choice: 0 Einzelbild
 *Choice: 1 Mehrbild
 *Choice: 2 Kontinuierlicher Langsame Geschwindigkeit
 *Choice: 3 Selbstauslöser
 *Choice: 4 Spiegel hochklappen
 *Choice: 5 Leises Auslösen
 *
 * Das Array $allsettings enthält:
 * 	capturemode => (name=>'capturemode',fullname=>'/main/capturesettings/capturemode', label=>'Bildaufnahmemodus',....)
 *  Die möglichen Auswahlmöglichkeiten (choices) finden sich in einem weiteren Array
 *
 */

$allsettings=array();


function getCameraSettings(){

	exec('gphoto2 --list-all-config',$answers);

	global $allsettings;
	$setting=array();
	$choices=array();

	foreach ($answers as $line){


		if (substr($line,0,1)==='/')
		{	
			$choices=array();
			$names=explode('/',$line);
			$name=$names[count($names)-1];
			$setting['fullname']=$line;
			$setting['name']=$name;
		}
		if (substr($line,0,5)==='Label'){
			$label=trim(explode(":",$line)[1]);
			$setting['label']=$label;
		}

		if (substr($line,0,4)==='Type'){
			$type=trim(explode(":",$line)[1]);
			$setting['type']=$type;
		}

		if (substr($line,0,7)==='Current'){
			$current=trim(explode(":",$line)[1]);
			$setting['current']=$current;
		}

		if (substr($line,0,6)==='Choice'){			
			$choice=explode(':',$line)[1];
			$keys=explode(' ',$choice);
			$key=$keys[1];
			$choices[$key]=trim($choice);
			$setting['choices']=array();
			$setting['choices']=$choices;
		}

		$allsettings[$name]=$setting;

	}
	
	return $allsettings;
	
}


function setLowestJPGQuality(){	
	global $allsettings;
	$maxChoices=count($allsettings['imagesize']['choices']);
	setCameraConfig('imagesize',$maxChoices-1);	
	setCameraConfig('imagequality','NEF+Basic');	
}


/*
 * Rufe eine spezifische Einstelliung von der Kamera ab
 * 
 * @param $setting : z.B. 'iso','expprogram'
 * 
 * @return current value
 * 
 * 
 */
function getCameraConfig($setting) {
	exec('gphoto2 --get-config '.$setting, $lines);
	
	$searchword = 'Current';
	$matches = array();
	foreach($lines as $k=>$v) {
    if(preg_match("/\b$searchword\b/i", $v)) {
        $matches[$k] = $v;
        $currentvalue = explode(':', $matches[$k]);
    }
	}
	$output = trim($currentvalue[1]);
	echo $output;	
	return $output;
}


/*
 * Setze eine spezifische Einstelliung auf die Kamera
 * Wahlweise Index oder Wert
 *
 * @param $setting : z.B. 'iso','expprogram'
 * @param $value:	 z.B. '1600','A','0','1'
 *
 * @return answer
 *
 *
 */

function setCameraConfig($setting, $value) {
	exec('gphoto2 --quiet --set-config '.$setting.'='.$value, $answer);
	//$output=trim(explode(":", $answer[0]));
	//return $answer[0];
}


/*
 * Get the number of images in the configured imagedir in trap.config.php
 * 
 */

function getImagesOnPi(){
	
	$fi = new FilesystemIterator(IMAGEDIR, FilesystemIterator::SKIP_DOTS);
	$filesonpi = iterator_count($fi);
	return $filesonpi;
}


/*
 * Get the number of JPG files on the camera
 * 
 */

function getImagesOnCamera(){

	//check how many files are on the camera
	exec('/usr/local/bin/gphoto2 -L --quiet | grep JPG | wc -l', $answer);
	return $answer[0];
}


//download files from camera and resize
function downloadImages() {
	
	global $filesonpi;
	global $filesoncamera;
	
	if ($filesonpi<$filesoncamera) {
		
		//download from camera
		
		exec('gphoto2 -L --quiet | grep JPG',$jpgfiles);
		$images = glob(IMAGEDIR.'/*.JPG');
		
		foreach ($jpgfiles as $line) {
			
			$splitted = preg_split('/\s/',$line);
			$key = $splitted[0];			
			if (in_array(IMAGEDIR.'/small_'.basename($key),$images)==FALSE)		{	
			exec('gphoto2 --skip-existing -p '.$key.' --filename '.IMAGEDIR.'/%f.%C',$answer);
			}
		}
		
		
		$image = glob(IMAGEDIR.'/*.JPG');
		
		foreach ($image as $filename) {
			$image=basename($filename);
			$outname=IMAGEDIR.'/small_'.$image;		
			
			if (exif_imagetype($filename)) {	
				if (substr(basename($filename),0,5)!= 'small'){
    				if(resizeImage($filename,$outname,THUMB_QUALITY,THUMB_WIDTH,THUMB_HEIGHT,TRAPID)){
     				echo $filename.' resize Success!<br />'; 
     				//Delete the .JPG file after resizing
     				unlink($filename);    						
    				}
				}
			} else 
			{				
				echo $filename.' ist kein valides Bild! Gelöscht!';
				unlink($filename);
			}
			
		}
	}
}

function isDownloadReady(){
	global $filesoncamera;
	
	if (getImagesOnPi()<$filesoncamera) {
		return FALSE;
	} 
	else {
		return TRUE;
	}
	
}


function resizeImage($inPath,$outPath,$thumbquality,$thumbwidth,$thumbheight) {
	
		try {
    //The blur factor where &gt; 1 is blurry, &lt; 1 is sharp.
    //$image=$inPath;
	
    $im = new Imagick($inPath);
    //$imagick->realpath($inPath);
    $im->setImageFormat('jpg');
    $im->setImageCompressionQuality($thumbquality);  
    $im->resizeImage($thumbwidth, $thumbheight, Imagick::FILTER_LANCZOS2SHARP, 1, TRUE);
     
    // Watermark text
	$exifdate = convertExifToTimestamp($im->getImageProperty('exif:DateTimeOriginal'),'d.m.Y H:i');	
	$exifblende=$im->getImageProperty('exif:FNumber');
	$exifshutter=$im->getImageProperty('exif:ExposureTime');
	$text = "Kamera: ".TRAPID." | ".basename($inPath)." | ".$exifshutter." @ ".$exifblende." | ".$exifdate;
	$im->stripImage();
	// Create a new drawing palette
	$draw = new ImagickDraw();
	// Set font properties
	$draw->setFont(BASEDIR.'/fonts/FreeSans.ttf');
	$draw->setFontSize(18);
	$draw->setFillColor('white');

	// Position text at the bottom-right of the image
	$draw->setGravity(Imagick::GRAVITY_SOUTHEAST);
	// Draw text on the image
	$im->annotateImage($draw, 10, 12, 0, $text);   
	$im->adaptiveSharpenImage(2,1);
    $im->writeimage($outPath);
    
    $im->destroy();
    return $outPath;
    
    
		}
	catch(Exception $e)
	{
		print $e->getMessage();
		return $inPath;
	}
}

function convertExifToTimestamp($exifString, $dateFormat)
{
  $exifPieces = explode(":", $exifString);
  return date($dateFormat, strtotime($exifPieces[0] . "-" . $exifPieces[1] .
        "-" . $exifPieces[2] . ":" . $exifPieces[3] . ":" . $exifPieces[4]));
}


?>