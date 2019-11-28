<?php
include '../common/common.inc';

if (CompleteWithNoAction()) return;

$LastOperation = 'Color Effect: ';

$Arg = $_POST['ARG1'];
$Setting = $_POST['SETTING'];
$Region = $_POST['REGION'];

$inputFileDir = $_POST['CURRENTFILE'];
$inputFileDir = GetConversionDir($inputFileDir);
$originalFileDir = $inputFileDir;

switch ($Arg)
{
case 'BLACKWHITE':
    $LastOperation .= 'Black & White';
    $black = $Setting * 10;
    $white = 100 - $black;
    $threshold_black = strval($black).'%';
    $threshold_white = strval($white).'%';
    $command = "convert -type Grayscale -black-threshold $threshold_black -white-threshold $threshold_white";
    break;
case 'BLEACH':
    $LastOperation .= 'Bleached';
    $inv = 10 - $Setting;
    $setting = strval($inv * 2000);
    $command = "convert -white-threshold $setting";
    break;
case 'CHARCOAL':
    $LastOperation .= 'Charcoaled';
    $command = "convert -charcoal $Setting";
    break;
case 'HEAT':
    $LastOperation .= 'Heated';
    $inv = 10 - $Setting;
    $setting = strval($inv * 10).'%';
    $command = "convert -solarize $setting";
    break;
case 'PAINT':
    $LastOperation .= 'Painted';
    $command = "convert -modulate 100,130 -paint $Setting";
    break;
case 'SEPIA':
    $LastOperation .= 'Sepia';
    $inv = 10 - $Setting;
    $setting = strval($inv * 10).'%';
    $command = "convert -sepia-tone $setting";
    break;
case 'WASH':
    $LastOperation .= 'Washed';
    $setting = strval($Setting * 8).'%';
    $command = "convert -colors 32 -level $setting";
default:
    break;
}

$targetName = NewImageName();
$outputFileDir = GetConversionDir($targetName);
$outputFilePath = GetConversionPath($targetName);

$command = "$command $inputFileDir $outputFileDir";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);

RecordCommand("$command");

if ($Region != 'ALL') {

    RecordCommand("Applying Region Operation").
    $maskFileDir = GetConversionDir($Region);
    $outputFileDir = ApplyRegionOperation($originalFileDir, $outputFileDir, $maskFileDir);
    $outputFilePath = GetConversionPath($outputFileDir);
}

RecordCommand("FINAL $outputFilePath");

RecordAndComplete('COLOR',$outputFilePath,FALSE);
?>
