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

$inputFileDir = GetConversionDir($_POST['CURRENTIMAGE']);
$inputFileName = basename($inputFileDir);

$targetName = NewImageName($inputFileDir);
$outputFileDir = GetConversionDir($targetName);
$outputFilePath = GetConversionPath($targetName);

$hash = "";
if (ctype_xdigit($TintColor) == TRUE) { $hash = "#"; }

$TintLevel = 100 - $TintLevel;
$command = "convert -fill '$hash$TintColor' -tint $TintLevel $inputFileDir $outputFileDir";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
RecordCommand("$command");

if ($Region != 'ALL') {

    RecordCommand("Applying Region Operation").
    $maskFileDir = GetConversionDir($Region);
    $outputFileDir = ApplyRegionOperation($inputFileDir, $outputFileDir, $maskFileDir);
}

$outputFilePath = CheckFileSize($outputFileDir);
RecordCommand("FINAL $outputFilePath");
RecordAndComplete("TINT",$outputFilePath,FALSE);

?>
