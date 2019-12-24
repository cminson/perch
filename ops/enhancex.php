<?php
include '../common/common.inc';


$Arg = $_POST['ARG1'];
$Setting = $_POST['SETTING'];
$Region = $_POST['REGION'];

$LastOperation = "Enhance";
$inputFilePath = GetCurrentImagePath();

$outputFilePath = NewImagePath();
$desc = 'None';
switch ($Arg)
{
case 'TOON1':
    $script = "../shells/toon.sh -m 1 $inputFilePath $outputFilePath";
    $desc = 'Cartoon Toon1';
    break;
case 'TOON2':
    $script = "../shells/toonify.sh -b 4 -t 10 -e DoG -q 0 $inputFilePath $outputFilePath";
    break;
case 'TOON3':
    $script = "../shells/toonizarro.sh -f 1 -t 0 $inputFilePath $outputFilePath";
    break;
case 'TURBULENCE':
    $script = "../shells/turbulence.sh -s 10 -d 20 $inputFilePath $outputFilePath";
    break;
case 'VIBRANCY':
    $script = "../shells/vibrance3.sh -a 5 $inputFilePath $outputFilePath";
    break;
case 'WOODCUT':
    $script = "../shells/woodcut.sh -k burn -d 50 $inputFilePath /var/www/perch/resources/textures/woodcut1.jpg $outputFilePath";
    break;
case 'ZERO':
    $script = "../shells/zerocrossing.sh -e sobel -l 5 -a 400 $inputFilePath $outputFilePath";
    break;
case 'OUTLINE':
    $script = "convert $inputFilePath -colorspace gray \( +clone -blur 0x2 \) +swap -compose divide -composite -linear-stretch 5%x0% $outputFilePath";
    $desc = 'Outlined';
    break;

case 'BEAUTY':
    $script = "../shells/lucasarteffect.sh $inputFilePath $outputFilePath";
    break;
case 'BLUR':
    $script = "convert -modulate 100,130 -paint $Setting  $inputFilePath $outputFilePath";
    $desc = 'Blurred';
    break;
case 'ENRICH':
    $script = "../shells/enrich.sh";
    break;
case 'HARDLIGHT':
    $script = "convert  \( granite: -blur 0x.5 -normalize -fill gray50 -colorize 70% \) -compose hardlight -composite  $inputFilePath $outputFilePath";
    break;
case 'HDR':
    $inputFilePath = RemoveTransparency($inputFilePath);
    $script = "../shells/mkhdr.sh 13  $inputFilePath $outputFilePath";
    break;
case 'INSTANT':
    $script = "convert -normalize $inputFilePath $outputFilePath";
    break;
case 'NEGATE':
    $script = "convert -negate $inputFilePath $outputFilePath";
    break;
case 'SMOOTH':
    $script = "convert -gaussian 2";
    break;
case "SOFTLIGHT":
    $script = "convert \( granite: -blur 0x.5 -normalize \) -compose softlight -composite $inputFilePath $outputFilePath ";
    break;

case 'ABSTRACT':
    $script = "convert -modulate 100,130 -paint 9  $inputFilePath $outputFilePath";
    $desc = 'Abstracted';
    break;
case 'PAINT':
    $script = "convert -paint 2  $inputFilePath $outputFilePath";
    $desc = 'Painted';
    break;
case 'COLORIZE':
    $script = "convert -colorize 270  $inputFilePath $outputFilePath";
    break;
case 'CHARCOAL':
    $script = "convert -charcoal 5  $inputFilePath $outputFilePath";
    break;
case 'SEPIA':
    $script = "convert -sepia-tone  50%  $inputFilePath $outputFilePath";
    break;
case 'VIGNETTE':
    $script = "convert -background white -vignette 10x20 +repage  $inputFilePath $outputFilePath";
    break;

case 'BLENDER':
    $script = "../shells/recursion.sh -d 30 -a 90 -r 10 -z 0.85 -i 10  $inputFilePath $outputFilePath";
    break;
case 'TWILIGHT':
    $script = "convert -black-threshold 50%  $inputFilePath $outputFilePath";
    break;
case 'PENCIL':
    $script = "convert -edge 3  $inputFilePath $outputFilePath";
    break;
case 'WASH':
    $script = "convert -colors 32 -level 15%  $inputFilePath $outputFilePath";
    break;
case 'FOSSIL':
    $script = "convert -blur 0x1  -shade 120x21.78 -normalize -raise 5x5  $inputFilePath $outputFilePath";
    break;
case 'SKETCH':
    $script = "convert -white-threshold 8000  $inputFilePath $outputFilePath";
    break;
case 'NORMALIZE':
    $script = "convert -normalize  $inputFilePath $outputFilePath";
    break;
case 'REDUCECOLORS':
    // get current color count and go downwards by halves
    $count = GetColorCount($inputFilePath);
    if ($count == 0) $count = 2;
    if ($count > 1000) $count = 1000;
    $count = $count / 2;
    APPLOG($count);
    $script = "convert +dither -colors $count $inputFilePath $outputFilePath";
    break;
case 'FLIPVERT':
    $script = "convert -flip $inputFilePath $outputFilePath";
    break;
case 'FLIPHORI':
    $script = "convert -flop  $inputFilePath $outputFilePath";
    break;


default:
    break;
}
$LastOperation = "$LastOperation $desc: ";

ExecScript($script);
APPLOG($script);

if ($Region != 'ALL') {

    APPLOG("Applying Region Operation").
    $maskFilePath = GetConversionPath($Region);
    $outputFilePath = ApplyRegionOperation($inputFilePath, $outputFilePath, $maskFilePath);
}

InformUILayer("ENHANCE",$outputFilePath,$REGIONS_PREVIOUS);

?>
