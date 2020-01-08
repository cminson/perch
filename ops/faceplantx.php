<?php
include '../common/common.inc';
APPLOG('FacePlant');

/*
Paste the source region image into the target region image.

Algorithm:

1) Extract the source image region
2) Resize it to same diminsions as target region
3) Paste the resized source image into the target image
*/

$LastOperation = 'Face Plant: ';

$targetImagePath = GetCurrentImagePath();
//$targetRegionPath = $_POST['REGION'];
$faceRegions = GetImageRegions($targetImagePath, 'FACE');
if (count($faceRegions) == 0) 
{
    APPLOG('No Face Regions');
    CompleteWithNoAction();
    exit(0);
}
$targetRegionPath = $faceRegions[0];
$targetRegionPath = GetConversionPath($targetRegionPath);

APPLOG("Target Region: $targetRegionPath");

$sourceImagePath = GetConversionPath($_POST['ID_SECONDARY_IMAGE_PATH']);
$faceRegionPath = GetConversionPath($_POST['ID_SECONDARY_REGION_PATH']);

$outputImagePath = NewImagePath();

APPLOG("TargetImage: $targetImagePath  TargetRegion: $targetRegionPath  SourceImage: $sourceImagePath  SourceRegion: $faceRegionPath");


/*
 * Generate the images necessary to do a cv2 seamless comoposite
 *
 * 1) The source face, cropped out
 * 2) The mask this face, resized to the target face
 */

$outputImagePath = NewImagePath();
$faceRegionPath = GetConversionPath($faceRegionPath);
APPLOG("faceRegionPath $maskImagePath");

/* prepare face mask */
$cutterImagePath = NewTMPImagePath();
$script = "convert -transparent white -fuzz 40% $faceRegionPath $cutterImagePath";
ExecScript($script);
APPLOG("$script");

/* overlay face mask on source image, getting only the face */
$outputImagePath = NewTMPImagePath();
$script = "composite -geometry +0+0 $cutterImagePath $sourceImagePath $outputImagePath";
ExecScript($script);
APPLOG("$script");

/* make everything around the face transparent */
$inputImagePath = $outputImagePath;
$outputImagePath = NewImagePath();
$script = "convert -fill white -opaque black $inputImagePath $outputImagePath";
$script = "convert -transparent black $inputImagePath $outputImagePath";
ExecScript($script);
APPLOG("$script");

$regionTerms = explode('.', $faceRegionPath);
$termList = $regionTerms[3];
$dims = explode('_', $termList);
$x = intval($dims[0]);
$y = intval($dims[1]);
$w = intval($dims[2]);
$h = intval($dims[3]);
$cropDim = $w."x$h+$x+$y";

/* then crop the face out of the full image */
$inputImagePath = $outputImagePath;
$croppedImagePath = NewImagePath();
$script = "convert -crop $cropDim +repage $inputImagePath $croppedImagePath";
ExecScript($script);
APPLOG("FINAL CROPPED FACE: $script");

/* likewise crop out the mask for this face */
$croppedMaskPath = NewImagePath();
$script = "convert -crop $cropDim +repage $faceRegionPath $croppedMaskPath";
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

/* resize the mask  to the target face size*/
$finalMaskPath = NewImagePath();
$script = "convert $croppedMaskPath -resize $newDim $finalMaskPath";
ExecScript($script);
APPLOG("FINAL SOURCE MASK $script");

/* resize the face size to the target face size */
$finalImagePath = NewImagePath();
$script = "convert $croppedImagePath -resize $newDim $finalImagePath";
ExecScript($script);
APPLOG("FINAL FACE  $script");

$outputImagePath = NewImagePath();
$script = "composite -geometry +$x+$y $finalImagePath $targetImagePath $outputImagePath";
ExecScript($script);
APPLOG("$script");

$outputImagePath = NewImagePath();
$script = escapeshellcmd("python3 ./mlcomposite.py $finalImagePath $finalMaskPath $targetImagePath $outputImagePath $centerX $centerY");
shell_exec($script);
APPLOG("MLCOMPOSITE: $script");


InformUILayer('FACEPLANT', $outputImagePath, $REGIONS_PREVIOUS);
?>
