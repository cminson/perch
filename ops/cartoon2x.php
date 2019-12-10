<?php
include '../zcommon/common.inc';

if (CompleteWithNoAction()) return;


$LastOperation = $X_PHOTOTOCARTOON;

	//get the command parameters
	$Setting = $_POST['SETTING'];
    if (strlen($Setting) < 2)
        $Setting = $DEFAULT;
    $Setting = str_replace(".jpg","",$Setting);
	RecordCommand(" $Setting=$Setting");

    $inputFileDir = $_POST['CURRENTFILE'];
    $inputFileDir = "$BASE_DIR$inputFileDir";
    $inputFileName = basename($inputFileDir);

    $targetName = TMPGIF();
    $outputFileDir = "$CONVERT_DIR$targetName";
    $command = "../zshells/emboss.sh -m 1 -d 4 -c overlay $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    $inputFileDir = $outputFileDir;
    RecordCommand("CARTOON $command $outputFilePath");


    $isAnimated = FALSE;
    if (IsAnimatedGIF($inputFileDir) == TRUE)
    {
        $imageList = GetAnimatedImages($inputFileDir);
        $isAnimated = TRUE;
    }

   if ($isAnimated == TRUE)
    {
        $AnimateString = "";
        foreach ($imageList as $imageFileDir)
        {
            $outputFileName = ConvertImage($imageFileDir);
            $outputFileDir = "$CONVERT_DIR$outputFileName";
            $AnimateString .= "$outputFileDir ";
        }

        // rebuild animation
        $outputFileName = NewNameGIF();
        $outputFileDir = "$CONVERT_DIR$outputFileName";
        $outputFilePath = "$CONVERT_PATH$outputFileName";
        $command = "convert -dispose previous -delay 25 $AnimateString -loop 0 $outputFileDir";
        $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);

    }
    else
    {
		$outputFileName = ConvertImage($inputFileDir);
		$outputFileDir = "$CONVERT_DIR$outputFileName";
		$outputFilePath = "$CONVERT_PATH$outputFileName";
	}

/*
	$inputFileDir = $outputFileDir;
	GetImageAttributes($inputFileDir,$real_width,$real_height,$size);
	if ($size > 500000)
	{
		if (($real_width > 400) || ($real_height > 400))
		{
		$inputFileDir = ResizeImage($inputFileDir,400,400,FALSE);
		$targetName = basename($inputFileDir);
		$outputFilePath = "$CONVERT_PATH$targetName";
		}
	}
*/

	RecordCommand("FINAL $outputFilePath");
    DecrementQuota();

	RecordAndComplete("CARTOON",$outputFilePath,FALSE);



function ConvertImage($inputFileDir)
{
global $CONVERT_DIR, $Setting;


	$Setting = '01';
	switch ($Setting)
	{
	case '01':
		$command = "-p 30 -e 4 -n 6";
		break;
	case '02':
		$command = "-p 50 -e 4 -n 6";
		break;
	case '03':
		$command = "-p 70 -e 4 -n 6";
		break;
	case '04':
		$command = "-p 90 -e 4 -n 6";
		break;
	case '05':
		$command = "-p 70 -e 2 -n 6";
		break;
	case '06':
		$command = "-p 70 -e 4 -n 6";
		break;
	case '07':
		$command = "-p 70 -e 6 -n 6";
		break;
	case '08':
		$command = "-p 70 -e 4 -n 2";
		break;
	case '09':
		$command = "-p 70 -e 4 -n 4";
		break;
	case '10':
		$command = "-p 70 -e 4 -n 6";
		break;
	case '11':
		$command = "-p 50 -e 2 -n 8";
		break;
	case '12':
		$command = "-p 90 -e 6 -n 4";
		break;
	}
	$command = "../zshells/cartoon2.sh $command";

	$targetName = NewName($inputFileDir);

	$outputFileDir = "$CONVERT_DIR$targetName";
	$command = "$command $inputFileDir $outputFileDir";
	$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
	RecordCommand("$command");

	$command = "cp -f $outputFileDir $BASE_DIR/wimages/examples/cartoon2/$Setting".".jpg";
	//$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
	RecordCommand("$command");
	return $targetName;
}
?>
	}
	$command = "../zshells/cartoon.sh $command";

	$targetName = NewName($inputFileDir);

	$outputFileDir = "$CONVERT_DIR$targetName";
	$command = "$command $inputFileDir $outputFileDir";
	$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
	RecordCommand("$command");
	return $targetName;
}
?>
