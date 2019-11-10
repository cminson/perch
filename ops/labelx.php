<?php
include '../common/common.inc';

if (CompleteWithNoAction()) return;

$LastOperation = 'Labeled';

$labelColor = $_POST['LABELCOLOR'];
$backgroundColor = $_POST['BACKGROUNDCOLOR'];
$pointSize = $_POST['FONTSIZE'];
$font = $_POST['FONTS'];
$label1 = $_POST['LABEL1'];
$label2 = $_POST['LABEL2'];
$position = $_POST['POSITION'];

if (isset($font) == FALSE) $font = "Helvetica";
if (isset($pointSize) == FALSE) $pointSize = 20;

if (strlen($label2) > 0)
    $label = "$label1\n$label2";
else
    $label = "$label1";

$inputFileDir = $_POST['CURRENTFILE'];
$inputFileDir = "$BASE_DIR$inputFileDir";
$inputFileName = basename($inputFileDir);

if ($position == 'Append')
{
    $targetName = NewName($inputFileDir);
    $outputFileDir = GetConversionDir($targetName);
    $outputFilePath = GetConversionPath($targetName);

    $command = "montage -background $backgroundColor -fill $labelColor -geometry +0+0 -font $font -pointsize $pointSize -label \"$label\"  \"$inputFileDir\" \"$outputFileDir\"";

}
else
{
    $targetName = NewNamePNG();
    $labelDir = GetConversionDir($targetName);
    $command = "convert -background $backgroundColor -fill $labelColor -font $font -pointsize $pointSize label:\"$label\" $labelDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    RecordCommand("XLABEL $command");

    $targetName = NewName($inputFileDir);
    $outputFileDir = GetConversionDir($targetName);
    $outputFilePath = GetConversionPath($targetName);
    $command = "composite $labelDir -gravity $position $inputFileDir $outputFileDir";

}

RecordCommand("XLABEL $command");
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
RecordCommand("XLABEL FINAL $outputFilePath");

$outputFilePath = CheckFileSize($outputFileDir);
RecordAndComplete("LABEL",$outputFilePath,FALSE);

?>
