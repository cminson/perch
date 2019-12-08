<?php
include '../common/common.inc';

$LastOperation = 'Warped:';

$Arg = $_POST['ARG1'];
$Setting = $_POST['SETTING'];
$Region = $_POST['REGION'];

$originalFilePath = $inputFilePath = GetCurrentImagePath();
$inputFilePath = ExtractRegionImage($inputFilePath, $Region);
APPLOG("WARP inputFilePath: $inputFilePath");

$outputFilePath = NewImagePath();

switch ($Arg)
{
case 'ENCIRCLE':

    $inputFilePath = RemoveTransparency($inputFilePath);
    
    $setting = $Setting * 36;
	$script = "convert -virtual-pixel Background -distort arc $setting  -background transparent +repage $inputFilePath $outputFilePath";
    ExecScript($script);

    $LastOperation = "$LastOperation Bent $Setting";
    break;
case 'EXPLODE':
    $setting = $Setting * -0.5;
	$script = "convert -virtual-pixel Background -implode $setting  -background white +repage $inputFilePath $outputFilePath";
	$script = "convert  -implode $setting $inputFilePath $outputFilePath";
    /*
	$script = "convert  -region 150x150+0+0 -implode $setting $inputFilePath $outputFilePath";
	$script = "convert -region 150x150+0+0 -virtual-pixel Background -implode $setting  -background white +repage $inputFilePath $outputFilePath";
     */
    $LastOperation = "$LastOperation Exploded $Setting";
    break;
case 'IMPLODE':
    $setting = $Setting * 0.5;
	$script = "convert -virtual-pixel Background -implode $setting  -background white +repage $inputFilePath $outputFilePath";
    break;
case 'FRACTALIZE':
    $spread = $Setting;
    $density = $Setting;
    $curve = $Setting;
    $script = "../shells/disperse.sh -s $spread -d $density -c $curve $inputFilePath $outputFilePath";
    $LastOperation = "$LastOperation Fractilized $Setting";
    break;
case 'KAL':
    //$inputFilePath = RemoveTransparency($inputFilePath);

    $setting = $Setting * 36;
    $script = "../shells/kal.sh -m image -o 180  -i $inputFilePath $outputFilePath";
    //ExecScript($script);
    //APPLOG($script);

    $LastOperation = "$LastOperation Kaleidoscoped $Setting";

    //$outputFilePath = ReshapeToRegion($Region, $outputFilePath);

    //$script = "../shells/kal.sh -m disperse -o 0 -s 5 -d 5 -c 10 -n 1 $inputFilePath $outputFilePath";
    break;
case 'PIXEL':
    $script = "convert -scale 10% -scale 1000% $inputFilePath $outputFilePath";
    break;
case 'SPLICE':
    $direction = 'x';
    $setting = $Setting;
    $script = "../shells/stutter.sh -s $setting -d $direction $inputFilePath $outputFilePath";

}

APPLOG("WARP $script");
ExecScript($script);

$x = $ExtractedRegionOriginX;
$y = $ExtractedRegionOriginY;

if ($Region != 'ALL')
{
    $regionFilePath = $outputFilePath;  
    $outputFilePath = NewImagePath();
    $script = "composite -geometry +$x+$y $regionFilePath $originalFilePath $outputFilePath";
    ExecScript($script);
    APPLOG($script);
    $LastOperation .=  " $Region";
}

$regionList = DuplicateImageRegions($originalFilePath, $outputFilePath);
InformUILayer('WARP',$outputFilePath,$regionList);



?>
