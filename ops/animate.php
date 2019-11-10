<?php
include '../common/common.inc';

$MAX_ROWS = 2;
$MAX_COLS = 4;

// hidden frame image load area 
print "<iframe id=\"ID_FRAMEUPLOAD_TARGET\" name=\"ID_FRAMEUPLOAD_TARGET\" src=\"#\" style=\"width:0;height:0px;border:1px solid #fff; display: none\"></iframe>\n\n";


// hidden submit form
print "<form enctype=\"multipart/form-data\" id=\"ID_FRAME_LOADFORM\" action=\"./ops/loadframex.php\" method=\"post\" target=\"ID_FRAMEUPLOAD_TARGET\">\n";
print "<input type=hidden name=\"MAX_FILE_SIZE\" value=\"8000000\">\n";
print "<div style=\"height:0px;overflow:hidden\">\n";
print  "<input onchange=\"submitFrameFile();\" size=\"90\" maxLength=\"200\" type=\"FILE\" id=\"ID_SUBMIT_FRAMEFILE\" name=\"FRAMEFILENAME[]\" multiple=\"multiple\">\n";
print "</div>\n";
print "</form>\n";


function DisplayAnimTable($current)
{
    global $PATH_IMAGE_NOOP, $PATH_IMAGE_ARROW_UP, $PATH_IMAGE_DEL;
    global $MAX_ROWS, $MAX_COLS;

    print  "<center>\n";
    print  "<input type=\"hidden\" name=\"CURRENTFILE\" value=\"$current\">\n";
    print  "<input type=\"hidden\" name=\"ANIMATION\" value=\"TRUE\">\n";

    RecordCommand($PATH_IMAGE_NOOP, $PATH_IMAGE_ARROW_UP);

    $inputFileDir = GetConversionDir($current);
	if (IsAnimatedGIF($inputFileDir) == TRUE)
	{
		$imageList = GetAnimatedImages($inputFileDir);
		for ($i = 0; $i < count($imageList); $i++)
		{
			$image = $imageList[$i];
			$image = GetConversionPath($image);
			$imageList[$i] = $image;
			RecordCommand("Setting GIF Image = $image");
		}
	}
	else
	{
		if (strlen($current) < 8)
		{
			$image = $PATH_IMAGE_NOOP;
		}
		else
		{
			$image = GetConversionPath($current);
		}
		$imageList[] = $image;
		RecordCommand("Setting Current Image = $image");
	}
	$maxImages = count($imageList);
	$imageCount = 0;
	$c = 1;
	RecordCommand("$inputFileDir $imageList[0] $maxImages");
    print  "<table class=\"selections\" cellspacing=5 cellpadding=5>\n";
	for ($row = 0; $row < $MAX_ROWS; $row++)
	{
		
        print  "<tr>\n";
		for ($col = 0; $col < $MAX_COLS; $col++)
		{
			print  "<td style=\"align: center\">\n";
			print "<center>\n";
			print "<span style=\"font-size: 10\"> $c </span>\n";
			DisplaySep1();
			print "<img onclick=\"deleteFrameImage($c)\" width=\"8\" src=\"$PATH_IMAGE_DEL\">\n";
			print "<br>\n";
			if ($imageCount < $maxImages)
			{
				$image = $imageList[$imageCount];
				RecordCommand("Setting Image $c  = $image");
				print  "<img onclick=\"chooseFrameFile($c)\" style=\"border:1px solid black\" src=\"$image\" width=\"50\"  id=\"FRAME$c\" alt=\"\">\n";
				print  "<input type=\"hidden\" name=\"FRAMEPATH$c\" id=\"FRAMEPATH$c\" value=\"$image\">\n";
				$imageCount++;
			}
			else
			{
				$image = $PATH_IMAGE_NOOP;
				print  "<img onclick=\"chooseFrameFile($c)\" style=\"border:1px solid black\" src=\"$image\" width=\"50\"  id=\"FRAME$c\" alt=\"\">\n";
				print  "<input type=\"hidden\" name=\"FRAMEPATH$c\" id=\"FRAMEPATH$c\" value=\"$image\">\n";
			}
			print "<br>\n";
			print "<img onclick=\"chooseFrameFile($c)\" border=\"0\" src=\"$PATH_IMAGE_ARROW_UP\" width=\"20\" alt=\"\">\n";
			print "</center>\n";
			print "</td>\n";
			$c++;
		}
        print  "</tr>\n";
	}
	print "</table>\n";
}


$X_ANIMMAX="maximum upload is 10M. frames larger than 250k will be ignored";

$current = $_POST['CURRENTFILE'];

DisplayTitle('Frame Animation');
DisplayFormStart();
DisplayAnimTable($current);
DisplayLineSep0();
DisplaySpeedPicker("Speed","TIME");
DisplaySep1();
DisplayLoopPicker("Loop","LOOP");
DisplaySep1();
DisplayCheckBox("Auto Resize","RESIZE",TRUE);
DisplayConvertButton();
DisplayAltNote($X_ANIMMAX);
DisplaySlowNote();


?>
