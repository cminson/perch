<?php
include '../common/common.inc';
$LastOperation = 'Labeled';

$labelColor = $_POST['LABELCOLOR'];
$backgroundColor = $_POST['BACKGROUNDCOLOR'];
$pointSize = $_POST['FONTSIZE'];
$font = $_POST['FONTS'];
$label = $_POST['LABEL1'];
$position = $_POST['POSITION'];
$SelectedRegion = $_POST['SELECTED_REGION'];

if (isset($font) == FALSE) $font = "Helvetica";
if (isset($pointSize) == FALSE) $pointSize = 20;

$originalImagePath = $inputImagePath = GetCurrentImagePath();
$inputImagePath = ExtractRegionImage($inputImagePath, $SelectedRegion);

if ($position == 'Append')
{
    $outputImagePath = GetConversionPath();

    $script = "montage -background $backgroundColor -fill $labelColor -geometry +0+0 -font $font -pointsize $pointSize -label \"$label\"  \"$inputImagePath\" \"$outputImagePath\"";

}
else
{
    $labelPath = NewImagePath();
    $script = "convert -background $backgroundColor -fill $labelColor -font $font -pointsize $pointSize label:\"$label\" $labelPath";
    ExecScript($script);
    APPLOG("XLABEL $script");

    $outputImagePath = NewImagePath();
    $script = "composite $labelPath -gravity $position $inputImagePath $outputImagePath";
}

APPLOG("XLABEL $script");
ExecScript($script);
APPLOG("XLABEL FINAL $outputImagePath");

$x = $ExtractedRegionOriginX;
$y = $ExtractedRegionOriginY;
if ($SelectedRegion != 'ALL') 
{
    $regionImagePath = $outputImagePath;
    $outputImagePath = NewImagePath();
    $script = "composite -geometry +$x+$y $regionImagePath $originalImagePath $outputImagePath";
    ExecScript($script);
    APPLOG($script);
    $regionName = explode('.', $SelectedRegion)[2];
    $LastOperation .=  "  $regionName";

}


NotifyUI('LABEL',$outputImagePath,$REGIONS_PREVIOUS);

?>
