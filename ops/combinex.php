<?php
include '../common/common.inc';



if (CompleteWithNoAction()) return;

$LastOperation = 'Blended';

$position = $_POST['POSITION'];
$blendLevel = $_POST['BLENDLEVEL'];
$blendImageDir = $_POST['FRAMEPATH1'];

if (stripos($blendImageDir, 'http') !== False) 
{
    RecordCommand('BLEND HTTP SEEN');
    $blendImageDir = GetConversionDir($blendImageDir);
}
RecordCommand("BLEND IMAGE $blendImageDir");

$blendImageDir = ConvertToJPG($blendImageDir);

$inputFileDir = $_POST['CURRENTFILE'];
$inputFileDir = "$BASE_DIR$inputFileDir";
RecordCommand("COMBINE PATTERN=$blendImageDir IMAGE=$inputFileDir");

GetImageAttributes($inputFileDir,$inputFile_width,$inputFile_height,$size);

//must resize target file to avoid too large files
//
/*
if ($size > 300000)
{
    $inputFile_width = $inputFile_height = 350;
    $inputFileDir = ResizeImage($inputFileDir, $inputFile_width,$inputFile_height, FALSE);
    $inputFileName = basename($inputFileDir);
    GetImageAttributes($inputFileDir,$inputFile_width,$inputFile_height,$size);
}
*/

RecordCommand(": Effect = $position");
switch ($position)
{
	case 'OVERLAY':	// pattern is same size as target
		$blendImageDir = ResizeImage($blendImageDir,$inputFile_width,$inputFile_height,TRUE);
		break;
	case 'TILED':	// tiled pattern, small tile size
		$blendImageDir = GenerateTiledPattern($blendImageDir);
		break;
}  // end switch 


$targetName = NewName($inputFileDir);
$outputFileDir = GetConversionDir($targetName);
$outputFilePath = GetConversionPath($targetName);

if (($position == 'OVERLAY') || ($position == 'TILED'))
    $command = "composite -blend $blendLevel $blendImageDir $inputFileDir $outputFileDir";
else
    $command = "composite -gravity $position -blend $blendLevel $blendImageDir $inputFileDir $outputFileDir";

$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
RecordCommand("$command");
RecordCommand("FINAL $outputFilePath $command");
RecordAndComplete("BLEND",$outputFilePath,FALSE);


function GenerateTiledPattern($blendImageDir)
{
global $inputFile_width;
global $inputFile_height;

	$w =  ($inputFile_width > 400) ? 100 : $inputFile_width / 5;
	$y =  ($inputFile_height > 400) ? 100 : $inputFile_height / 5;
	$blendImageDir = ResizeImage($blendImageDir,$w,$h,FALSE);
	$dimensions = "$inputFile_width"."x"."$inputFile_height";
	$outputFileName = TMPName("temp.gif");
    $outputFileDir = GetConversionDir($outputFileName);
	$command = "convert -size $dimensions tile:$blendImageDir $outputFileDir";
	$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
	RecordCommand("$command");
	$blendImageDir = $outputFileDir;
	return $blendImageDir;
}

?>
