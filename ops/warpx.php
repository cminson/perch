<?php
include '../common/common.inc';

if (CompleteWithNoAction()) return;

$LastOperation = 'Warped:';
$Arg = $_POST['ARG1'];
$Setting = $_POST['SETTING'];
$Region = $_POST['REGION'];


$inputFileDir = $_POST['CURRENTIMAGE'];
$inputFileDir = "$BASE_DIR$inputFileDir";

$imageName = NewImageName($inputFileDir);
$outputFileDir = GetConversionDir($imageName);
$outputFilePath = GetConversionPath($imageName);

switch ($Arg)
{
case 'ENCIRCLE':
    $setting = $Setting * 36;
	$command = "convert -virtual-pixel Background -distort arc $setting  -background white +repage $inputFileDir $outputFileDir";
    $LastOperation = "$LastOperation Bent $Setting";
    break;
case 'EXPLODE':
    $setting = $Setting * -0.5;
	$command = "convert -virtual-pixel Background -implode $setting  -background white +repage $inputFileDir $outputFileDir";
	$command = "convert  -implode $setting $inputFileDir $outputFileDir";
    /*
	$command = "convert  -region 150x150+0+0 -implode $setting $inputFileDir $outputFileDir";
	$command = "convert -region 150x150+0+0 -virtual-pixel Background -implode $setting  -background white +repage $inputFileDir $outputFileDir";
     */
    $LastOperation = "$LastOperation Exploded $Setting";
    break;
case 'IMPLODE':
    $setting = $Setting * 0.5;
	$command = "convert -virtual-pixel Background -implode $setting  -background white +repage $inputFileDir $outputFileDir";
    break;
case 'FRACTALIZE':
    $spread = $Setting;
    $density = $Setting;
    $curve = $Setting;
    $command = "../shells/disperse.sh -s $spread -d $density -c $curve $inputFileDir $outputFileDir";
    $LastOperation = "$LastOperation Fractilized $Setting";
    break;
case 'KAL':
    $setting = $Setting * 36;
    $command = "../shells/kal.sh -m image -o 180  -i $inputFileDir $outputFileDir";
    $LastOperation = "$LastOperation Kaleidoscoped $Setting";
    //$command = "../shells/kal.sh -m disperse -o 0 -s 5 -d 5 -c 10 -n 1 $inputFileDir $outputFileDir";
    break;
case 'PIXEL':
    $command = "convert -scale 10% -scale 1000% $inputFileDir $outputFileDir";
    break;
case 'SPLICE':
    $direction = 'x';
    $setting = $Setting;
    $command = "../shells/stutter.sh -s $setting -d $direction $inputFileDir $outputFileDir";

}
RecordCommand("WARP $command");

$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);

if ($Region != 'ALL') {

    RecordCommand("Applying Region Operation").
    $maskFileDir = GetConversionDir($Region);
    $outputFileDir = ApplyRegionOperation($inputFileDir, $outputFileDir, $maskFileDir);
    $outputFilePath = GetConversionPath($outputFileDir);
}


RecordCommand("FINAL $outputFilePath");
RecordAndComplete("BEND",$outputFilePath,FALSE);


?>
