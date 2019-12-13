<?php
include '../common/common.inc';
$LastOperation = 'Extract Image: ';

$originalFilePath = $inputFilePath = GetCurrentImagePath();
$outputFilePath = NewImagePath();

$Region = $_POST['REGION'];
if ($Region == 'ALL') {
    $outputFilePath = GetConversionPath($inputFilePath);
    InformUILayer('EXTRACT', $outputFilePath, '');
    APPLOG('EXTRACT',$outputFilePath,FALSE);
    exit();
}
APPLOG($Region);
if (stripos($Region, 'background') != FALSE)
{

    APPLOG('Background SEEN');
    $maskFilePath = GetConversionPath($Region);
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

    InformUILayer('EXTRACT', $outputFilePath, '');
    exit();

}

$regionTerms = explode('.', $Region);
$termList = $regionTerms[3];
$dims = explode('_', $termList);
$x = intval($dims[0]);
$y = intval($dims[1]);
$w = intval($dims[2]);
$h = intval($dims[3]);
$cropDim = $w."x$h+$x+$y";
APPLOG("$cropDim $x $y $w $h");

$outputFilePath = NewImagePath();
$maskFilePath = GetConversionPath($Region);
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


InformUILayer('EXTRACT', $outputFilePath, null);
?>
