<?php
include "../common/common.inc";


$LastOperation = "Image Loaded";

$UploadSuccess = TRUE;
$ErrorCode = 0;

$xq = 0;


$remote = FALSE;
$UploadSuccess = FALSE;
$rand = MakeRandom();

$tmpName = $_FILES['FILENAME']['tmp_name']; 

$sourceFilePath = $_FILES['FILENAME']['name']; 
$sourceName = basename($sourceFilePath); 
$targetName = $sourceName;
RecordCommand("LOAD: tmp=$tmpName source=$sourceFilePath");

if (array_key_exists('URL',$_POST) == TRUE)
{
    $urlPath= $_POST['URL'];
    if ((strlen($urlPath) > 10) && (stristr($urlPath,".") != FALSE))
    {
        $remote = TRUE;
        $sourceFilePath = $urlPath;
    }
}


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
    RecordCommand("XLOAD: MOVE $tmpName $outputFileDir");

    // if a tiff, just convert to a jpg here give tifs 
    // cause us grief downstream
    if (IsTIFFImage($outputFileDir))
    {
        $outputFileDir = ConvertToJPG($outputFileDir);
        $targetName = basename($outputFileDir);
        $outputFilePath = GetConversionPath($outputFileDir);
        RecordCommand("TIFF Convert $outputFileDir $targetName");
        $LastOperation .= "- TIFF Automatically Converted to JPG";
    }

    if (IsAnimatedNonGIF($outputFileDir))
    {
        $outputFileDir = ConvertToGIF($outputFileDir);
        $targetName = basename($outputFileDir);
        $outputFilePath = GetConversionPath($outputFileDir);
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
    /*
    else if (IsPSDImage($outputFileDir))
    {
        $ErrorCode = 9;
        $Error = "Corrupt Or Unsupported File";
        //RecordCommand("XLOAD PSD SEEN Error=$ErrorCode");
    }
     */
    else
    {
        chmod($outputFileDir,0777);
        $UploadSuccess = TRUE;
    }
}


if ($UploadSuccess == TRUE)
{

    // CJM DEV XXX - Handle the fact BMPs aren't working
    // properly in this IM version
    if (IsValidBMP($outputFileDir))
    {
        RecordCommand("BMP HACK $outputFileDir");
        $outputFileDir = ConvertToPNG($outputFileDir);
        RecordCommand("BMP HACK CONVERTED -> $outputFileDir");
    }

	GetImageAttributes($outputFileDir,$width,$height,$size);
	RecordCommand("LOADX $outputFileDir");
	//if ($size > 400000)
	if ($size > $MAX_FIlE_SIZE)
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
	echo "parent.completeImageLoad(\"$outputFilePath\",\"$stats\");";
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
