<?php
include "../common/common.inc";

$LastOperation = "Image Loaded";

$UploadSuccess = TRUE;
$ErrorCode = 0;
$UploadSuccess = FALSE;

$tmpName = $_FILES['FILENAME']['tmp_name']; 

$sourceName = $_FILES['FILENAME']['name']; 
RecordCommand("LOAD: tmp=$tmpName source=$sourceName");

//
// check to see if file loaded
// if so, ensure it's a JPG and put into conversions directory
//
if (empty($sourceName))
{
    //upload failed because no data entered
    $ErrorCode = 1;
    $Error = "No File Specified";
}
else if (!is_uploaded_file($tmpName))
{
    //upload failed due to size constraints or non-existence
    $Error = "File Too Large Or Doe Not Exist";
    $ErrorCode = 2;
}
else if (filesize($tmpName) != 0)
{
    //
    // upload from a remote file system succeeded.  
    // convert to jpg and store in conversions dir
    // 
    $targetName = GenerateSessionImageName();
    $outputFileDir = GetConversionDir($targetName);
    $outputFilePath = GetConversionPath($targetName);
    $command = "convert $tmpName $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    /*
    if (file_exists($outputFileDir) == FALSE)
    {
        $targetName = StripSuffix($targetName);
        $outputFileDir = GetConversionDir("$targetName-0$JPGSUFFIX");
        RecordCommand("ConvertToJPG ANIM SEEN $outputFileDir");
    }
    */
    RecordCommand("XLOAD: UPLOAD JPG $tmpName $outputFileDir");

    chmod($outputFileDir,0777);
    $UploadSuccess = TRUE;
}

// Exec AI segment analysis of uploaded file
$command = escapeshellcmd("python ./mlsegment.py $outputFileDir");
shell_exec($command);

// inform javascript caller that the image is loaded and ready for display
if ($UploadSuccess == TRUE)
{

	GetImageAttributes($outputFileDir,$width,$height,$size);
	RecordCommand("LOADX $outputFileDir");
	if ($size > $MAX_FILE_SIZE)
	{
		if (($width > $RESIZE_MAX_WIDTH) || ($height > $RESIZE_MAX_HEIGHT))
		{
		$outputFileDir = ResizeImage($outputFileDir, $RESIZE_MAX_WIDTH, $RESIZE_MAX_HEIGHT, FALSE);
		$targetName = basename($outputFileDir);
		$outputFilePath = GetConversionPath($outputFileDir);
		RecordCommand("LOADX RESIZE $size $width $height");
		}
	}


	$stats = GetStatString($outputFileDir);

	RecordCommand("LOADX SUCCESS $outputFilePath");
	echo '<html><head><title>-</title></head><body>';
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo "parent.completeImageLoad(\"$outputFilePath\",\"$stats\",\"$segmentInfo\");";
	echo "\n".'</script></body></html>';
}
else
{
	//RecordCommand("WEBLOAD ERROR $outputFilePath");
	$ErrorReport = "Error: $Error";
	echo '<html><head><title>-</title></head><body>';
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo "parent.reportLoadError(\"$ErrorReport\");";
	echo "\n".'</script></body></html>';

}

function IsTIFFImage($targetFileDir)
{
    $icommand = "identify $targetFileDir";
    $execResult = exec("$icommand 2>&1", $lines, $ConvertResultCode);
    RecordCommand("DEV $execResult $lines[0]");
    if ((stristr($execResult, "TIF") != FALSE))
        return TRUE;
        return FALSE;
}




?>
