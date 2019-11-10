<?php
include "../common/common.inc";


$LastOperation = "File Loaded";

$ErrorCode = 0;
$UploadSuccess = FALSE;
$ImageList = array();

RecordCommand('HERE');

foreach ($_FILES['FRAMEFILENAME']['tmp_name'] as $i => $uploadedFile)
{
	$sourceName = $_FILES['FRAMEFILENAME']['name'][$i];
    RecordCommand("$i $sourceName $uploadedFile");
	if (filesize($uploadedFile) != 0)
	{
		$targetName = NewTMPName($sourceName);
        $outputFileDir = GetConversionDir($targetName);
        $outputFilePath = GetConversionPath($targetName);
        RecordCommand($outputFileDir);

		move_uploaded_file($uploadedFile, $outputFileDir);
		RecordCommand("MOVE: $i $uploadedFile $outputFileDir");
		chmod($outputFileDir,0777);
		RecordCommand("MOVE: $i $uploadedFile $outputFileDir");
		GetImageAttributes($outputFileDir,$width,$height,$size);

		if ($size > 400000)
		{
			if (($width > 900) || ($height > 900))
			{
				$outputFileDir = ResizeImage($outputFileDir, 900, 900, FALSE);
                $outputFilePath = GetConversionPath($outputFileDir);
				RecordCommand("RESIZE $size $width $height");
			}
		}

		if (IsAnimatedGIF($outputFileDir) == TRUE)
		{
			$animList = GetAnimatedImages($outputFileDir);
			$count = count($animList);
			RecordCommand("Animation Seen: $count $outputFileDir");
			for ($i = 0; $i < $count; $i++)
			{
				$image = $animList[$i];
				$image = GetConversionPath($image);
				$ImageList[] = $image;
				RecordCommand("Setting GIF Image = $image");
			}
		}
		else
		{
			$image = GetConversionPath($outputFileDir);
			$ImageList[] = $image;
		}
	}
} // end foreach



$FileList = "";
foreach ($ImageList as $image)
{
	$FileList .= $image.",";
	$UploadSuccess = TRUE;
}
$FileList = trim($FileList,",");


$stats = "";
if ($UploadSuccess == TRUE)
{
	RecordCommand("SUCCESS $FileList");
	echo '<html><head><title>-</title></head><body>';
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo "parent.completeFrameLoad(\"$FileList\",\"$stats\");";
	echo "\n".'</script></body></html>';
}
else
{
	RecordCommand("ERROR $outputFilePath");
	//$ErrorReport = "Error: $Error";
	$ErrorReport = "Error";
	echo '<html><head><title>-</title></head><body>';
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo "parent.reportFrameLoadError(\"$ErrorReport\");";
	echo "\n".'</script></body></html>';
}

?>
