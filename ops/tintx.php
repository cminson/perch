<?php
include '../common/common.inc';

if (CompleteWithNoAction()) return;

$LastOperation = 'Tinted';

$TintColor = $_POST['COLOR'];
$TintLevel = $_POST['TINTLEVEL'];
$Region = $_POST['REGION'];
RecordCommand("TINT BEGINS: $TintColor $Region");

if (strlen($TintColor) < 2) { $TintColor = "FF0000"; }

$TintColor = str_replace("#", "", $TintColor);

$inputFilePath = GetConversionPath($_POST['CURRENTIMAGE']);
$inputFileName = basename($inputFileDir);

$imageName = NewImageName($inputFilePath);
$outputFilePath = GetConversionPath($imageName);

$hash = "";
if (ctype_xdigit($TintColor) == TRUE) { $hash = "#"; }

$TintLevel = 100 - $TintLevel;
$command = "convert -fill '$hash$TintColor' -tint $TintLevel $inputFilePath $outputFilePath";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
RecordCommand("$command");

$regionList = GenerateImageRegions($inputFilePath, $outputFilePath);
$regions = Implode(',', $regionList);

if ($Region != 'ALL') {

    RecordCommand("Applying Region Operation").
    $maskFilePath = GetConversionPath($Region);
    $outputFilePath = ApplyRegionOperation($inputFilePath, $outputFilePath, $maskFilePath);
    $LastOperation .=  " $Region";
}
RecordCommand("TINT REGIONS: $regions");

RecordAndComplete("TINT",$outputFilePath,$regions);

?>
