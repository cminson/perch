<?php
include "../common/common.inc";



$LastOperation = $X_FILELOADED;

$UploadSuccess = TRUE;
$ErrorCode = 0;


$UploadSuccess = FALSE;

$tmpName = $_FILES['FRAMEFILENAME']['tmp_name']; 

$sourceFilePath = $_FILES['FRAMEFILENAME']['name']; 
$sourceName = basename($sourceFilePath); 
$targetName = $sourceName;
RecordCommand("tmp=$tmpName source=$sourceFilePath");


//
// special case hack: 
// check for jpeg suffix. if exists, convert it to jpg
// (for simplicity, we assume all file endings are 3 chars)
//
$suffix = GetSuffix($sourceName);
if (((stristr($suffix,"jpeg")) != FALSE) || (strlen($suffix) < 1))
{
	$sourceName = StripSuffix($sourceName);
	$sourceName .= "$JPGSUFFIX";
	$targetName = $sourceName;
}

	//
	// if nothing loaded, see if we have a previous file
	// we were working on.  if this file exists, use it.
	// if it doesnt, then report an error.
	//
	if (empty($sourceName))
	{
		//upload failed because no data entered
		$ErrorCode = 1;
		$Error = "No File Specified";
		//RecordCommand("XLOAD Error=$ErrorCode");
	}
	else if (!IsValidImageFormat($sourceName))
	{
		//upload failed due to this is not an image type we deal with
		$ErrorCode = 5;
		$Error = "File Bad Format";
		//RecordCommand("XLOAD Error=$ErrorCode");
	}
	else if (!is_uploaded_file($tmpName))
	{
		//upload failed due to size constraints or non-existence
		$Error = "File Too Large Or Doe Not Exist";
		$ErrorCode = 2;
		$t = ini_get('upload_max_filesize');
		//RecordCommand("XLOAD $errmsg $t Error=$ErrorCode");
	}
	else if (filesize($tmpName) != 0)
	{
		//means upload from a remote file system succeeded.  
		//move the tmp file into our convert directory.
		//this is our starting point for all future conversions
		//$targetName = preg_replace('/[^a-zA-Z0-9\s]/', '', $targetName);
		$targetName = NewName($targetName);
		$outputFileDir = GetConversionDir($targetName);
		$outputFilePath = GetConversionPath($targetName);
		move_uploaded_file($tmpName, $outputFileDir);
		//RecordCommand("XLOAD: MOVE $tmpName $outputFileDir");

		// if a tiff, just convert to a jpg here give tifs 
		// cause us grief downstream
		if (IsValidTIF($outputFileDir))
		{
			$outputFileDir = ConvertTIF($outputFileDir);
			$targetName = basename($outputFileDir);
			$outputFilePath = "$CONVERT_PATH$targetName";
			//RecordCommand("TIFF Convert $outputFileDir $targetName");
			$LastOperation .= "- Automatically Converted to JPG";
		}



		if (IsAnimatedNonGIF($outputFileDir))
		{
			$outputFileDir = ConvertToGIF($outputFileDir);
			$targetName = basename($outputFileDir);
			$outputFilePath = "$CONVERT_PATH$targetName";
			//RecordCommand("GIF Convert $outputFileDir $targetName");
			$LastOperation .= "- Automatically Converted to GIF";
		}
		else if (!IsGoodImage($outputFileDir))
		{
			//upload failed cuz bad something
			$ErrorCode = 15;
			$Error = "Corrupt Or Unsupported File";
			//RecordCommand("XLOAD BAD IMAGE SEEN $inputFileDir Error=$ErrorCode");
		}
		else
		{
			chmod($outputFileDir,0777);
			$UploadSuccess = TRUE;
		}
	}
	else
	{
		//otherwise, nothing worked.  This is is an error.
		$ErrorCode = 3;
		$Error = "File Was Too Large or Bad Format";
		//RecordCommand("Error=$ErrorCode");
	}



if ($UploadSuccess == TRUE)
{
	GetImageAttributes($outputFileDir,$width,$height,$size);
	if ($size > 400000)
	{
		if (($width > 900) || ($height > 900))
		{
		$outputFileDir = ResizeImage($outputFileDir, 900, 900, FALSE);
		$targetName = basename($outputFileDir);
		$outputFilePath = "$CONVERT_PATH$targetName";
		RecordCommand("RESIZE $size $width $height");
		}
	}

	$imageList = array();
	RecordCommand("Animation: $outputFileDir");
    if (IsAnimatedGIF($outputFileDir) == TRUE)
    {
        $imageList = GetAnimatedImages($outputFileDir);
		$count = count($imageList);
		RecordCommand("Animation Seen: $count $outputFileDir");
        for ($i = 0; $i < $count; $i++)
        {
            $image = $imageList[$i];
            $image = GetConversionPath($image);
            //$image = "$BASE_PATH/$image";
            $imageList[$i] = $image;
            RecordCommand("Setting GIF Image = $image");
        }
	}
	else
	{
		$image = GetConversionPath($outputFileDir);
		//$image = "$BASE_PATH/$image";
		$imageList[] = $image;
	}
	$FileList = "";
	foreach ($imageList as $image)
	{
		$FileList .= $image.",";
	}
	$FileList = trim($FileList,",");


	RecordCommand("SUCCESS $FileList");
	$outputFilePath="$BASE_PATH$outputFilePath";
	echo '<html><head><title>-</title></head><body>';
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo "parent.completeFrameLoad(\"$FileList\",\"$stats\");";
	echo "\n".'</script></body></html>';
}
else
{
	RecordCommand("ERROR $outputFilePath");
	$ErrorReport = "Error: $Error";
	echo '<html><head><title>-</title></head><body>';
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo "parent.reportFrameLoadError(\"$ErrorReport\");";
	echo "\n".'</script></body></html>';
}


function IsTIFFImage($targetFileDir)
{

    $icommand = "identify $targetFileDir";
    $execResult = exec("$icommand 2>&1", $lines, $ConvertResultCode);
    if ((stristr($execResult, "TIF") != FALSE))
        return TRUE;
    return FALSE;
}
?>
