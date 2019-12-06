<?php
include '../common/common.inc';
$LastOperation = 'Labeled';

$labelColor = $_POST['LABELCOLOR'];
$backgroundColor = $_POST['BACKGROUNDCOLOR'];
$pointSize = $_POST['FONTSIZE'];
$font = $_POST['FONTS'];
$label1 = $_POST['LABEL1'];
$label2 = $_POST['LABEL2'];
$position = $_POST['POSITION'];
$Region = $_POST['REGION'];

if (isset($font) == FALSE) $font = "Helvetica";
if (isset($pointSize) == FALSE) $pointSize = 20;

if (strlen($label2) > 0)
    $label = "$label1\n$label2";
else
    $label = "$label1";

$originalFilePath = $inputFilePath = GetCurrentImagePath();
$inputFilePath = ExtractRegionImage($inputFilePath, $Region);

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

$x = $TESTX;
$y = $TESTY;
if ($Region != 'ALL') 
{
    $regionFilePath = $outputFilePath;
    $outputFilePath = NewImagePath();
    $script = "composite -geometry +$x+$y $regionFilePath $originalFilePath $outputFilePath";
    ExecScript($script);
    APPLOG($script);
    $LastOperation .=  " $Region";
}


$regionList = DuplicateImageRegions($inputFilePath, $outputFilePath);
InformUILayer('LABEL',$outputFilePath,$regionList);

?>
