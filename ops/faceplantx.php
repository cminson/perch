<?php
include '../common/common.inc';
APPLOG('FacePlant');

/*
Paste the source region image into the target region image.
*/

$LastOperation = 'Face Plant: ';

$targetImagePath = GetCurrentImagePath();

$Region = $_POST['REGION'];
$targetRegionPath = GetConversionPath($Region);

/*
if (stristr($Region, 'FACE') == False)
{
    APPLOG('No Face Regions');
    CompleteWithNoAction();
    exit(0);
}
 */

$sourceImagePath = GetConversionPath($_POST['ID_SECONDARY_IMAGE_PATH']);
$sourceRegionPath = GetConversionPath($_POST['ID_SECONDARY_REGION_PATH']);

APPLOG("TargetImage: $targetImagePath  TargetRegion: $targetRegionPath  SourceImage: $sourceImagePath  SourceRegion: $sourceRegionPath");

$regionTerms = explode('.', $sourceRegionPath);
$termList = $regionTerms[3];
$dims = explode('_', $termList);
$x = intval($dims[0]);
$y = intval($dims[1]);
$w = intval($dims[2]);
$h = intval($dims[3]);
$cropDim = $w."x$h+$x+$y";

/* crop the face out of the full image */
$croppedImagePath = NewImagePath();
$script = "convert -crop $cropDim +repage $sourceImagePath $croppedImagePath";
ExecScript($script);
APPLOG("FINAL CROPPED FACE: $script");

if (stristr($Region, 'ALL') != False)
{
    NotifyUI('FACEPLANT', $croppedImagePath, $REGIONS_PREVIOUS);
    exit();
}

/* crop out the mask for this face */
$croppedMaskPath = NewImagePath();
$script = "convert -crop $cropDim +repage $sourceRegionPath $croppedMaskPath";
ExecScript($script);
APPLOG("CROPPED FACE MASK: $script");

$regionTerms = explode('.', $targetRegionPath);
$termList = $regionTerms[3];
$dims = explode('_', $termList);
$x = intval($dims[0]);
$y = intval($dims[1]);
$w = intval($dims[2]);
$h = intval($dims[3]);
$newDim = $w."x"."$h!";

$centerX = intval($x + ($w / 2));
$centerY = intval($y + ($h / 2));

/* resize the face to target face size */
$finalImagePath = NewImagePath();
$script = "convert $croppedImagePath -resize $newDim $finalImagePath";
ExecScript($script);
APPLOG("FINAL FACE  $script");

/* resize the mask  to target face size*/
$finalMaskPath = NewImagePath();
$script = "convert $croppedMaskPath -resize $newDim $finalMaskPath";
ExecScript($script);
APPLOG("FINAL SOURCE MASK $script");

$outputImagePath = NewImagePath();
$script = escapeshellcmd("python3 ./mlcomposite.py $finalImagePath $finalMaskPath $targetImagePath $outputImagePath $centerX $centerY");
shell_exec($script);
APPLOG("MLCOMPOSITE: $script");


/* need to restore foreground? */
if (stristr($Region, 'BACKGROUND') != False)
{
    APPLOG('BACKGROUND');
    $compositeImagePath = $outputImagePath;

    $cutterImagePath = NewTMPImagePath();
    $script = "convert -transparent black -fuzz 40% $targetRegionPath $cutterImagePath";
    ExecScript($script);
    APPLOG("$script");

    $outputImagePath = NewTMPImagePath();
    $script = "composite -geometry +0+0 $cutterImagePath $targetImagePath $outputImagePath";
    ExecScript($script);
    APPLOG("$script");

    $inputImagePath = $outputImagePath;
    $outputImagePath = NewTMPImagePath();
    $script = "convert -transparent white  $inputImagePath $outputImagePath";
    ExecScript($script);
    APPLOG("$script");

    $inputImagePath = $outputImagePath;
    $outputImagePath = NewTMPImagePath();
    $script = "composite -geometry +0+0 $inputImagePath $compositeImagePath $outputImagePath";
    ExecScript($script);
    APPLOG("$script");
}


NotifyUI('FACEPLANT', $outputImagePath, $REGIONS_PREVIOUS);
?>
