<?php
include '../common/common.inc';

$LastOperation = 'Enhance: ';

$Arg = $_POST['ARG1'];
$Setting = $_POST['SETTING'];
$Region = $_POST['REGION'];

$inputFilePath = GetCurrentImagePath();

switch ($Arg)
{
case 'BEAUTY':
    $script = "../shells/lucasarteffect.sh";
    break;
case 'BLUR':
    $script = "convert -modulate 100,130 -paint $Setting";
    break;
case 'ENRICH':
    $script = "../shells/enrich.sh";
    break;
case 'HARDLIGHT':
    $script = "convert  \( granite: -blur 0x.5 -normalize -fill gray50 -colorize 70% \) -compose hardlight -composite";
    break;
case 'HDR':
    $inputFilePath = RemoveTransparency($inputFilePath);
    $script = "../shells/mkhdr.sh 13";
    break;
case 'INSTANT':
    $script = "convert -normalize";
    break;
case 'NEGATE':
    $script = "convert -negate";
    break;
case 'SMOOTH':
    $script = "convert -gaussian 2";
    break;
case "SOFTLIGHT":
    $script = "convert \( granite: -blur 0x.5 -normalize \) -compose softlight -composite ";
    break;

case 'ABSTRACT':
    $script = "convert -modulate 100,130 -paint 9";
    break;
case 'PAINT':
    $script = "convert -paint 2";
    break;
case 'COLORIZE':
    $script = 'convert -colorize 270';
    break;
case 'CHARCOAL':
    $script = 'convert -charcoal 5';
    break;
case 'SEPIA':
    $script = "convert -sepia-tone  50%";
    break;
case 'VIGNETTE':
    $script = "convert -background white -vignette 10x20 +repage";
    break;

case 'BLENDER':
    $script = "../shells/recursion.sh -d 30 -a 90 -r 10 -z 0.85 -i 10";
    break;
case 'TWILIGHT':
    $script = "convert -black-threshold 50%";
    break;
case 'PENCIL':
    $script = "convert -edge 3";
    break;
case 'WASH':
    $script = "convert -colors 32 -level 15%";
    break;
case 'FOSSIL':
    $script = "convert -blur 0x1  -shade 120x21.78 -normalize -raise 5x5";
    break;
case 'SKETCH':
    $script = "convert -white-threshold 8000";
    break;
case 'NORMALIZE':
    $script = "convert -normalize";
    break;
case 'REDUCECOLORS':
    // get current color count and go downwards by halves
    $count = GetColorCount($inputFilePath);
    if ($count == 0) $count = 2;
    if ($count > 1000) $count = 1000;
    $count = $count / 2;
    APPLOG($count);
    $script = "convert +dither -colors $count";
    break;
case 'FLIPVERT':
    $script = "convert -flip";
    break;
case 'FLIPHORI':
    $script = "convert -flop";
    break;


default:
    break;
}

$outputFilePath = NewImagePath();
$script = "$script $inputFilePath $outputFilePath";
ExecScript($script);
APPLOG($script);

if ($Region != 'ALL') {

    APPLOG("Applying Region Operation").
    $maskFilePath = GetConversionPath($Region);
    $outputFilePath = ApplyRegionOperation($inputFilePath, $outputFilePath, $maskFilePath);
    $LastOperation .=  " $Region";
}

InformUILayer("ENHANCE",$outputFilePath,'');

?>
