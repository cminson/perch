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
$outputFileDir = GetConversionDir($imageName);
$outputFilePath = GetConversionPath($imageName);
$command = "convert $tmpName $outputFileDir";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
/*
    if (file_exists($outputFileDir) == FALSE)
    {
        $imageName = StripSuffix($imageName);
        $outputFileDir = GetConversionDir("$imageName-0$JPGSUFFIX");
        RecordCommand("ConvertToJPG ANIM SEEN $outputFileDir");
    }
*/
RecordCommand("XLOAD: UPLOAD JPG $tmpName $outputFileDir");

chmod($outputFileDir,0777);
$UploadSuccess = TRUE;


GetImageAttributes($outputFileDir,$width,$height,$size);
RecordCommand("LOADX $outputFileDir");
if ($size > $MAX_FILE_SIZE)
{
    if (($width > $RESIZE_MAX_WIDTH) || ($height > $RESIZE_MAX_HEIGHT))
    {
		$outputFileDir = ResizeImage($outputFileDir, $RESIZE_MAX_WIDTH, $RESIZE_MAX_HEIGHT, FALSE);
		$outputFilePath = GetConversionPath($outputFileDir);
		RecordCommand("LOADX RESIZE $size $width $height");
    }
}

// Exec AI segment analysis of uploaded file
$command = escapeshellcmd("python ./mlsegment.py $outputFileDir");
shell_exec($command);
RecordCommand("XLOAD SEGMENT ANALYSIS: $command");

$regionMaskList = GetImageRegions($outputFileDir);
$regionCount = count($regionMaskList);

if ($regionCount == 1) 
{
    $backgroundRegionMask = NewRegionMaskName($outputFileDir, 'background', 99);
    $backgroundRegionMaskDir = GetConversionDir($backgroundRegionMask);
    $regionMask = $regionMaskList[0];
    $command = "convert -negate $regionMask $backgroundRegionMaskDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    RecordCommand("LOADX Region1 $command");
} 
else if ($regionCount >= 2) {

    $regionMask1 = $regionMaskList[0];
    $regionMask2 = $regionMaskList[1];
    $tmpDir1 = GetConversionDir(NewTmpImageName());
    $tmpDir2 = GetConversionDir(NewTmpImageName());
    $command = "convert -fill red -opaque white -transparent black $regionMask1 $tmpDir1";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    $command = "convert -fill red -opaque white -transparent black $regionMask2 $tmpDir2";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    RecordCommand("LOADX Region2 $command");

    $tmpDir3 = GetConversionDir(NewTmpImageName());
    $command = "composite $tmpDir1 $tmpDir2 $tmpDir3";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    RecordCommand("LOADX Region2 $command");

    $tmpDir4 = GetConversionDir(NewTmpImageName());
    $command = "convert -flatten $tmpDir3 $tmpDir4";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    RecordCommand("LOADX Region2 $command");

    $tmpDir5 = GetConversionDir(NewTmpImageName());
    $command = "convert -fill black -opaque white  $tmpDir4 $tmpDir5";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    RecordCommand("LOADX Region2 $command");

    $tmpDir6 = GetConversionDir(NewTmpImageName());
    $command = "convert -fill white -opaque red $tmpDir5 $tmpDir6";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    RecordCommand("LOADX Region2 $command");

    $backgroundRegionMask = NewRegionMaskName($outputFileDir, 'background', 99);
    $backgroundRegionMaskDir = GetConversionDir($backgroundRegionMask);
    $command = "convert -negate $tmpDir6 $backgroundRegionMaskDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    RecordCommand("LOADX Region2 $command");
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
