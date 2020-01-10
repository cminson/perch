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

$originalFilePath = $inputFilePath = GetCurrentImagePath();
$inputFilePath = ExtractRegionImage($inputFilePath, $SelectedRegion);

if ($position == 'Append')
{
    $outputFilePath = GetConversionPath();

    $script = "montage -background $backgroundColor -fill $labelColor -geometry +0+0 -font $font -pointsize $pointSize -label \"$label\"  \"$inputFilePath\" \"$outputFilePath\"";

}
else
{
    $labelPath = NewImagePath();
    $script = "convert -background $backgroundColor -fill $labelColor -font $font -pointsize $pointSize label:\"$label\" $labelPath";
    ExecScript($script);
    APPLOG("XLABEL $script");

    $outputFilePath = NewImagePath();
    $script = "composite $labelPath -gravity $position $inputFilePath $outputFilePath";
}

APPLOG("XLABEL $script");
ExecScript($script);
APPLOG("XLABEL FINAL $outputFilePath");

$x = $ExtractedRegionOriginX;
$y = $ExtractedRegionOriginY;
if ($SelectedRegion != 'ALL') 
{
    $regionFilePath = $outputFilePath;
    $outputFilePath = NewImagePath();
    $script = "composite -geometry +$x+$y $regionFilePath $originalFilePath $outputFilePath";
    ExecScript($script);
    APPLOG($script);
    $LastOperation .=  " $SelectedRegion";
}


NotifyUI('LABEL',$outputFilePath,$REGIONS_PREVIOUS);

?>
