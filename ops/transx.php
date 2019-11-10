<?php
include '../common/common.inc';

if (CompleteWithNoAction()) return;

$LastOperation = 'Transparent';

$IMAGEOFFSET_X = 2;
$IMAGEOFFSET_Y = 2;

$clientX = $_POST['CLIENTX'];
$clientY = $_POST['CLIENTY'];
$NewColor = $_POST['PICKCOLOR'];

RecordCommand("TRANS NewColor = $NewColor $clientX $clientY");

// if the user manuall entered a color, use it
if (strlen($NewColor) >= 3)
{
    $Color = str_replace("#", "", $NewColor);
    $clientX = $clientY = 0;
    RecordCommand("XTRANS Chosen Color = $Color");
}
else
{
    $Color = 'white';
}

	
$ArgFuzz = $_POST['FUZZ'];
//$ArgFuzz= "20%";


//build up the input and output paths
$inputFileDir = $_POST['CURRENTFILE'];
$inputFileDir = "$BASE_DIR$inputFileDir";
RecordCommand("$inputFileDir");


// must resize really small images so pick will work properly
$ImageResized = FALSE;
GetImageAttributes($inputFileDir,$w,$h,$size);
if (($w < 30) || ($h < 30))
{
    $ImageResized = TRUE;
    $inputFileDir = ResizeImage($inputFileDir,30,30,FALSE);
    RecordCommand("Small Image Seen");
}

//make sure it's a gif (just go ahead and do the convert)
$targetName = NewNameGIF();
$outputFileDir = GetConversionDir($targetName);

$command = "convert $inputFileDir $outputFileDir";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
$GIFinputFileDir = $outputFileDir;

//get image dimensions, filter out images that aren't cooperating
GetImageAttributes($GIFinputFileDir,$real_width,$real_height,$size);
if ($real_height <= 0)
{
    $ErrorCode = 10;
    $real_height = 300;
}
if ($real_width <= 0)
{
    $ErrorCode = 10;
    $real_width = 300;
}

//convert to png (if not png already)
//gotta use matte option, so to disregard any transparent layer
$targetName = NewNamePNG();
$outputFileDir = GetConversionDir($targetName);
$command = "convert +matte $inputFileDir $outputFileDir";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
//RecordCommand("$command");

//
// if it's an animation we'll get multiple png files,
// otherwise a single file
//
if ((file_exists($outputFileDir)) == FALSE)
{
    $outputFileDir = StripSuffix($outputFileDir);
    $outputFileDir = $outputFileDir."-0".$PNGSUFFIX;
    if ((file_exists($outputFileDir)) == FALSE)
    {
        $ErrorCode = 10;
    }
}
$inputFileDir = $outputFileDir;

//do the conversion
$inputFileDir = $GIFinputFileDir;
$targetName = NewNameGIF();
$outputFileDir = GetConversionDir($targetName);
$outputFilePath = GetConversionPath($targetName);

$Color = str_replace("#", "", $Color);
if (ctype_xdigit($Color) == TRUE)
{
    if ($ArgFuzz > 0)
        $command = "convert -fuzz $ArgFuzz -transparent \"#$Color\""; 
    else
        $command = "convert -transparent \"#$Color\"";
}
else
{
    RecordCommand("TRANS USERCOLOR CHOSEN");
    if ($ArgFuzz > 0)
        $command = "convert -fuzz $ArgFuzz -transparent $Color";
    else
        $command = "convert -transparent $Color";
}

// CJM DEV 
$command = "$command -dispose previous";

$command = "$command $inputFileDir $outputFileDir";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
RecordCommand("$command");

if ($ImageResized == TRUE)
{
    $outputFileDir = ResizeImage($outputFileDir,$w,$h,FALSE);
    $targetName = basename($outputFileDir);
    $outputFilePath = GetConversionPath($targetName);
    RecordCommand("Resized back to original size");
}

$LastOperation = "Transparent Color: $Color Fuzz: $ArgFuzz";
RecordCommand("FINAL $outputFilePath");
RecordAndComplete("TRANS",$outputFilePath,FALSE);

?>
