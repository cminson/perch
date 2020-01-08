<?php
include '../common/common.inc';

$LastOperation = 'Warped';

$Op = $_POST['OP'];
$Setting = $_POST['SETTING'];
$Region = $_POST['REGION'];

$originalFilePath = $inputFilePath = GetCurrentImagePath();
$inputFilePath = ExtractRegionImage($inputFilePath, $Region);
APPLOG("WARP inputFilePath: $inputFilePath");

$outputFilePath = NewImagePath();

switch ($Op)
{
case 'ENCIRCLE':

    $inputFilePath = RemoveTransparency($inputFilePath);
    
    $setting = $Setting * 36;
	$script = "convert -virtual-pixel Background -distort arc $setting  -background transparent +repage $inputFilePath $outputFilePath";
    ExecScript($script);
    $Description = 'Bent';
    break;
case 'EXPLODE':
    $setting = $Setting * -0.5;
	$script = "convert -virtual-pixel Background -implode $setting  -background white +repage $inputFilePath $outputFilePath";
	$script = "convert  -implode $setting $inputFilePath $outputFilePath";
    /*
	$script = "convert  -region 150x150+0+0 -implode $setting $inputFilePath $outputFilePath";
	$script = "convert -region 150x150+0+0 -virtual-pixel Background -implode $setting  -background white +repage $inputFilePath $outputFilePath";
     */
    $Description = "Exploded";
    break;
case 'IMPLODE':
    $setting = $Setting * 0.5;
	$script = "convert -virtual-pixel Background -implode $setting  -background white +repage $inputFilePath $outputFilePath";
    $Description = "Imploded";
    break;
case 'FRACTALIZE':
    $spread = $Setting;
    $density = $Setting;
    $curve = $Setting;
    $script = "../shells/disperse.sh -s $spread -d $density -c $curve $inputFilePath $outputFilePath";
    $Description = "Fractalized";
    break;
case 'KAL':
    //$inputFilePath = RemoveTransparency($inputFilePath);

    $setting = $Setting * 36;
    $script = "../shells/kal.sh -m image -o 180  -i $inputFilePath $outputFilePath";
    //ExecScript($script);
    //APPLOG($script);

    $Description = "Kaleidoscoped";

    //$outputFilePath = ReshapeToRegion($Region, $outputFilePath);

    //$script = "../shells/kal.sh -m disperse -o 0 -s 5 -d 5 -c 10 -n 1 $inputFilePath $outputFilePath";
    break;
case 'PIXEL':
    $script = "convert -scale 10% -scale 1000% $inputFilePath $outputFilePath";
    $script = "convert -scale 1% -scale 10000% $inputFilePath $outputFilePath";
    $Description = "Pixeled";
    break;
case 'SPLICE':
    $direction = 'x';
    $setting = $Setting;
    $script = "../shells/stutter.sh -s $setting -d $direction $inputFilePath $outputFilePath";
    $Description = "Spliced";
}

APPLOG("WARP $script");
ExecScript($script);

$x = $ExtractedRegionOriginX;
$y = $ExtractedRegionOriginY;

if ($Region != 'ALL')
{
    $regionName = explode('.', $Region)[2];

    $regionFilePath = $outputFilePath;  
    $outputFilePath = NewImagePath();
    $script = "composite -geometry +$x+$y $regionFilePath $originalFilePath $outputFilePath";
    ExecScript($script);
    APPLOG($script);
    $LastOperation = "$LastOperation $Description:  $regionName";
}
else
{
    $LastOperation = "$LastOperation $Description:  Entire Image";
}

NotifyUI('WARP',$outputFilePath,$REGIONS_PREVIOUS);



?>
