<?php
include '../common/common.inc';

if (CompleteWithNoAction()) return;

$LastOperation = 'Color Effect: ';
$inputFileDir = $_POST['CURRENTFILE'];
$inputFileDir = "$BASE_DIR$inputFileDir";


$Arg = $_POST['ARG1'];
$Setting = $_POST['SETTING'];

$path_style = "$DIR_STYLES"."escher1.jpg";

$command = escapeshellcmd("./mlstyle.py $inputFileDir $path_style");
$output = shell_exec($command);
$outputFilePath = GetConversionPath($output);
RecordCommand("pythonx.php $output $outputFilePath");
RecordCommand("FINAL $outputFilePath");

RecordAndComplete('Style',$outputFilePath,TRUE);
?>
