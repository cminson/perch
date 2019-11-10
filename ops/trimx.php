<?php
include '../common/common.inc';

if (CompleteWithNoAction()) return;

$LastOperation = 'Trimmed';

$inputFileDir = $_POST['CURRENTFILE'];
$inputFileDir = "$BASE_DIR$inputFileDir";
$inputFileName = basename($inputFileDir);
$targetName = NewName($inputFileDir);
$outputFileDir = GetConversionDir($targetName);
$outputFilePath = GetConversionPath($targetName);


$amt = $_POST['SETTING'];
$gravity = $_POST['GRAVITY'];
switch ($gravity)
{
    case "NORTH":	
		$ArgShave = "0x".$amt;
		$lastOperation = "trimmed $amt pixels from top of image";
		$command = "convert -chop $ArgShave -gravity North $inputFileDir $outputFileDir";
		break;
    case "SOUTH":	
		$ArgShave = "0x".$amt;
		$lastOperation = "trimmed $amt pixels from bottom of image";
		$command = "convert -chop $ArgShave -gravity South $inputFileDir $outputFileDir";
		break;
    case "NORTHSOUTH":	
		$ArgShave = "0x".$amt;
		$lastOperation = "trimmed $amt pixels from top and bottom of image";
		$command = "convert -shave $ArgShave $inputFileDir $outputFileDir";
		break;
	case "WEST":	
		$ArgShave = $amt."x0";
		$lastOperation = "trimmed $amt pixels from left of image";
		$command = "convert -chop $ArgShave -gravity West $inputFileDir $outputFileDir";
		break;
	case "EAST":	
		$ArgShave = $amt."x0";
		$lastOperation = "trimmed $amt pixels from right of image";
		$command = "convert -chop $ArgShave -gravity East $inputFileDir $outputFileDir";
		break;
	case "WESTEAST":	
		$ArgShave = $amt."x0";
		$lastOperation = "trimmed $amt pixels from left and right of image";
		$command = "convert -shave $ArgShave $inputFileDir $outputFileDir";
		break;
	case "ALL":	
		$ArgShave = $amt."x".$amt;
		$lastOperation = "trimmed $amt pixels from all sides of the image";
		$command = "convert -shave $ArgShave $inputFileDir $outputFileDir";
		break;
	default:
		$ArgShave = $amt."x".$amt;
		$lastOperation = "trimmed $amt pixels from all sides of the image";
		$command = "convert -shave $ArgShave $inputFileDir $outputFileDir";
		break;
}


$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
RecordCommand("$command");
RecordCommand("FINAL $outputFilePath");
RecordAndComplete("CROP",$outputFilePath,FALSE);
?>
