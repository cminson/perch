<?php
include "../common/common.inc";

$tmpName = $_FILES['FILENAME']['tmp_name']; 
$sourceName = $_FILES['FILENAME']['name']; 

function ExitWithError($error)
{
    global $LastOperation;

	$errorReport = "Error: $error";
    $LastOperation = $errorReport;
	echo '<html><head><title>-</title></head><body>';
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo "parent.reportLoadError(\"$errorReport\");";
	echo "\n".'</script></body></html>';
    exit();
}

APPLOG("LOAD: tmp=$tmpName source=$sourceName");
if (empty($sourceName)) 
{ 
    ExitWithError("No File Specified"); 
}

if (!is_uploaded_file($tmpName)) 
{
    ExitWithError("Image Did Not Load"); 
}

if (filesize($tmpName) == 0)
{
    ExitWithError("Image Did Not Load"); 
}

//
// if we reached this point, then the image load succeeded
// convert it to a png and store in conversions dir
// 
$outputFilePath = NewImagePath();
$script = "convert $tmpName $outputFilePath";
ExecScript($script);

/*
    if (file_exists($outputFilePath) == FALSE)
    {
        $imageName = StripSuffix($imageName);
        $outputFilePath = GetConversionPath("$imageName-0$JPGSUFFIX");
        APPLOG("ConvertToJPG ANIM SEEN $outputFilePath");
    }
*/
APPLOG("XLOAD: UPLOAD JPG $tmpName $outputFilePath");

chmod($outputFilePath,0777);
$UploadSuccess = TRUE;


GetImageAttributes($outputFilePath,$width,$height,$size);
APPLOG("LOADX $outputFilePath");
if ($size > $MAX_FILE_SIZE)
{
    if (($width > $RESIZE_MAX_WIDTH) || ($height > $RESIZE_MAX_HEIGHT))
    {
		$outputFilePath = ResizeImage($outputFilePath, $RESIZE_MAX_WIDTH, $RESIZE_MAX_HEIGHT, FALSE);
		APPLOG("LOADX RESIZE $size $width $height");
    }
}

$outputFileURL = GetConversionURL($outputFilePath);

//
// Exec AI segment analysis of uploaded file
//
// specify path to downgraded venv tensorflow (1.14)
// how to do this as current mrcnn deosn't work in tf 2.0
//
//
/*
$script = escapeshellcmd("/var/www/perch/VENV/bin/python3 ./mlsegment.py $outputFilePath");
shell_exec($script);
APPLOG("XLOAD SEGMENT ANALYSIS: $script");

$stats = GetStatString($outputFilePath);
$regionList = GetImageRegions($outputFilePath);
$regions = Implode(',', $regionList);
APPLOG("XLOAD REGIONS: $regions");
 */

$regions = "";

// inform javascript caller that the image is loaded and ready for display
$LastOperation = "Image Loaded";
APPLOG("LOADX SUCCESS $outputFilePath");
echo '<html><head><title>-</title></head><body>';
echo '<script language="JavaScript" type="text/javascript">'."\n";
echo "parent.completeImageLoad(\"$outputFileURL\",\"$stats\",\"$regions\",\"$width\", \"$height\");";
echo "\n".'</script></body></html>';





?>
