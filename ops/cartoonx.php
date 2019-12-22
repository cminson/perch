<?php
include '../common/common.inc';

$inputFilePath = GetCurrentImagePath();
$LastOperation='Cartoon';

$ArgLevel = $_POST['LEVEL'] + 1;
$ArgEdge = $_POST['EDGE'];
$ArgBrightness = $_POST['BRIGHTNESS'];
$ArgSaturation = $_POST['SATURATION'];
$Region = $_POST['REGION'];


$script = "../shells/cartoon2.sh -n $ArgLevel -e $ArgEdge -b $ArgBrightness -s $ArgSaturation";
$script = "../shells/cartoon2.sh -p 30 -e 4 -n 6";
$outputFilePath = NewImagePath();
$script = "$script $inputFilePath $outputFilePath";
ExecScript($script);

if ($Region != 'ALL')
{
    APPLOG("Applying Region Operation").
    $maskFilePath = GetConversionPath($Region);
    $outputFilePath = ApplyRegionOperation($inputFilePath, $outputFilePath, $maskFilePath);
    $LastOperation .=  " $Region";
}

InformUILayer('CARTORN',$outputFilePath,$REGIONS_PREVIOUS);
?>
