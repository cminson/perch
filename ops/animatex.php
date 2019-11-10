<?php
include '../common/common.inc';

RecordCommand("ANIM BEGIN");


$MAXANIMFILESIZE = 25000000;	//25M max upload size for all files


$LastOperation = "Frame Animation";

$MAX_ANIM = 80;

$ArgLoop = $_POST['LOOP'];
$ArgTime = $_POST['TIME'];
$ArgResize = $_POST['RESIZE'];

//RecordCommand("$ArgLoop $ArgTime $ArgResize");
if (isset($ArgLoop) == FALSE)
		$ArgLoop = 0;
if (isset($ArgTime) == FALSE)
		$ArgTime = 50;

$tmpName = array();
$sourceName = array();
$sourceFilePath = array();
$targetName = array();
$FileArray = array();

for ($i = 1; $i <= $MAX_ANIM; $i++)
{
	$file = $_POST["FRAMEPATH$i"];
	//RecordCommand("RAW: $i FILE = $file");
	if (strlen($file) <= 1)
		continue;
	if (stristr($file,"ezimbanoop") != FALSE)
		continue;
	$file = GetConversionDIR($file);
    if ((file_exists($file)) == FALSE)
    {
	    RecordCommand("$i Error FILE NOT FOUND: $file");
    }
	RecordCommand("$i FILE = $file");
    $FileArray[] = $file;
}

if (count($FileArray) < 2)
{
    RecordCommand("Exiting: Less than 2 files input");
    echo '<html><head><title>-</title></head><body>';
    echo '<script language="JavaScript" type="text/javascript">'."\n";
    echo "parent.completeWithNoAction();";
    echo "\n".'</script></body></html>';
    return;
}

// make sure max size is not exceeded
$size = 0;
foreach ($FileArray as $file)
{
	$size += filesize($file);
	//RecordCommand("FILESIZEDEV CHECK $size $file");
	if ($size > $MAXANIMFILESIZE)
	{
		ReportError("Size: $size. Max total file size of 25M exceeded. Try with fewer or smaller files");
	}
}

//automatically resize all images, if this option selection
if ($ArgResize == 'on')
{
	GetImageAttributes($FileArray[0],$real_width,$real_height,$size);
	$count = count($FileArray);
	RecordCommand("GETTING SIZE $count $FileArray[0] $real_width $real_height");
	for ($i = 1; $i < $count; $i++)
	{
		$file = $FileArray[$i];
		RecordCommand("RESIZED: $i $file");
		if (strlen($file) > 3)
		{
			$resizeFile = ResizeTMPImage($file,$real_width,$real_height,TRUE);
			$FileArray[$i] = $resizeFile;
			RecordCommand("RESIZED: $i $file $resizeFile $real_width $real_height");
		}
	}
}

$FileList="";
foreach ($FileArray as $file)
{
	if (strlen($file) > 3)
	{
		$FileList .= "$file ";
		RecordCommand("FILELIST $file");
	}
}

//do the conversion
$targetName = NewNameGIF();
$outputFileDir = GetConversionDir($targetName);
$outputFilePath = GetConversionPath($targetName);


$command = "convert -dispose previous -delay $ArgTime $FileList -loop $ArgLoop $outputFileDir";
$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
RecordCommand("ANIM $command");

GetImageAttributes($outputFileDir,$real_width,$real_height,$size);
if ($size > 2800000)
{
	if (($real_width > 800) || ($real_height > 800))
	{
		RecordCommand("RESIZING $size $outputFileDir");
		$outputFileDir = ResizeImage($outputFileDir,800,800,FALSE);
		RecordCommand("RESIZED $outputFileDir");
		$targetName = basename($outputFileDir);
        $outputFilePath = GetConversionPath($targetName);
	}
}

RecordCommand("FINAL $outputFilePath");
RecordAndComplete("ANIM",$outputFilePath,FALSE);

?>
