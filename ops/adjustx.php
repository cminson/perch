<?php
include '../common/common.inc';

//if (CompleteWithNoAction()) return;

$Arg = ($_POST['ARG1']);
$inputFileDir = $_POST['CURRENTFILE'];
$inputFileDir = "$BASE_DIR$inputFileDir";


switch ($Arg)
{
case "UP_BRIGHT":
	$LastOperation = "Increased Brightness";
	$command = "convert -modulate 110";
	break;
case "DOWN_BRIGHT":
	$LastOperation = "Decreased Brightness";
	$command = "convert -modulate 90";
	break;
case "UP_CONTRAST":
	$LastOperation = "Increased Contrast";
	$command = "convert -contrast";
	break;
case "DOWN_CONTRAST":
	$LastOperation = "Decreased Contrast";
	$command = "convert +contrast";
	break;
case "UP_HUE":
	$LastOperation = "Increased Hue";
	$command = "convert -modulate 100,100,110";
	break;
case "DOWN_HUE":
	$LastOperation = "Decreased Hue";
	$command = "convert -modulate 100,100,90";
	break;
case "UP_SATURATE":
	$LastOperation = "Increased Saturation";
	$command = "convert -modulate 100,130";
	break;
case "DOWN_SATURATE":
	$LastOperation = "Decreased Saturation";
	$command = "convert -modulate 100,70";
	break;
case "UP_SHARP":
	$LastOperation = "Increased Sharpness";
	$command = "convert -sharpen 0.0x1.0";
	break;
case "DOWN_SHARP":
	$LastOperation = "Decreased Sharpness";
	$command = "convert -unsharp 0.0x1.0";
	break;
};

$targetName = NewImageName($inputFileDir);
$outputFileDir = GetConversionDir($targetName);
$outputFilePath = GetConversionPath($targetName);
$command = "$command $inputFileDir $outputFileDir";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
RecordCommand("FINAL $Arg $command $outputFilePath");

RecordAndComplete($LastOperation,$outputFilePath,TRUE);




?>
