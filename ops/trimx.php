<?php
include '../zcommon/common.inc';


if (CompleteWithNoAction()) return;


RecordCommand('ENTER');
$current = $_POST['CURRENTFILE'];
$percent = $_POST['PERCENT'];

$LastOperation = "trimmed $percent% from image sides";

$inputFileDir = $_POST['CURRENTFILE'];
$inputFileDir = "$BASE_DIR$inputFileDir";
GetImageAttributes($inputFileDir,$w,$h,$size);


$outputFileName = NewName($inputFileDir);
$outputFileDir = "$CONVERT_DIR$outputFileName";
$outputFilePath = "$CONVERT_PATH$outputFileName";

$x = intval($w * ($percent/200));
$y = intval($h * ($percent/200));
$command = "convert -shave $x"."x".$y;
$command = "$command $inputFileDir $outputFileDir";
RecordCommand("$command");
RecordCommand("FINAL $outputFilePath");
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
DecrementQuota();

RecordAndComplete("CROP",$outputFilePath,FALSE);

?>

