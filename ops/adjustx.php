<?php
include '../common/common.inc';


$inputImagePath = GetCurrentImagePath();


APPLOG("Vars:  $inputImagePath $SelectedArg1 $SelectedRegion");

switch ($SelectedArg1)
{
case "UP_BRIGHT":
	$LastOperation = "Increased Brightness";
	$script = "convert -modulate 110";
	break;
case "DOWN_BRIGHT":
	$LastOperation = "Decreased Brightness";
	$script = "convert -modulate 90";
	break;
case "UP_CONTRAST":
	$LastOperation = "Increased Contrast";
	$script = "convert -contrast";
	break;
case "DOWN_CONTRAST":
	$LastOperation = "Decreased Contrast";
	$script = "convert +contrast";
	break;
case "UP_HUE":
	$LastOperation = "Increased Hue";
	$script = "convert -modulate 100,100,110";
	break;
case "DOWN_HUE":
	$LastOperation = "Decreased Hue";
	$script = "convert -modulate 100,100,90";
	break;
case "UP_SATURATE":
	$LastOperation = "Increased Saturation";
	$script = "convert -modulate 100,130";
	break;
case "DOWN_SATURATE":
	$LastOperation = "Decreased Saturation";
	$script = "convert -modulate 100,70";
	break;
case "UP_SHARP":
	$LastOperation = "Increased Sharpness";
	$script = "convert -sharpen 0.0x1.0";
	break;
case "DOWN_SHARP":
	$LastOperation = "Decreased Sharpness";
	$script = "convert -unsharp 0.0x1.0";
	break;
default:
    APPLOG("Default $SelectedArg1");
};

$LastOperation .= ':';

$outputImagePath = NewImagePath();
$script = "$script $inputImagePath $outputImagePath";
ExecScript($script);
APPLOG("Script: $script");

APPLOG("Applying Region Operation").
$maskImagePath = GetConversionPath($SelectedRegion);
$outputImagePath = ApplyRegionOperation($inputImagePath, $outputImagePath, $maskImagePath);

APPLOG("FINAL $outputImagePath");

NotifyUI($LastOperation, $outputImagePath, $REGIONS_PREVIOUS);

?>
