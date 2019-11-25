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
// convert it to a jpg and store in conversions dir
// 
$targetName = NewBaseImageName();
$outputFileDir = GetConversionDir($targetName);
$outputFilePath = GetConversionPath($targetName);
$command = "convert $tmpName $outputFileDir";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
/*
    if (file_exists($outputFileDir) == FALSE)
    {
        $targetName = StripSuffix($targetName);
        $outputFileDir = GetConversionDir("$targetName-0$JPGSUFFIX");
        RecordCommand("ConvertToJPG ANIM SEEN $outputFileDir");
    }
*/
RecordCommand("XLOAD: UPLOAD JPG $tmpName $outputFileDir");

chmod($outputFileDir,0777);
$UploadSuccess = TRUE;

// Exec AI segment analysis of uploaded file
$command = escapeshellcmd("python ./mlsegment.py $outputFileDir");
//$command = escapeshellcmd("python ./test.py $outputFileDir");
$r = shell_exec($command);
RecordCommand("XLOAD: $command $r");

GetImageAttributes($outputFileDir,$width,$height,$size);
RecordCommand("LOADX $outputFileDir");
if ($size > $MAX_FILE_SIZE)
{
    if (($width > $RESIZE_MAX_WIDTH) || ($height > $RESIZE_MAX_HEIGHT))
    {
		$outputFileDir = ResizeImage($outputFileDir, $RESIZE_MAX_WIDTH, $RESIZE_MAX_HEIGHT, FALSE);
		$targetName = basename($outputFileDir);
		$outputFilePath = GetConversionPath($outputFileDir);
		RecordCommand("LOADX RESIZE $size $width $height");
    }
}
$stats = GetStatString($outputFileDir);

// inform javascript caller that the image is loaded and ready for display
$LastOperation = "Image Loaded";
RecordCommand("LOADX SUCCESS $outputFilePath");
echo '<html><head><title>-</title></head><body>';
echo '<script language="JavaScript" type="text/javascript">'."\n";
echo "parent.completeImageLoad(\"$outputFilePath\",\"$stats\");";
echo "\n".'</script></body></html>';





?>
