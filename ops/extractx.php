<?php
include '../common/common.inc';

if (CompleteWithNoAction()) return;

$LastOperation = 'Extract Image: ';

$inputFilePath = GetConversionPath($_POST['CURRENTIMAGE']);
$Region = $_POST['REGION'];

if ($Region == 'ALL') {
    $outputFilePath = GetConversionPath($inputFilePath);
    APPLOG('EXTRACT',$outputFilePath,FALSE);
    exit();
}

$imageName = NewImageName();
$outputFilePath = GetConversionPath($imageName);
$outputFileURL = GetConversionURL($imageName);
$maskFilePath = GetConversionPath($Region);
APPLOG("maskFilePath $maskFilePath");

$imageName = NewTMPImageName();
$cutterFilePath = GetConversionPath($imageName);
$command = "convert -transparent white -fuzz 40% $maskFilePath $cutterFilePath";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
APPLOG("$command");

$imageName = NewTMPImageName();
$outputFilePath = GetConversionPath($imageName);
$command = "composite -geometry +0+0 $cutterFilePath $inputFilePath $outputFilePath";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
APPLOG("$command");

$inputFilePath = $outputFilePath;
$imageName = NewTMPImageName();
$outputFilePath = GetConversionPath($imageName);
$command = "convert -fill white -opaque black $inputFilePath $outputFilePath";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
APPLOG("$command");

RecordAndComplete('EXTRACT',$outputFilePath,FALSE);
?>
