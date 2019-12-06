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

RecordCommand("LOAD: tmp=$tmpName source=$sourceName");
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
$imageName = NewImageName();
$outputFilePath = GetConversionPath($imageName);
$outputFileURL = GetConversionURL($imageName);
$command = "convert $tmpName $outputFilePath";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
/*
    if (file_exists($outputFilePath) == FALSE)
    {
        $imageName = StripSuffix($imageName);
        $outputFilePath = GetConversionPath("$imageName-0$JPGSUFFIX");
        RecordCommand("ConvertToJPG ANIM SEEN $outputFilePath");
    }
*/
RecordCommand("XLOAD: UPLOAD JPG $tmpName $outputFilePath");

chmod($outputFilePath,0777);
$UploadSuccess = TRUE;


GetImageAttributes($outputFilePath,$width,$height,$size);
RecordCommand("LOADX $outputFilePath");
if ($size > $MAX_FILE_SIZE)
{
    if (($width > $RESIZE_MAX_WIDTH) || ($height > $RESIZE_MAX_HEIGHT))
    {
		$outputFilePath = ResizeImage($outputFilePath, $RESIZE_MAX_WIDTH, $RESIZE_MAX_HEIGHT, FALSE);
		$outputFileURL = GetConversionURL($outputFilePath);
		RecordCommand("LOADX RESIZE $size $width $height");
    }
}

// Exec AI segment analysis of uploaded file
//

$command = escapeshellcmd("python ./mlsegment.py $outputFilePath");
shell_exec($command);
RecordCommand("XLOAD SEGMENT ANALYSIS: $command");

$stats = GetStatString($outputFilePath);
$regionList = GetImageRegions($outputFilePath);
$regions = Implode(',', $regionList);
RecordCommand("XLOAD REGIONS: $regions");

// inform javascript caller that the image is loaded and ready for display
$LastOperation = "Image Loaded";
RecordCommand("LOADX SUCCESS $outputFilePath");
echo '<html><head><title>-</title></head><body>';
echo '<script language="JavaScript" type="text/javascript">'."\n";
echo "parent.completeImageLoad(\"$outputFileURL\",\"$stats\",\"$regions\",\"$width\", \"$height\");";
echo "\n".'</script></body></html>';





?>
