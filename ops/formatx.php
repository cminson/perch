<?php
include '../common/common.inc';

if (CompleteWithNoAction()) return;

$Arg = $_POST['ARG1'];
RecordCommand("Reformat $Arg");

$inputFileDir = $_POST['CURRENTFILE'];
$inputFileDir = "$BASE_DIR$inputFileDir";
$inputFileName = basename($inputFileDir);

switch ($Arg)
{
case "BMP":
	$LastOperation = "BMP";
    $targetName = NewNameBMP();
    $suffix = ".bmp";
	break;
case "GIF":
	$LastOperation = "GIF";
    $targetName = NewNameGIF();
    $suffix = ".gif";
	break;
case "JPG":
	$LastOperation =  "JPG";
    $targetName = NewNameJPG();
    $suffix = ".jpg";
	break;
case "PNG":
	$LastOperation = "PNG";
    $suffix = ".png";
    $targetName = NewNamePNG();
	break;
};


$outputFilePath = GetConversionPath($targetName);
$outputFileDir = GetConversionDir($targetName);
$command = "convert $inputFileDir $outputFileDir";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
RecordCommand($command);

// 
// check to make sure the output file really exists ..
//
// if it doesn't, we assume it's because the source file
// was an animated image.  
//
if ((file_exists($outputFileDir)) == FALSE)
{
	RecordCommand("$outputFileDir File Not Found");

    $targetName = StripSuffix($targetName);
    $targetName = $targetName."-0".$suffix;
    $targetName = GetConversionDir($targetName);

    $outputFileDir = GetConversionDir($targetName);
	RecordCommand("$outputFileDir TRUE File Found");
    $outputFilePath = GetConversionPath($targetName);
}

$inputFileDir = $outputFileDir;
GetImageAttributes($inputFileDir,$real_width,$real_height,$size);
if ($size > $MAX_FILE_SIZE)
{
    if (($real_width > $RESIZE_MAX_WIDTH) || ($real_height > $RESIZE_MAX_HEIGHT))
    {
            RecordCommand("XCF RESIZED $size $outputFileDir");
            $inputFileDir = ResizeImage($inputFileDir,$RESIZE_MAX_WIDTH,$RESIZE_MAX_HEIGHT,FALSE);
            $targetName = basename($inputFileDir);
            $outputFilePath = GetConversionPath($targetName);
    }
}

RecordAndComplete($LastOperation,$outputFilePath,TRUE);


?>
