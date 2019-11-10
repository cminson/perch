<?php
include '../common/common.inc';

if (CompleteWithNoAction()) return;


$LastOperation = 'Rotated';

$current = $_POST['CURRENTFILE'];
if (strlen($current) < 2)
{
	return;
}



	$direction = $_POST['DIRECTION'];
	$degrees = $_POST['DEGREES'];
	$adjust = $_POST['ADJUST'];

    if ($direction == 'COUNTERCLOCKWISE')
        $degrees = "-$degrees";

    $LastOperation .= " $degrees";

	//build up the input and output paths
    $inputFileDir = $_POST['CURRENTFILE'];
    $inputFileDir = "$BASE_DIR$inputFileDir";
    //$inputFileDir = ConvertToJPG($inputFileDir);

	$outputFileName = NewName($inputFileDir);
    $outputFileDir = GetConversionDir($outputFileName);
    $outputFilePath = GetConversionPath($outputFileName);

	$command = "convert -rotate";
	if ($adjust == 'on')
	{
		$command = "convert $inputFileDir -distort SRT \"%[fx:aa=$degrees*3.1415/180;(w*abs(sin(aa))+h*abs(cos(aa)))/min(w,h)], $degrees\" $outputFileDir";
	}
	else
	{
		$command = "convert -background white -rotate $degrees $inputFileDir $outputFileDir";
	}
	$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
	RecordCommand("$command");
	RecordCommand("FINAL $outputFilePath");

	RecordAndComplete("ROTATE",$outputFilePath,FALSE);
?>
