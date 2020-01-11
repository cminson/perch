<?php
include '../common/common.inc';

$LastOperation = 'Warped';


$originalImagePath = $inputImagePath = GetCurrentImagePath();
$inputImagePath = ExtractRegionImage($inputImagePath, $SelectedRegion);
$outputImagePath = NewImagePath();

switch ($SelectedOp)
{
case 'ENCIRCLE':

    $inputImagePath = RemoveTransparency($inputImagePath);
    
    $setting = $SelectedSetting * 36;
	$script = "convert -virtual-pixel Background -distort arc $setting  -background transparent +repage $inputImagePath $outputImagePath";
    ExecScript($script);
    $Description = 'Bent';
    break;
case 'EXPLODE':
    $setting = $SelectedSetting * -0.5;
	$script = "convert -virtual-pixel Background -implode $setting  -background white +repage $inputImagePath $outputImagePath";
	$script = "convert  -implode $setting $inputImagePath $outputImagePath";
    /*
	$script = "convert  -region 150x150+0+0 -implode $setting $inputImagePath $outputImagePath";
	$script = "convert -region 150x150+0+0 -virtual-pixel Background -implode $setting  -background white +repage $inputImagePath $outputImagePath";
     */
    $Description = "Exploded";
    break;
case 'IMPLODE':
    $setting = $SelectedSetting * 0.5;
	$script = "convert -virtual-pixel Background -implode $setting  -background white +repage $inputImagePath $outputImagePath";
    $Description = "Imploded";
    break;
case 'FRACTALIZE':
    $spread = $SelectedSetting;
    $density = $SelectedSetting;
    $curve = $SelectedSetting;
    $script = "../shells/disperse.sh -s $spread -d $density -c $curve $inputImagePath $outputImagePath";
    $Description = "Fractalized";
    break;
case 'KAL':
    //$inputImagePath = RemoveTransparency($inputImagePath);

    $setting = $SelectedSetting * 36;
    $script = "../shells/kal.sh -m image -o 180  -i $inputImagePath $outputImagePath";
    //ExecScript($script);
    //APPLOG($script);

    $Description = "Kaleidoscoped";


    //$script = "../shells/kal.sh -m disperse -o 0 -s 5 -d 5 -c 10 -n 1 $inputImagePath $outputImagePath";
    break;
case 'PIXEL':
    $script = "convert -scale 10% -scale 1000% $inputImagePath $outputImagePath";
    $script = "convert -scale 1% -scale 10000% $inputImagePath $outputImagePath";
    $Description = "Pixeled";
    break;
case 'SPLICE':
    $direction = 'x';
    $setting = $SelectedSetting;
    $script = "../shells/stutter.sh -s $setting -d $direction $inputImagePath $outputImagePath";
    $Description = "Spliced";
    break;
default:
    APPLOG("ERROR: $SelectedOp");
    CompleteWithNoAction();
    exit(0);
}

APPLOG("WARP $script");
ExecScript($script);

$x = $ExtractedRegionOriginX;
$y = $ExtractedRegionOriginY;

if ($SelectedRegion != 'ALL')
{
    $regionName = explode('.', $SelectedRegion)[2];

    $regionImagePath = $outputImagePath;  
    $outputImagePath = NewImagePath();
    $script = "composite -geometry +$x+$y $regionImagePath $originalImagePath $outputImagePath";
    ExecScript($script);
    APPLOG($script);
    $LastOperation = "$LastOperation $Description:  $regionName";
}
else
{
    $LastOperation = "$LastOperation $Description:  Entire Image";
}

NotifyUI('WARP',$outputImagePath,$REGIONS_PREVIOUS);



?>
