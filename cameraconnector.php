<?php
$filesoncamera = array();
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

function getCamera() {
	exec("gphoto2 --auto-detect", $answer);
	if (explode("usb", $answer[count($answer) - 1])){
		$output = trim(explode("usb", $answer[count($answer) - 1]));
	}
	else {
		$output ='Keine Kamera verbunden!'	
	}
	
	
	return $output;
}

//hole alle Kamerarelevanten Einstellungen
function getCameraSettings() {	
	$cameramodel=getCameraConfig('cameramodel');
	$manufacturer=getCameraConfig('manufacturer');
	$serialnumber=getCameraConfig('serialnumber');
	$iso=getCameraConfig('iso');
	$fnumber=getCameraConfig('f-number');
	$shutterspeed=getCameraConfig('shutterspeed2');
	$exposureprogram=getCameraConfig('expprogram');
	$batterylevel=getCameraConfig('batterylevel');
	$focusmode=getCameraConfig('focusmode');
	$focallength=getCameraConfig('focallength');
	$capturemode=getCameraConfig('capturemode');
	$exposurecompensation=getCameraConfig('exposurecompensation');
}

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

function setCameraConfig($setting) {
	exec('gphoto2 --quiet --set-config '.$setting, $answer);
	//$output=trim(explode(":", $answer[0]));
	return $output;
}

function getImagesOnPi(){
	$fi = new FilesystemIterator($imagedir, FilesystemIterator::SKIP_DOTS);
	$filesonpi = iterator_count($fi);
	return $filesonpi;
}

//download files from camera and resize
function downloadImages() {
	
	$downloadfromcamera = 'gphoto2 -L --quiet | grep JPG | while read fn 
			do
			echo $fn
			/usr/local/bin/gphoto2  --skip-existing -p $fn --filename '.$imagedir.'%f.%C 
			done';
	//check how many files are on the camera
	exec('/usr/local/bin/gphoto2 -L --quiet | grep JPG | wc -l', $filesoncamera);
	
	$filesonpi=getImagesOnPi();
	
	if ($filesonpi<$filesoncamera[0]) {
		//download from camera
		exec($downloadfromcamera);
		
		$image = glob($imagedir.'/*.JPG');
		
		foreach ($image as $filename) {
			$imagename=basename($filename, ".JPG");
			$outname=$imagedir.$imagename.".jpg";
			if (exif_imagetype($filename)) {				
    				if(resizeImage($filename,$outname)){
     				echo $file.' resize Success!<br />'; 
     				//Delete the .JPG file after resizing
     				unlink($filename);    						
    				}
				}
			
		}
}
}


function resizeImage($inPath,$outPath) {
    //The blur factor where &gt; 1 is blurry, &lt; 1 is sharp.
    $imagick = new \Imagick(realpath($inPath));
    $imagick->setImageFormat('jpg');
    $imagick->setImageCompressionQuality($thumbquality);  
    $imagick->resizeImage($thumbwidth, $thumbheight, $filterType, 1, TRUE);
      
    // Watermark text
	$exifdate = convertExifToTimestamp($image->getImageProperty('exif:DateTimeOriginal'),'d.m.Y H:i');	
	$text = $trapid." | ".basename($inPath)." | ".$exifdate;
	
	// Create a new drawing palette
	$draw = new ImagickDraw();
	// Set font properties
	$draw->setFont('Arial');
	$draw->setFontSize(12);
	$draw->setFillColor('white');

	// Position text at the bottom-right of the image
	$draw->setGravity(Imagick::GRAVITY_SOUTHEAST);
	// Draw text on the image
	$imagick->annotateImage($draw, 10, 12, 0, $text);    
    $imagick->writeimage($outPath);
	
}

function convertExifToTimestamp($exifString, $dateFormat)
{
  $exifPieces = explode(":", $exifString);
  return date($dateFormat, strtotime($exifPieces[0] . "-" . $exifPieces[1] .
        "-" . $exifPieces[2] . ":" . $exifPieces[3] . ":" . $exifPieces[4]));
}


function array_find($needle, array $haystack)
{
    foreach ($haystack as $key => $value) {
        if (false !== stripos($needle, $value)) {
            return $key;
            echo $key;
        }
    }
    return false;
}


?>