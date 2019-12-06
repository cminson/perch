<?php
include '../common/common.inc';
$LastOperation = 'Extract Image: ';

$inputFilePath = GetCurrentImagePath();

$Region = $_POST['REGION'];
if ($Region == 'ALL') {
    $outputFilePath = GetConversionPath($inputFilePath);
    APPLOG('EXTRACT',$outputFilePath,FALSE);
    exit();
}

$outputFilePath = NewImagePath();
$maskFilePath = GetConversionPath($Region);
APPLOG("maskFilePath $maskFilePath");

$cutterFilePath = NewTMPImagePath();
$script = "convert -transparent white -fuzz 40% $maskFilePath $cutterFilePath";
ExecScript($script);
APPLOG("$script");

$outputFilePath = NewTMPImagePath();
$script = "composite -geometry +0+0 $cutterFilePath $inputFilePath $outputFilePath";
ExecScript($script);
APPLOG("$script");

$inputFilePath = $outputFilePath;
$outputFilePath = NewImagePath();
$script = "convert -fill white -opaque black $inputFilePath $outputFilePath";
ExecScript($script);
APPLOG("$script");

DuplicateImageRegions($inputFilePath, $outputFilePath);
RecordAndComplete('EXTRACT',$outputFilePath,FALSE);
?>
