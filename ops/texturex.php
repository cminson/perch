<?php
include '../common/common.inc';

if (CompleteWithNoAction()) return;

$LastOperation = 'Textured';
$Arg = $_POST['ARG1'];
$Region = $_POST['REGION'];

RecordCommand("Texture $Arg");


$inputFileDir = GetConversionDir($_POST['CURRENTIMAGE']);

switch ($Arg)
{
case 'oldwall':
case 'silk':
case 'oldpaper':
case 'marble':
case 'history':
case 'ice':
case 'brick':
case 'ripples':
case 'glasstiles':
case 'curves':
$inputFileDir = ConvertToJPG($inputFileDir);
break;
}

RecordCommand("$inputFileDir $Arg");


$targetName = NewImageName($inputFileDir);
$outputFileName = $targetName;
$outputFileDir = GetConversionDir($outputFileName);
$outputFilePath = GetConversionPath($outputFileName);

#$textureImageDir = "$TEXTURE_DIR$Arg$GIFSUFFIX";


switch ($Arg)
{
case 'oldwall': //plaster
    $LastOperation .= ": Plaster";
case 'MARBLE': //roman mural
    $LastOperation .= ": Ancient Roman Mural";
    $command = "composite -tile $textureImageDir -compose Hardlight $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    break;
case 'wetclay':
    $LastOperation .= ": Wet Clay";
    $command = "convert -shade 120x21.78 -sepia-tone 95% -blur 0x1 -raise 5x5 -paint 4 $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    break;
case 'METAL':
    $LastOperation .= ": Metal Sheet";
    $command = "convert -blur 0x1  -shade 120x21.78 -normalize -raise 5x5 -sepia-tone 65% -emboss 3 -modulate 110 -sharpen 0.0x1.0 $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    break;
case 'GRANITE':
    $LastOperation .= ": Granite";
    $command = "convert -blur 1x5  -shade 20x121.78 -normalize -emboss 4 $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    break;
case 'glasstiles':
    $LastOperation .= ": Glass Tiles";
    $command = "../shells/glasseffects.sh -e disperse -k simple -t double -m displace -a 3 -d 3 -g 3 -w 2 -n 100 $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
	RecordCommand($lines[0]);
    break;
case 'SAND':
    $LastOperation .= ": Sand";
    $command = "convert -emboss 3 -blur 0x1 -shade 60x21.78 -normalize -sepia-tone 65% $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    break;
case 'silk':
    $LastOperation .= ": Silk Screen";
    $command = "composite $BASE_DIR/wimages/tiles/texture_fabric.gif  -tile  -compose Hardlight $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    break;

case 'history':
    $LastOperation .= ": Historical";
    GetImageAttributes($inputFileDir,$width,$height,$size);
    $textureImageDir = "$TEXTURE_DIR"."oldpaper1.gif";

    $targetName = NewTMPImageName($inputFileDir);
    $outputFileDir = "$CONVERT_DIR$targetName";
    $command = "convert  -resize $width"."x$height!"." $textureImageDir $outputFileDir";
	RecordCommand($command);
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    $textureImageDir = $outputFileDir;

    $targetName = NewTMPImageName($inputFileDir);
    $outputFileDir = "$CONVERT_DIR$targetName";
    $command = "composite -dissolve 50% $inputFileDir $textureImageDir $outputFileDir";
	RecordCommand($command);
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    $inputFileDir = $outputFileDir;

    $targetName = NewTMPImageName($inputFileDir);
    $outputFileDir = "$CONVERT_DIR$targetName";
    $command = "composite  $inputFileDir $textureImageDir -compose bumpmap -gravity center $outputFileDir";
	RecordCommand($command);
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    $inputFileDir = $outputFileDir;

    $targetName = NewTMPImageName($inputFileDir);
    $outputFileDir = "$CONVERT_DIR$targetName";
    $outputFilePath = "$CONVERT_PATH$targetName";
	RecordCommand($command);
    $command = "convert -sharpen 0x0x1.0 -contrast -contrast -contrast -sepia-tone 95% $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    break;
case 'oldpaper':
    $LastOperation .= ": Ink on Old Parchment";
    GetImageAttributes($inputFileDir,$width,$height,$size);
    $textureImageDir = "$TEXTURE_DIR"."oldpaper1.gif";

    $targetName = NewTMPImageName($inputFileDir);
    $outputFileDir = "$CONVERT_DIR$targetName";
    $command = "convert  -resize $width"."x$height!"." $textureImageDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    $textureImageDir = $outputFileDir;

    $targetName = NewTMPImageName($inputFileDir);
    $outputFileDir = "$CONVERT_DIR$targetName";
    $command = "composite -dissolve 50% $inputFileDir $textureImageDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    $inputFileDir = $outputFileDir;

    $targetName = NewTMPImageName($inputFileDir);
    $outputFileDir = "$CONVERT_DIR$targetName";
    $outputFilePath = "$CONVERT_PATH$targetName";
    $command = "composite  $inputFileDir $textureImageDir -compose bumpmap -gravity center $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    break;
case 'ice':
    $LastOperation .= ": Glacial Ice";
    GetImageAttributes($inputFileDir,$width,$height,$size);
    $textureImageDir = "$TEXTURE_DIR"."paper10.gif";

    $outputFileName = NewTMPImageName($inputFileDir);
    $outputFileDir = "$CONVERT_DIR$outputFileName";
    $command = "convert $textureImageDir -colorspace gray  -normalize -fill gray50 -colorize 70% $outputFileDir";
    // $command = "convert $textureImageDir colorize 70% -colorspace gray  -normalize -fill gray50 $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    $textureImageDir = $outputFileDir;
	RecordCommand("TEXTURE $command");

    $outputFileName = NewTMPImageName($inputFileDir);
    $outputFileDir = "$CONVERT_DIR$outputFileName";
    $outputFilePath = "$CONVERT_PATH$outputFileName";
    $command = "composite -tile $textureImageDir -dissolve 30% $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
	RecordCommand("TEXTURE $command");

    $inputFileDir = $outputFileDir;
    $outputFileName = NewTMPImageName($inputFileDir);
    $outputFileDir = "$CONVERT_DIR$outputFileName";
    $outputFilePath = "$CONVERT_PATH$outputFileName";
    $command = "convert -blur 1x5 -shade 20x121.78 -normalize -emboss 4 -tint 90 -fill blue $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);

    break;
case 'ripples':
    $LastOperation .= ": Pond Ripples";
    $targetName = NewName($inputFileDir);
    $outputFileDir = "$CONVERT_DIR$targetName";
    $outputFilePath = "$CONVERT_PATH$targetName";
    $command = "../shells/ripples.sh -t d -a 20 -w 25 -o 0 -r 25 -p 0 $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);

    break;
case 'snaketrails':
    $LastOperation .= ": Snakes";
    $command = "composite -tile $textureImageDir -compose Hardlight $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
	break;
case 'curves':
    $LastOperation .= ": Curves";
    $command = "composite -tile $textureImageDir -compose Hardlight $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
	break;
case 'brick':
    $LastOperation .= ": brick";
    $command = "composite -tile $textureImageDir -compose Hardlight $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
	break;
case 'goldbar':
    $LastOperation .= ": Gold Bar";
	$command = "convert -shade 60x21.78 -normalize -raise 9x9 -fill gold -tint 100 $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
	break;
case 'sketch':
    $LastOperation .= ": sketch";
	$command = "convert $inputFileDir -colorspace gray -sketch 0x20+120 $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
	break;
}

RecordCommand("$command");

if ($Region != 'ALL') {

    RecordCommand("Applying Region Operation").
    $maskFileDir = GetConversionDir($Region);
    $outputFileDir = ApplyRegionOperation($inputFileDir, $outputFileDir, $maskFileDir);
    $outputFilePath = GetConversionPath($outputFileDir);
}


//$outputFilePath = CheckFileSize($outputFileDir);
RecordCommand("FINAL $outputFilePath");

RecordAndComplete("TEXTURE",$outputFilePath,FALSE);
?>
