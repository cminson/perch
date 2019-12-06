<?php
include '../common/common.inc';
$LastOperation = 'Extract Image: ';

$originalFilePath = $inputFilePath = GetCurrentImagePath();

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

$tmpFilePath = $outputFilePath;
$outputFilePath = NewImagePath();
$script = "convert -fill white -opaque black $tmpFilePath $outputFilePath";
ExecScript($script);
APPLOG("$script");

$regionList = DuplicateImageRegions($originalFilePath, $outputFilePath);
InformUILayer('EXTRACT', $outputFilePath, $regionList);
?>
