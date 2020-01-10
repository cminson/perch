<?php
include '../common/common.inc';
$LastOperation = 'Extract Image: ';

$originalFilePath = $inputFilePath = GetCurrentImagePath();
$outputFilePath = NewImagePath();

if ($SelectedRegion == 'ALL') {
    $outputFilePath = GetConversionPath($inputFilePath);
    NotifyUI('EXTRACT', $outputFilePath, $REGIONS_NONE);
    APPLOG('EXTRACT',$outputFilePath,FALSE);
    exit();
}
if (stripos($SelectedRegion, 'background') != FALSE)
{

    APPLOG('Background SEEN');
    $maskFilePath = GetConversionPath($SelectedRegion);
    $tmpFilePath1 = NewTMPImagePath();
    $script = "convert -transparent white $maskFilePath $tmpFilePath1";
    ExecScript($script);
    APPLOG($script);

    $tmpFilePath2 = NewTMPImagePath();
    $script = "convert -fill white -opaque black -fuzz 1% $tmpFilePath1 $tmpFilePath2";
    ExecScript($script);
    APPLOG($script);

    $outputFilePath = NewImagePath();
    $script = "composite -geometry +0+0 $tmpFilePath2 $inputFilePath $outputFilePath";
    ExecScript($script);
    APPLOG($script);

    NotifyUI('EXTRACT', $outputFilePath, $REGIONS_NONE);
    exit();

}

$regionTerms = explode('.', $SelectedRegion);
$termList = $regionTerms[3];
$dims = explode('_', $termList);
$x = intval($dims[0]);
$y = intval($dims[1]);
$w = intval($dims[2]);
$h = intval($dims[3]);
$cropDim = $w."x$h+$x+$y";
APPLOG("$cropDim $x $y $w $h");

$outputFilePath = NewImagePath();
$maskFilePath = GetConversionPath($SelectedRegion);
APPLOG("maskFilePath $maskFilePath");

$cutterFilePath = NewTMPImagePath();
$script = "convert -transparent white -fuzz 40% $maskFilePath $cutterFilePath";
ExecScript($script);
APPLOG("$script");

$outputFilePath = NewTMPImagePath();
$script = "composite -geometry +0+0 $cutterFilePath $inputFilePath $outputFilePath";
ExecScript($script);
APPLOG("$script");

$inputFilePath = $outputFilePath;
$outputFilePath = NewImagePath();
$script = "convert -fill white -opaque black $inputFilePath $outputFilePath";
ExecScript($script);
APPLOG("$script");

$inputFilePath = $outputFilePath;
$outputFilePath = NewImagePath();
$script = "convert -crop $cropDim $inputFilePath $outputFilePath";
ExecScript($script);


NotifyUI('EXTRACT', $outputFilePath, $REGIONS_NONE);
?>
