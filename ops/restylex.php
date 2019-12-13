<?php
include '../common/common.inc';
$LastOperaton = 'Restyled';


$inputFilePath = GetCurrentImagePath();
$outputFilePath = NewImagePath();
$Region = $_POST['REGION'];


/*
$Arg = $_POST['ARG1'];
$Setting = $_POST['SETTING'];
 */

$path_style = "$PATH_STYLES"."escher1.jpg";

$script = escapeshellcmd("./mlstyle.py $inputFilePath $path_style $outputFilePath");
ExecScript($script);
APPLOG($script);

if ($Region != 'ALL') {

    APPLOG("Applying Region Operation").
    $maskFilePath = GetConversionPath($Region);
    $outputFilePath = ApplyRegionOperation($inputFilePath, $outputFilePath, $maskFilePath);
    $LastOperation .=  " $Region";
}

InformUILayer("RESTYLE",$outputFilePath, null);

?>
