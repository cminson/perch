<?php
include '../common/common.inc';

$Region = $_POST['REGION'];
$Op = $_POST['OP'];

$LastOperation = "Enhance";
$inputFilePath = GetCurrentImagePath();
$outputFilePath = NewImagePath();

switch ($Op)
{
case 'BLUR':
    //$script = "convert -modulate 100,130 -paint 2  $inputFilePath $outputFilePath";
    $script = "convert -blur 0x3 $inputFilePath $outputFilePath";
    $script = "convert -radial-blur 5  $inputFilePath $outputFilePath";
    $Description = 'Blurred';
    break;
case 'SMEAR':
    $script = "convert -radial-blur 5  $inputFilePath $outputFilePath";
    $script = "convert -motion-blur 0x12+45  $inputFilePath $outputFilePath";
    $Description = 'Smeared';
    break;
case 'SHARPEN':
    $script = "convert -sharpen 0.0x1.0 $inputFilePath $outputFilePath";
    $Description = 'Sharpened';
    break;
case 'UNSHARPEN':
    $script = "convert -unsharp 0.0x1.0 $inputFilePath $outputFilePath";
    $Description = 'Unsharpened';
    break;
case 'ENRICH':
    $script = "../shells/enrich.sh  $inputFilePath $outputFilePath";
    $Description = 'Enriched';
    break;
case 'HARDLIGHT':
    $script = "convert  \( granite: -blur 0x.5 -normalize -fill gray50 -colorize 70% \) -compose hardlight -composite  $inputFilePath $outputFilePath";
    $Description = 'Hard Lit';
    break;
case 'HDR':
    $inputFilePath = RemoveTransparency($inputFilePath);
    $script = "../shells/mkhdr.sh 13  $inputFilePath $outputFilePath";
    $Description = 'HDR';
    break;
case 'INSTANT':
    $script = "convert -normalize $inputFilePath $outputFilePath";
    $Description = 'Normalized';
    break;
case 'NEGATE':
    $script = "convert -negate $inputFilePath $outputFilePath";
    $Description = 'Negated';
    break;
case 'SMOOTH':
    $script = "convert -gaussian 4 $inputFilePath $outputFilePath";
    $Description = 'Smoothed';
    break;
case "SOFTLIGHT":
    $script = "convert \( granite: -blur 0x.5 -normalize \) -compose softlight -composite $inputFilePath $outputFilePath ";
    $Description = 'Soft Lit';
    break;
case 'WASH':
    $script = "convert -colors 32 -level 15%  $inputFilePath $outputFilePath";
    $Description = 'Washed';
    break;
case 'NORMALIZE':
    $script = "convert -normalize  $inputFilePath $outputFilePath";
    $Description = 'Normalized';
    break;
case 'REDUCECOLORS':
    // get current color count and go downwards by halves
    $count = GetColorCount($inputFilePath);
    if ($count == 0) $count = 8;
    if ($count > 1000) $count = 1000;
    $count = $count / 8;
    APPLOG($count);
    $script = "convert +dither -colors $count $inputFilePath $outputFilePath";
    $Description = 'Colors Reduced';
    break;
default:
    $Description = 'Error';
    break;
}
$LastOperation = "$Description: ";

ExecScript($script);
APPLOG($script);

APPLOG("Applying Region Operation").
$maskFilePath = GetConversionPath($Region);
$outputFilePath = ApplyRegionOperation($inputFilePath, $outputFilePath, $maskFilePath);

InformUILayer("ENHANCE",$outputFilePath,$REGIONS_PREVIOUS);

?>
