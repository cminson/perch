<?php
include '../common/common.inc';
APPLOG('TEXTURE');

$LastOperation = 'Textured';

$originalImagePath = $inputImagePath = GetCurrentImagePath();
$textureImagePath = "$PATH_TEXTURES$SelectedOp$GIF_SUFFIX";
$outputImagePath = NewImagePath();

APPLOG($SelectedOp);

switch ($SelectedOp)
{
case 'CURVES':
    $Description = 'Curves';
    $script = "composite -tile $textureImagePath -compose Hardlight $inputImagePath $outputImagePath";
    ExecScript($script);
    APPLOG($script);
	break;
case 'GRANITE':
    $Description = 'Granite';
    $script = "convert -blur 1x5  -shade 20x121.78 -normalize -emboss 4 $inputImagePath $outputImagePath";
    ExecScript($script);
    break;
case 'GLASSTILES':
    $Description = 'Glass';
    $script = "../shells/glasseffects.sh -e disperse -k simple -t double -m displace -a 3 -d 3 -g 3 -w 2 -n 100 $inputImagePath $outputImagePath";
    ExecScript($script);
    break;
case 'SILK':
    $Description = 'Silk Screen';
    $script = "composite $textureImagePath  -tile  -compose Hardlight $inputImagePath $outputImagePath";
    ExecScript($script);
    break;
case 'GOLD':
    $Description = 'Gold Bar';
	$script = "convert -shade 60x21.78 -normalize -raise 9x9 -fill gold -tint 100 $inputImagePath $outputImagePath";
    $execResult = exec("$script 2>&1", $lines, $ConvertResultCode);
	break;
case 'HISTORY':
    $Description = 'Historical';
    GetImageAttributes($inputImagePath,$width,$height,$size);
    $outputImagePath = NewTMPImagePath();
    $script = "convert  -resize $width"."x$height!"." $textureImagePath $outputImagePath";
    ExecScript($script);
	APPLOG($script);

    $textureImagePath = $outputImagePath;
    $outputImagePath = NewTMPImagePath();
    $script = "composite -dissolve 50% $inputImagePath $textureImagePath $outputImagePath";
    ExecScript($script);
	APPLOG($script);

    $inputImagePath = $outputImagePath;

    $outputImagePath = NewTMPImagePath();
    $script = "composite  $inputImagePath $textureImagePath -compose bumpmap -gravity center $outputImagePath";
    ExecScript($script);
	APPLOG($script);

    $inputImagePath = $outputImagePath;
    $outputImagePath = NewTMPImagePath();
    $script = "convert -sharpen 0x0x1.0 -contrast -contrast -contrast -sepia-tone 95% $inputImagePath $outputImagePath";
    ExecScript($script);
	APPLOG($script);
    break;
case 'ICE':
    $Description = 'Ice';
    GetImageAttributes($inputImagePath,$width,$height,$size);

    $outputImagePath = NewTMPImagePath();
    $script = "convert $textureImagePath -colorspace gray  -normalize -fill gray50 -colorize 70% $outputImagePath";
    ExecScript($script);
    $textureImagePath = $outputImagePath;
	APPLOG("TEXTURE $script");

    $outputImagePath = NewImagePath();
    $script = "composite -tile $textureImagePath -dissolve 30% $inputImagePath $outputImagePath";
    ExecScript($script);
	APPLOG("TEXTURE $script");

    $inputImagePath = $outputImagePath;
    $outputImagePath = NewTMPImagePath();
    $script = "convert -blur 1x5 -shade 20x121.78 -normalize -emboss 4 -tint 90 -fill blue $inputImagePath $outputImagePath";
    ExecScript($script);
    break;
case 'MARBLE': 
    $Description = 'Marble';
    $script = "composite -tile $textureImagePath -compose Hardlight $inputImagePath $outputImagePath";
    ExecScript($script);
    break;
case 'METAL':
    $Description = 'Metal';
    $script = "convert -blur 0x1  -shade 120x21.78 -normalize -raise 5x5 -sepia-tone 65% -emboss 3 -modulate 110 -sharpen 0.0x1.0 $inputImagePath $outputImagePath";
    ExecScript($script);
    break;
case 'OLDPAPER':
    $Description = 'Old Parchment';
    GetImageAttributes($inputImagePath,$width,$height,$size);

    $outputImagePath = NewTMPImagePath();
    $script = "convert  -resize $width"."x$height!"." $textureImagePath $outputImagePath";
    ExecScript($script);
    $textureImagePath = $outputImagePath;

    $outputImagePath = NewTMPImagePath();
    $script = "composite -dissolve 50% $inputImagePath $textureImagePath $outputImagePath";
    ExecScript($script);
    $inputImagePath = $outputImagePath;

    $outputImagePath = NewTMPImagePath();
    $script = "composite  $inputImagePath $textureImagePath -compose bumpmap -gravity center $outputImagePath";
    ExecScript($script);
    break;
case 'RIPPLES':
    $Description = 'Pond Ripples';
    $outputImagePath = NewImagePath();
    $script = "../shells/ripples.sh -t d -a 20 -w 25 -o 0 -r 25 -p 0 $inputImagePath $outputImagePath";
    ExecScript($script);
    break;
case 'SAND':
    $Description = 'Sand';
    $script = "convert -emboss 3 -blur 0x1 -shade 60x21.78 -normalize -sepia-tone 65% $inputImagePath $outputImagePath";
    ExecScript($script);
    break;
case 'SKETCH':
    $Description = 'Sketched';
	$script = "convert $inputImagePath -colorspace gray -sketch 0x20+120 $outputImagePath";
    ExecScript($script);
	break;
case 'SNAKES':
    $Description = 'Snakes';
    $script = "composite -tile $textureImagePath -compose Hardlight $inputImagePath $outputImagePath";
    ExecScript($script);
	break;
case 'WETCLAY':
    $Description = 'Wet Clay';
    $script = "convert -shade 120x21.78 -sepia-tone 95% -blur 0x1 -raise 5x5 -paint 4 $inputImagePath $outputImagePath";
    ExecScript($script);
    break;
default:
    $Description = 'Error';
    break;
}

APPLOG($script);

$LastOperation = "$LastOperation $Description: ";


APPLOG("Applying Region Operation").
$maskImagePath = GetConversionPath($SelectedRegion);
$outputImagePath = ApplyRegionOperation($originalImagePath, $outputImagePath, $maskImagePath);

APPLOG("FINAL $outputImagePath");
NotifyUI("TEXTURE",$outputImagePath,$REGIONS_PREVIOUS);

?>
