<?php
include '../common/common.inc';
$LastOperation = 'Textured';

$ARG_TEXTURE = $_POST['ARG1'];
$ARG_REGION = $_POST['REGION'];

$originalFilePath = $inputFilePath = GetCurrentImagePath();
$textureImagePath = "$PATH_TEXTURES$ARG_TEXTURE$GIF_SUFFIX";

APPLOG("TEXTURE $inputFilePath $ARG_TEXTURE $ARG_REGION $textureImagePath");

$script = "";
$outputFilePath = NewImagePath();

switch ($ARG_TEXTURE)
{
case 'CURVES':
    $LastOperation .= ": Curves";
    $script = "composite -tile $textureImagePath -compose Hardlight $inputFilePath $outputFilePath";
    ExecScript($script);
	break;

case 'GRANITE':
    $LastOperation .= ": Granite";
    $script = "convert -blur 1x5  -shade 20x121.78 -normalize -emboss 4 $inputFilePath $outputFilePath";
    ExecScript($script);
    break;

case 'GLASSTILES':
    $LastOperation .= ": Glass Tiles";
    $script = "../shells/glasseffects.sh -e disperse -k simple -t double -m displace -a 3 -d 3 -g 3 -w 2 -n 100 $inputFilePath $outputFilePath";
    ExecScript($script);
    break;
case 'SILK':
    $LastOperation .= ": Silk Screen";
    $script = "composite $textureImagePath  -tile  -compose Hardlight $inputFilePath $outputFilePath";
    ExecScript($script);
    break;

case 'GOLD':
    $LastOperation .= ": Gold Bar";
	$script = "convert -shade 60x21.78 -normalize -raise 9x9 -fill gold -tint 100 $inputFilePath $outputFilePath";
    $execResult = exec("$script 2>&1", $lines, $ConvertResultCode);
	break;

case 'HISTORY':
    $LastOperation .= ": Historical";
    GetImageAttributes($inputFilePath,$width,$height,$size);
    $outputFilePath = NewTMPImagePath();
    $script = "convert  -resize $width"."x$height!"." $textureImagePath $outputFilePath";
    ExecScript($script);
	APPLOG($script);

    $textureImagePath = $outputFilePath;
    $outputFilePath = NewTMPImagePath();
    $script = "composite -dissolve 50% $inputFilePath $textureImagePath $outputFilePath";
    ExecScript($script);
	APPLOG($script);

    $inputFilePath = $outputFilePath;

    $outputFilePath = NewTMPImagePath();
    $script = "composite  $inputFilePath $textureImagePath -compose bumpmap -gravity center $outputFilePath";
    ExecScript($script);
	APPLOG($script);

    $inputFilePath = $outputFilePath;
    $outputFilePath = NewTMPImagePath();
    $script = "convert -sharpen 0x0x1.0 -contrast -contrast -contrast -sepia-tone 95% $inputFilePath $outputFilePath";
    ExecScript($script);
	APPLOG($script);
    break;

case 'ICE':
    $LastOperation .= ": Glacial Ice";
    GetImageAttributes($inputFilePath,$width,$height,$size);

    $outputFilePath = NewTMPImagePath();
    $script = "convert $textureImagePath -colorspace gray  -normalize -fill gray50 -colorize 70% $outputFilePath";
    ExecScript($script);
    $textureImagePath = $outputFilePath;
	APPLOG("TEXTURE $script");

    $outputFilePath = NewImagePath();
    $script = "composite -tile $textureImagePath -dissolve 30% $inputFilePath $outputFilePath";
    ExecScript($script);
	APPLOG("TEXTURE $script");

    $inputFilePath = $outputFilePath;
    $outputFilePath = NewTMPImagePath();
    $script = "convert -blur 1x5 -shade 20x121.78 -normalize -emboss 4 -tint 90 -fill blue $inputFilePath $outputFilePath";
    ExecScript($script);
    break;

case 'MARBLE': 
    $LastOperation .= ": Ancient Roman Mural";
    $script = "composite -tile $textureImagePath -compose Hardlight $inputFilePath $outputFilePath";
    ExecScript($script);
    break;

case 'METAL':
    $LastOperation .= ": Metal Sheet";
    $script = "convert -blur 0x1  -shade 120x21.78 -normalize -raise 5x5 -sepia-tone 65% -emboss 3 -modulate 110 -sharpen 0.0x1.0 $inputFilePath $outputFilePath";
    ExecScript($script);
    break;

case 'OLDPAPER':
    $LastOperation .= ": Ink on Old Parchment";
    GetImageAttributes($inputFilePath,$width,$height,$size);

    $outputFilePath = NewTMPImagePath();
    $script = "convert  -resize $width"."x$height!"." $textureImagePath $outputFilePath";
    ExecScript($script);
    $textureImagePath = $outputFilePath;

    $outputFilePath = NewTMPImagePath();
    $script = "composite -dissolve 50% $inputFilePath $textureImagePath $outputFilePath";
    ExecScript($script);
    $inputFilePath = $outputFilePath;

    $outputFilePath = NewTMPImagePath();
    $script = "composite  $inputFilePath $textureImagePath -compose bumpmap -gravity center $outputFilePath";
    ExecScript($script);
    break;

case 'RIPPLES':
    $LastOperation .= ": Pond Ripples";
    $outputFilePath = NewImagePath();
    $script = "../shells/ripples.sh -t d -a 20 -w 25 -o 0 -r 25 -p 0 $inputFilePath $outputFilePath";
    ExecScript($script);
    break;

case 'SAND':
    $LastOperation .= ": Sand";
    $script = "convert -emboss 3 -blur 0x1 -shade 60x21.78 -normalize -sepia-tone 65% $inputFilePath $outputFilePath";
    ExecScript($script);
    break;

case 'SKETCH':
    $LastOperation .= ": sketch";
	$script = "convert $inputFilePath -colorspace gray -sketch 0x20+120 $outputFilePath";
    ExecScript($script);
	break;

case 'SNAKES':
    $LastOperation .= ": Snakes";
    $script = "composite -tile $textureImagePath -compose Hardlight $inputFilePath $outputFilePath";
    ExecScript($script);
	break;

case 'WETCLAY':
    $LastOperation .= ": Wet Clay";
    $script = "convert -shade 120x21.78 -sepia-tone 95% -blur 0x1 -raise 5x5 -paint 4 $inputFilePath $outputFilePath";
    ExecScript($script);
    break;


}

APPLOG("$script");

if ($ARG_REGION != 'ALL') {

    APPLOG("Applying Region Operation").
    $maskFilePath = GetConversionPath($ARG_REGION);
    $outputFilePath = ApplyRegionOperation($originalFilePath, $outputFilePath, $maskFilePath);
}

//$outputFilePath = CheckFileSize($outputFilePath);
APPLOG("FINAL $outputFilePath");

InformUILayer("TINT",$outputFilePath,$REGIONS_PREVIOUS);

?>
