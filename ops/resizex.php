<?php
include '../common/common.inc';

if (CompleteWithNoAction()) return;

$current = $_POST['CURRENTFILE'];
$clientx = $_POST['CLIENTX'];
$clienty = $_POST['CLIENTY'];
$aspect = $_POST['ASPECT'];

// CJM TODO
if (isset($clientx) == FALSE) $clientx = '200';
if (isset($clienty) == FALSE) $clienty = '200';
if ($clientx <=  0) $clientx = '200';
if ($clienty <=  0) $clienty = '200';

$clientx = preg_replace("[^0-9]", "", $clientx); 
$clienty = preg_replace("[^0-9]", "", $clienty); 

$inputFileDir = $_POST['CURRENTFILE'];
$inputFileDir = "$BASE_DIR$inputFileDir";
$inputFileName = basename($inputFileDir);

GetImageAttributes($inputFileDir,$width1,$height1,$size);

// CJM TODO
if ($clientx > 5000) $clientx = 5000;
if ($clienty > 5000) $clienty = 5000;


$outputFileName = NewName($inputFileDir);
$outputFileDir = GetConversionDir($outputFileName);
$outputFilePath = GetConversionPath($outputFileName);

$command = "convert -resize %ARG $inputFileDir $outputFileDir";
if ($aspect == 'on')
    $command = str_replace("%ARG","$clientx"."x"."$clienty",$command);
else
    $command = str_replace("%ARG","$clientx"."x"."$clienty"."\!",$command);

RecordCommand("XSIZE $command");
RecordCommand("XSIZE FINAL $outputFilePath");
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);

GetImageAttributes($outputFileDir,$width2,$height2,$size);
$LastOperation = "Resized from $width1"."x"."$height1 to $width2"."x"."$height2";


RecordAndComplete("RESIZE",$outputFilePath,FALSE);
?>
