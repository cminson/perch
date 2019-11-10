<?php
include '../common/common.inc';

RecordCommand('pythonx.php');

$inputFileDir = $_POST['CURRENTFILE'];
$inputFileDir = "$BASE_DIR$inputFileDir";

$path_style = "$DIR_STYLES"."escher1.jpg";

$command = escapeshellcmd("./mlstyle.py $inputFileDir $path_style");
$output = shell_exec($command);
RecordCommand("pythonx.php $command");
$outputFilePath = GetConversionPath($output);
RecordCommand("pythonx.php $output $outputFilePath");

RecordAndComplete('Python Gateway',$outputFilePath,TRUE);



?>
