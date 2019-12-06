<?php
include '../common/common.inc';
$LastOperation = 'Tinted';

$inputFilePath = GetCurrentImagePath();

$TintColor = $_POST['COLOR'];
$TintLevel = $_POST['TINTLEVEL'];
$Region = $_POST['REGION'];
$regionList = GetImageRegions($inputFilePath);

if (strlen($TintColor) < 2) { $TintColor = "FF0000"; }
$TintColor = str_replace("#", "", $TintColor);
$hash = "";
if (ctype_xdigit($TintColor) == TRUE) { $hash = "#"; }

$TintLevel = 100 - $TintLevel;

$outputFilePath = NewImagePath();
$script = "convert -fill '$hash$TintColor' -tint $TintLevel $inputFilePath $outputFilePath";
ExecScript($script);
APPLOG($script);

if ($Region != 'ALL') {

    APPLOG("Applying Region Operation").
    $maskFilePath = GetConversionPath($Region);
    $outputFilePath = ApplyRegionOperation($inputFilePath, $outputFilePath, $maskFilePath);
    $LastOperation .=  " $Region";
}

DuplicateImageRegions($inputFilePath, $outputFilePath);
RecordAndComplete("TINT",$outputFilePath,$regionList);

?>
