<?php
$RecordCommandOn=TRUE;


// global env for php and javascript
$HOME_DIR = "/var/www/perh/index.html";
$BASE_DIR = "/var/www/perch";
$BASE_PATH = "http://54.71.108.91";
$CONVERSION_PATH = "$BASE_PATH"."/CONVERSIONS/";
$CONVERSION_DIR = "$BASE_DIR"."/CONVERSIONS/";

$LOGFILE = "/var/www/perch/LOGS/command.log";
$DEBUGFILE_COMMAND = "/var/www/perch/LOGS/debug.log";

// precomputed paths to resources
$PATH_IMAGE_DEL = $BASE_PATH."/resources/utils/delicon1.png";
$PATH_IMAGE_NOOP = $BASE_PATH."/resources/utils/noop.png";
$PATH_IMAGE_ARROW_UP = $BASE_PATH."/resources/navs/arrow-up.jpg";
$PATH_IMAGE_EX01 = $BASE_PATH."/resources/defaults/tab.jpg";
$DIR_IMAGE_EX01 = $BASE_DIR."/resources/defaults/tab.jpg";
$DIR_IMAGE_LINEBREAK = "$BASE_PATH:/wimages/tools/graypixel.jpg";
$DIR_IMAGE_BUSY = "$BASE_PATH/resources/utils/smallbusyicon.gif";
$DIR_FONTS = "$BASE_DIR/resources/fonts/fontlist.txt";
$DIR_STYLES = "$BASE_DIR/resources/styles/";

$DIR_OPS = './ops/';

// this value is cross-definied in style1.css for img tags!!
$HEIGHT_IMAGE = 290;   //styled height of all displayed images

putenv("LD_LIBRARY_PATH=/usr/local/lib");

$PNGSUFFIX = ".png";
$GIFSUFFIX = ".gif";
$JPGSUFFIX = ".jpg";
$BMPSUFFIX = ".bmp";
$TIFFSUFFIX = ".tif";
$ICOSUFFIX = ".ico";

/////////////////////////////////////////////////////////////////////
//Image formats we support in general
$VALID_IMAGE_FORMATS = array("JPG", "GIF", "PNG", "BMP", "ICO", "TIF", "TIFF");
$VALID_CONV_FORMATS = array("JPG", "GIF", "PNG", "BMP");
$VIF_STRING = "JPG, GIF,  PNG, BMP, TIF, ICO";

//Image formats that you can usually view on an HTML page
$HTML_IMAGE_FORMATS = array("JPG", "GIF",  "ICO", "PNG", "BMP", "PDB", "TIF", "TIFF");

//////////////////////////////////////////////////////////////////////
//Global Error Messages
$MSG_ERRORBADFORMAT = "ERROR: This file is corrupted or has a bad format";

$MSG_ERRORNOFILE = "ERROR: No file entered.  Enter a correct file name in the form below and then click the Load button";

$MSG_ERRORMAXFILESIZE = "ERROR: Not a valid file.<BR>The file is either too large (2.0M limit) or does not exist.";

$MSG_ERRORUPLOAD = "ERROR: Could not locate this file.<BR>Please check the file name and try again.";

$MSG_ERRORMAXFILESIZE = "ERROR: Not a valid file.<BR>The file is either too large (3.0M limit) or does not exist.";

$MSG_ERRORCONVERT = "ERROR: File would not convert.<BR>Please check the file and try again.";

$MSG_ERRORWRONGTYPE = "ERROR: Not a valid file type.<BR>Your file  must be in graphics format, having a correct suffix at the end of the file name.";

$MSG_ERRORINVALIDIMAGE = "ERROR: Not a valid image type.<BR>Your file  must be in  one of these supported formats:  $VIF_STRING.";

$MSG_ERRORNOPSD = "ERROR: This is a PSD formatted file.<BR>Your file  must be in  one of these supported formats:  $VIF_STRING.";

$MSG_SUCCESSCONVERT = "";

$MSG_SUCCESSTEXTCONVERT = "Your converted file is below.  <BR>Windows Users: Right-click the link to save it to your hard disk. <BR>Mac Users: Control-click the link to save it to your hard disk.";


$MAX_FILE_SIZE = 10000000;   // 10M. Max image file size. Also set in php.ini (upload_max_filesize)
$MAX_FILE_SIZE_PRINTABLE = "3.0Mb";  //For output in help messages
$MAX_ANIM_FRAME_SIZE = 3000000;  // max size for animation frame file 
$RESIZE_MAX_WIDTH = 800;  // Resize target width when resizing overly large files
$RESIZE_MAX_HEIGHT= 800;  // Resize target height when resizing overly large files

$RANDOM_NAME_MIN = 1000000000;  // min bound number for random image names
$RANDOM_NAME_MAX = 9999999999;  // max bound number for random image names


$current = getGETVar('CURRENTFILE');


/////////////////////////////////////////////////////
//Common Functions
////////////////////////////////////////////////////


function MakeRandom()
{
global $RANDOM_NAME_MIN;
global $RANDOM_NAME_MAX;

	$rand = rand($RANDOM_NAME_MIN, $RANDOM_NAME_MAX);
	return $rand;
}


function IsGoodImage($targetFileDir)
{

    $command = "identify $targetFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    if ((stristr($lines[0], "Improper") != FALSE))
        return FALSE;
    if ((stristr($lines[0], "Corrupt") != FALSE))
        return FALSE;
    if ((stristr($lines[0], "Bad") != FALSE))
        return FALSE;
    if ((stristr($lines[0], "Unable") != FALSE))
        return FALSE;
    if ((stristr($lines[0], "found") != FALSE))
        return FALSE;
    if ((stristr($lines[0], "starts") != FALSE))
        return FALSE;
	return TRUE;
}

function IsValidImageFormat($name)
{
	global $VALID_IMAGE_FORMATS;

	$len = strlen($name);
	if ($len <= 4)
		return FALSE;
	$suffix = strrchr($name, ".");
	if ($suffix == FALSE)
		return FALSE;
	$arr = array("1", "2", "3");
	foreach ($VALID_IMAGE_FORMATS as $format)
	{
		if (strcasecmp($suffix, ".$format") == 0)
			return TRUE;
	}	
	return FALSE;
}


function CorrectFormat($suffix)
{
	global $VALID_CONV_FORMATS;
	global $VALID_IMAGE_FORMATS;
	global $JPGSUFFIX;

	if (strlen($suffix) < 1)
	{
		//return $JPGSUFFIX;
		return $suffix;
	}
	if (strlen($suffix) > 10)
		return $JPGSUFFIX;
	foreach ($VALID_IMAGE_FORMATS as $format)
	{
		if (strcasecmp($suffix, ".$format") == 0)
			return $suffix;
	}	
	return $JPGSUFFIX;
}



function IsValidBMP($name)
{
    $len = strlen($name);
    if ($len <= 4)
        return FALSE;
    $suffix = strrchr($name, ".");
    if ($suffix == FALSE)
		return FALSE;
    if (strcasecmp($suffix, ".bmp") == 0)
        return TRUE;
    return FALSE;
}


function IsValidGIF($name)
{
    $len = strlen($name);
    if ($len <= 4)
        return FALSE;
    $suffix = strrchr($name, ".");
    if ($suffix == FALSE)
		return FALSE;
    if (strcasecmp($suffix, ".gif") == 0)
        return TRUE;
    return FALSE;
}


function IsValidJPG($name)
{
    $len = strlen($name);
    if ($len <= 4)
        return FALSE;
    $suffix = strrchr($name, ".");
    if ($suffix == FALSE)
		return FALSE;
    if (strcasecmp($suffix, ".jpg") == 0)
        return TRUE;
    return FALSE;
}



function IsValidTIF($name)
{
    $len = strlen($name);
    if ($len <= 4)
        return FALSE;
    $suffix = strrchr($name, ".");
    if ($suffix == FALSE)
		return FALSE;
    if (strcasecmp($suffix, ".tif") == 0)
        return TRUE;
    if (strcasecmp($suffix, ".tiff") == 0)
        return TRUE;
    if (strcasecmp($suffix, ".ico") == 0)
        return TRUE;
    return FALSE;
}



function IsValidPNG($name)
{
    $len = strlen($name);
    if ($len <= 4)
        return FALSE;
    $suffix = strrchr($name, ".");
    if ($suffix == FALSE)
		return FALSE;
    if (strcasecmp($suffix, ".png") == 0)
        return TRUE;
    return FALSE;
}



function IsHTMLImageFormat($name)
{
	global $HTML_IMAGE_FORMATS;
	global $BASE_DIR;


	$len = strlen($name);
	if ($len <= 4)
		return FALSE;
	$suffix = strrchr($name, ".");
	if ($suffix == FALSE)
		return FALSE;
	foreach($HTML_IMAGE_FORMATS as $format)
	{
		if (strcasecmp($suffix, ".$format") == 0)
			return TRUE;
	}	
	return FALSE;
}


function GetSuffix($name)
{
	$suffix = strrchr($name, ".");
	return $suffix;
}


function StripSuffix($name)
{
	$len = strlen($name);
	$suffix = strrchr($name, ".");
	if ($suffix == FALSE)
		return $name;
	$newName = str_replace("$suffix", "", $name); 
	return $newName;
}


function NewName($inputFileDir)
{

	$rand = MakeRandom();

	$name = basename($inputFileDir);
    $suffix = strrchr($name, ".");

    $suffix = CorrectFormat($suffix);
    $suffix = strtolower($suffix);
    $newName = "$rand$suffix";

	return $newName;
}

function NewTMPName($inputFileDir)
{

    $rand = MakeRandom();

    $name = basename($inputFileDir);
    $suffix = strrchr($name, ".");

    $suffix = CorrectFormat($suffix);
    $suffix = strtolower($suffix);
    $newName = "$rand$suffix";

    return $newName;
}


function NewNameGIF()
{
global $GIFSUFFIX;

	$rand = MakeRandom();
    $newName = "$rand$GIFSUFFIX";
	return $newName;
}


function NewNamePNG()
{
global $PNGSUFFIX;

	$rand = MakeRandom();
    $newName = "$rand$PNGSUFFIX";
	return $newName;
}


function NewNameJPG()
{
global $JPGSUFFIX;

	$rand = MakeRandom();
    $newName = "$rand$JPGSUFFIX";
	return $newName;
}

function NewNameBMP()
{
global $BMPSUFFIX;

	$rand = MakeRandom();
    $newName = "$rand$BMPSUFFIX";
	return $newName;
}


function MakeRandomTextName($path, $name, $rand)
{

	$suffix = strrchr($name, ".");
	$suffix = CorrectFormat($suffix);
	$suffix = strtolower($suffix);
	$newName = "$path$rand$suffix";

	return $newName;
}

function MakeTMPRandomTextName($path, $name, $rand)
{


	$suffix = strrchr($name, ".");
	$suffix = CorrectFormat($suffix);
	$suffix = strtolower($suffix);
	$newName = "$path$rand$suffix";

	return $newName;
}


function IsHandHeld()
{
	$ua = $_SERVER['HTTP_USER_AGENT'];
	//RecordCommand("IsHandHeld: $ua");
    if ((stristr($ua, "phone") != FALSE))
	{
		//RecordCommand("IsHandHeld: TRUE");
		return TRUE;
	}
    if ((stristr($ua, "Pad") != FALSE))
	{
		return TRUE;
	}
    if ((stristr($ua, "android") != FALSE))
	{
		return TRUE;
	}
	return FALSE;
}


function RecordCommand($command)
{
	global $LOGFILE;
	global $RecordCommandOn;

    $page = $_SERVER['PHP_SELF'];
	$page = str_ireplace(".php", "", $page); 
	$page = strtoupper(basename($page));

	if ($RecordCommandOn == TRUE)
	{

		$date = date('m . d . y ');
		$date = date("H:i:s");
        $ip = $_SERVER['REMOTE_ADDR'];
        $fp = fopen($LOGFILE, 'a+');
		fputs($fp, "$ip $date $command");
		fputs($fp, "\n");
		fclose($fp);
	}
}



function PrintSaveImageString()
{
global $X_CLICKTOVIEWMAC,$X_CLICKTOVIEWPC;

    $browser="";
    if (array_key_exists('HTTP_USER_AGENT',$_SERVER) == TRUE)
	    $browser = $_SERVER['HTTP_USER_AGENT'];

	//print "<a href=\"#\" onclick=\"deleteCurrentImage()\">X</a>\n";
	//print "&nbsp;&nbsp;&nbsp;\n";
	if (stristr($browser, "Mac"))
		print $X_CLICKTOVIEWMAC;
	else
		print $X_CLICKTOVIEWPC;
}


function GetStatString($outputFileDir)
{
    global $X_SIZE, $X_DIMENSIONS, $X_COLORCOUNT, $X_HISTORY, $LastOperation;

	GetImageAttributes($outputFileDir,$width,$height,$size);
    $colors = GetColorCount($outputFileDir);

    $X_COLORS = "Colors";
    $psize = round(($size / 1000),0);
    $psize = $psize."kb";
    $pdim = "$width"."x"."$height";
    $stats = "$X_SIZE: $psize $pdim $X_COLORS: $colors&nbsp;&nbsp;[$LastOperation]";

	$stats = "$stats";
	return ($stats);
}


function ReportError($error)
{
	$error = "Error: $error";
	RecordCommand("ReportError: $error");
	echo '<html><head><title>-</title></head><body>';
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo "parent.reportOpError(\"$error\");";
	echo "\n".'</script></body></html>';
	exit(0);
}

function RecordAndComplete($op,$outputFilePath,$simple)
{
global $LastOperation;

    $outputFileDir = GetConversionDir($outputFilePath);
	RecordCommand("RecordAndComplete FILE DIR: $outputFilePath $outputFileDir");

    if (file_exists($outputFileDir) == TRUE)
	{
		$stats = GetStatString($outputFileDir);
	}
	else
	{
		// no valid output image, so output an error banner instead
	    RecordCommand("ERROR - File Not Found: $outputFileDir");
        //CJM DEV
		$inputFileDir = "/var/www//ic/wimages/messages/error1.png";
		$outputFileDir = ConvertToJPG($inputFileDir);
		$outputFilePath = GetConversionPath($outputFileDir);
		$stats = "Error";
		RecordCommand("RecordAndComplete: Error Image = $outputFilePath");
	}

	RecordCommand($outputFilePath);
	echo '<html><head><title>-</title></head><body>';
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo "parent.completeImageOp(\"$outputFilePath\",\"$stats\");";
	echo "\n".'</script></body></html>';

    /*
	if ($simple == TRUE)
	{
		$outputFilePath = $outputFilePath."?".$stats;
		RecordCommand($outputFilePath);
		echo $outputFilePath;
	}
	else
	{
		RecordCommand($outputFilePath);
		echo '<html><head><title>-</title></head><body>';
		echo '<script language="JavaScript" type="text/javascript">'."\n";
		echo "parent.completeImageOp(\"$outputFilePath\",\"$stats\");";
		echo "\n".'</script></body></html>';
	}
     */
}


function DisplayLoadReturn()
{
global $X_RETURNTOMAINPAGE;

	$return = "&larr;&nbsp;$X_RETURNTOMAINPAGE";
	print "<center>\n";
	print "<A class=\"mainpagereturnlink\" HREF=\"#home\" onclick=\"toggleLoadDisplay()\">$return</A>\n";
	print "</center>\n";
}


function DisplayMainReturn()
{ 
global $X_RETURNTOMAINPAGE;
global $BASE_PATH;

	$return = $X_RETURNTOMAINPAGE;
	print "<center>\n";
	print "<A class=navlink HREF=\"$BASE_PATH\">$return</A>\n";
	print "</center>\n";
}


function DisplayCurrentEventJSImage($current)
{
	if (IsHTMLImageFormat($current) == TRUE)
	{
		print "<center>";
        print "<a href=\"#\" onclick=\"handle(event)\">\n";
		print "<img class=\"opimage\" id=\"image\" src=\"$current\" alt=\"current image\">\n";
/*
		print "<a href=\"$current\">\n";
		print "<img class=\"opimage\" id=\"image\" src=\"$current\" alt=\"current image\">\n";
*/
        print "</a>\n";
		print "</center>\n";
	}
}

function DisplayOp($category, $text, $file, $target)
{
	global $BASE_PATH;
	global $fileLoaded;
	global $LastOperation;
	global $DIR_OPS;

	print "<tr>\n";
    print "<td style=\"height: 12px; padding: 4px; text-align: left; white-space: nowrap; font-size: 10px;\">\n";
	print "$category";
	print "</td>\n";
    print "<td style=\"height: 12px; padding: 4px; text-align: center; white-space: nowrap; font-size: 10px;\">\n";

    print "<img height=1 src=\"$BASE_PATH/wimages/tools/pixel.gif\" alt=\"\">\n";
	print "</td>\n";
    print "<td style=\"height: 12px; padding: 4px; text-align: left; white-space: nowrap; font-size: 10px;\">\n";

	if (strlen($target) < 1)
	{
		$op = "$DIR_OPS$file";
		print "<A class=navlink href=\"#home\" onclick=\"displayOp('$op')\" >$text</A>\n";
	}
	else
	{
		$op = "$DIR_OPS$file";
		print "<A class=navlink href=\"#home\" onclick=\"execSimpleOp('$op','$target')\">$text</A>\n";

	}

	print "</td></tr>\n\n\n";
	$LastOperation = $text;
}


function DisplayOpButton($label, $opHandler)
{
	global $BASE_PATH;
	global $DIR_OPS;

	print "<tr>\n";

    /*
    print "<td style=\"height: 12px; padding: 4px; text-align: center; white-space: nowrap; font-size: 10px;\">\n";
    print "<img height=1 src=\"$BASE_PATH/wimages/tools/pixel.gif\" alt=\"\">\n";
	print "</td>\n";
     */

    print "<td style=\"height: 12px; padding: 4px; text-align: left; white-space: nowrap; font-size: 10px;\">\n";

	$opHandler = "$DIR_OPS$opHandler";
    print "<button class=\"opHandler\" onclick=\"displayOp('$opHandler')\">$label</button>\n";

	print "</td></tr>\n\n\n";
}







function GetFileSuffix($name)
{
$default = "gif";
$len = strlen($name);

    if ($len <= 4)
        return $default;
    $s = substr($name,$len-4,4);
    if ((stristr($s,"gif")) != FALSE)
        return "gif";
    if ((stristr($s,"jpg")) != FALSE)
        return "jpg";
    if ((stristr($s,"png")) != FALSE)
        return "png";
    if ((stristr($s,"bmp")) != FALSE)
        return "bmp";
    return $default;
}


function DetermineImageSize($path,$w,$h)
{
    $w = $h = 0;
    $icommand = "identify $inputFileDir";
    $execResult = exec("$icommand 2>&1", $lines, $ConvertResultCode);
    $tok = strtok($execResult," ");
    for ($i = 0; $i < 6; $i++)
    {
        $tok = strtok(" ");
        if ($tok == FALSE) break;
        if ($i == 1)
        {
            list($w, $h) = sscanf($tok, "%dx%d");
        }
        break;
    }
}


function IsAnimatedGIF($inputFileDir)
{
/*
    if ((IsValidGIF($inputFileDir)) == FALSE)
        return FALSE;
*/
	//RecordCommand("IsAnimatedGIF $inputFileDir");
    $imageCount = 0;
    $icommand = "identify $inputFileDir";
    $execResult = exec("$icommand 2>&1", $lines, $ConvertResultCode);
	foreach ($lines as $line)
    {
		//RecordCommand("IsAnimatedGIF $line");
        $imageCount++;
    }
    if ($imageCount < 2)
	{
		//RecordCommand("IsAnimatedGIF == FALSE");
        return FALSE;
	}
	//RecordCommand("IsAnimatedGIF == TRUE");
    return TRUE;
}


function IsAnimatedNonGIF($inputFileDir)
{
    if ((IsValidGIF($inputFileDir)) != FALSE)
        return FALSE;
    $imageCount = 0;
    $icommand = "identify $inputFileDir";
    $execResult = exec("$icommand 2>&1", $lines, $ConvertResultCode);
	foreach ($lines as $line)
    {
        $imageCount++;
    }
    if ($imageCount < 2)
        return FALSE;
    return TRUE;
}


//
// return the first image in an animated GIF image
//
// this function assumes the image IS an animation
// thus you should always call this function only AFTER calling IsAnimatedGIF
//
function getBaseImage($inputFileDir)
{
global $PNGSUFFIX;

    $targetName = basename($inputFileDir);
    $targetName = StripSuffix($targetName);
    //RecordCommand("target Image: $targetName");
    $targetName = TMPPNG();
    $outputFileDir = GetConversionDir($targetName);
    $command = "convert $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);

    $baseFile = "$targetName-0$PNGSUFFIX";

    $baseFileDir = GetConversionDir($baseFile);
    //RecordCommand("Base Image: $baseFileDir ");
    if (file_exists($baseFileDir) == TRUE)
        return $baseFile;
    return FALSE;
}



function getGETVar($var)
{
    $value = '';

    if ((isset($_GET[$var])) == TRUE)
    {
        $value = $_GET[$var];
    }
    return $value;
}


function getPOSTVar($var)
{
    $value = '';

    if ((isset($_POST[$var])) == TRUE)
    {
        $value = $_POST[$var];
    }
    return $value;
}


function GetImageAttributes($imageDir, &$width, &$height, &$size)
{
    $icommand = "identify $imageDir";
    $execResult = exec("$icommand 2>&1", $lines, $ConvertResultCode);
    $tok = strtok($execResult," ");
    for ($i = 0; $i < 6; $i++)
    {
        $tok = strtok(" ");        
        if ($tok == FALSE) break;
        if ($i == 1)
        {
            list($width, $height) = sscanf($tok, "%dx%d");
            break;
        }
    }
    if (file_exists($imageDir) == TRUE)
		$size = filesize($imageDir);
}


function GetColorCount($imageDir)
{

    if (file_exists($imageDir) == FALSE)
	{
		return "NA";
	}
	//RecordCommand("GetColorCount $imageDir");
    $command = "identify -format \"%k \" $imageDir";
    $count = exec("$command 2>&1", $lines, $ConvertResultCode);
	$spc = substr_count($count,' ');
	if ($spc >= 1)
	{
		$a = explode(' ',$count);
		$count = $a[0];
	}
	return $count;
}



function ResizeImage($imageDir, $width, $height, $exactFit)
{

    //RecordCommand("XRESIZE");
    $targetName = NewName($imageDir);
    $outputFileDir = GetConversionDir($targetName);
    if ($exactFit == TRUE)
        $command = "convert -resize $width"."x"."$height"."\!"." $imageDir $outputFileDir";
    else
        $command = "convert -resize $width"."x"."$height $imageDir $outputFileDir";

    //RecordCommand("XRESIZE: $command");
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    return $outputFileDir;
}


function ResizeTMPImage($imageDir, $width, $height, $exactFit)
{

    $targetName = basename($imageDir);
    $targetName = TMPName($targetName);
    $outputFileDir = GetConversionDir($targetName);
    if ($exactFit == TRUE)
        $command = "convert -resize $width"."x"."$height"."\!"." $imageDir $outputFileDir";
    else
        $command = "convert -resize $width"."x"."$height $imageDir $outputFileDir";

    RecordCommand("XRESIZE: $command");
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    return $outputFileDir;
}



function ConvertToJPG($inputFileDir)
{
global $JPGSUFFIX;

	$targetName = NewNameJPG();
    $outputFileDir = GetConversionDir($targetName);
    $command = "convert $inputFileDir $outputFileDir";

	RecordCommand("ConvertToJPG $command");
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
	if (file_exists($outputFileDir) == FALSE)
	{
		$targetName = StripSuffix($targetName);
		$outputFileDir = GetConversionDir("$targetName-0$JPGSUFFIX");
		RecordCommand("ConvertToJPG ANIM SEEN $outputFileDir");
	}
    return $outputFileDir;
}


function ConvertToGIF($imageDir)
{

	$targetName = NewNameGIF();
    $outputFileDir = GetConversionDir($targetName);
    $command = "convert $imageDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    return $outputFileDir;
}



function ConvertToPNG($imageDir)
{
global $PNGSUFFIX;

	$targetName = NewNamePNG();
    $outputFileDir = GetConversionDir($targetName);
    $command = "convert $imageDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    return $outputFileDir;
}



//
// Convert a tif to a jpg
// CJM DEV - note special case link to /usr/bin
// this is due to lack of tiff delegate in runtime convert
//
function ConvertTIF($imageDir)
{
global $JPGSUFFIX;

    $targetName = NewNameJPG();
    $outputFileDir = GetConversionDir($targetName);
    $command = "/usr/bin/convert $imageDir $outputFileDir"; // CJM NOTE
	RecordCommand("ConvertTIF $command");
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
	if (file_exists($outputFileDir) == FALSE)
	{
		$baseName = basename($outputFileDir);
        $outputFileDir = GetConversionDir("$baseName-0$JPGSUFFIX");
		//RecordCommand("ConvertToJPG ANIM SEEN $outputFileDir");
	}
    return $outputFileDir;
}



function GetAnimatedImages($animatedFile)
{
global $PNGSUFFIX;
global $GIFGSUFFIX;

    //RecordCommand("GetAnimatedImages: $animatedFile");
    $targetName = TMPPNG();
	$outputFileDir = GetConversionDir($targetName);
    $command = "convert $animatedFile $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    RecordCommand("GetAnimatedImages: $command");

    $imageList = array();
    for ($i=0;;$i++)
    {
		$targetName = StripSuffix($targetName);
	    $imageFileDir = GetConversionDir("$targetName-$i$PNGSUFFIX");
        //RecordCommand("IMAGE: $imageFileDir");
        if (file_exists($imageFileDir) == FALSE)
            break;
        $imageList[] = $imageFileDir;
    }
    return $imageList;
}


function TMPName($name)
{

$rand = rand(1000000000,9999999999);
$suffix = GetSuffix($name);
$suffix = CorrectFormat($suffix);
$newname = "CJM-$rand$suffix";
return $newname;
}

function TMPGIF()
{
global $GIFSUFFIX;

$rand = rand(1000000000,9999999999);
$newname = "CJM-$rand$GIFSUFFIX";
return $newname;
}

function TMPJPG()
{
global $JPGSUFFIX;

$rand = rand(1000000000,9999999999);
$newname = "CJM-$rand$JPGSUFFIX";
return $newname;
}

function TMPPNG()
{
    global $PNGSUFFIX;

$rand = rand(1000000000,9999999999);
$newname = "CJM-$rand$PNGSUFFIX";
return $newname;
}


function DisplayCurrentClickableImageReturnMain($current,$returnToMainPage)
{
	global $WIDTH_IMAGE;

	print "<center>\n";
	print "<A class=navlink HREF=\"$BASE_PATH/index.html?CURRENTFILE=$current\">$returnToMainPage</A>\n";

	print "<br><p>";
	print "<table>\n";

	// If image is a format that can be displayed in HTML,
	// then display it.  Otherwise, display a link to the this image.
	$fileName = basename($current);
	if (IsHTMLImageFormat($current) == TRUE)
	{
		print "<TR> <TD align=\"center\" width=\"300\">\n";
		print "<A href=\"#\"\n";
		print "onclick=\"handle(event)\">\n";
		print "<IMG src=\"$current\" id=\"image\" alt=\"Click Here to View Image\"  width=\"$WIDTH_IMAGE\">\n";
		print "</A>\n";
		print "</TD> </TR>";
	}
	else
	{
		print "<TR> <TD class=\"verysmall\" align=\"center\" width=\"300\">\n";
		print "<A class=navlink href=\"$current\">\n";
		print "Click Here to View Image\n";
		print "</A>\n";
		print "</TD> </TR>";
	}

	print "<TR> <TD align=\"center\" class=\"image\">\n";
	print "$fileName\n";
	print "</TD></TR>\n";
	print "</TBODY></table>\n";
	print "</center>";
}

// was 500k and 450
function CheckFileSize($inputFileDir)
{

	$targetName = basename($inputFileDir);
	$outputFilePath = GetConversionPath($inputFileDir);

    GetImageAttributes($inputFileDir,$width,$height,$size);
    RecordCommand("CHECKFILESIZE PRE REDUCE $size ");
    if ($size > 800000)
    {
        $targetName = NewName($inputFileDir);
		if (($width > 650) || ($height > 650))
		{
			$outputFileDir = GetConversionDir($targetName);
			$outputFilePath = GetConversonPath($targetName);

			$command = "convert -resize 650x650 $inputFileDir $outputFileDir";
			$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
			RecordCommand("CHECKFILE POST REDUCE  $command");
		}
    }
	return $outputFilePath;
}



// ***********************************************
function DisplayInitCropJS()
{
    print "<script src=\"$BASE_PATH/tools/cropper/lib/prototype.js\" type=\"text/javascript\"></script>\n";
    print "<script src=\"$BASE_PATH/tools/cropper/lib/scriptaculous.js?load=builder,dragdrop\" type=\"text/javascript\"></script>\n";
    print "<script src=\"$BASE_PATH/tools/cropper/cropper.js\" type=\"text/javascript\"></script>\n";
}


function DisplaySelectionTable($id,$dir,$path,$maxcols,$width,$selwidth,$displaynames,$default)
{
global $X_SELECTED;


	$handle = opendir($dir);
	$files = array();
	while ($file = readdir($handle))
	{
		if (($file == '.') || ($file == '..'))
			continue;
     $files[] = $file;
	}
	closedir($handle);
	sort($files);

	print  "<center>\n";
	print  "<table class=\"selections\" cellspacing=5 cellpadding=5>";
	$i = $j = 0;
	$num = 1;
	$pdefault = 1;
	foreach ($files as $file)
	{
		$j++;
		$pfile = str_ireplace(".jpg","",$file);
		$pfile = str_ireplace(".png","",$pfile);
		$pfile = str_ireplace(".gif","",$pfile);
		if ($displaynames == true)
		{
			$psel = "$j $pfile";
			$psel = str_replace(".ttf","",$psel);
		}
		else
			$psel = $j;

		if ($default == $file)
			$pdefault = $j;

		$psel2 = "test";
		if ($i == 0)
			print  "<tr>\n";
		//print  "<td class=selection onclick=\"alert('$file'+'$psel')\">\n";
		print  "<td class=selection onclick=\"selectTableItem('$id','$path','$file','$psel')\">\n";
		print  "<img border=0 align=center src=$path$file alt=$file width=\"$width\">\n";
		print  "<br><center>$psel</center>\n";
		print  "</td>\n";
		$i++;
		if ($i == $maxcols)
		{
			print  "</tr>\n";
			$i = 0;
		}
		$num++;
	}
	print  "</table>\n";
	print  "<p><br>\n";
	
	print  "<input type=\"hidden\" name=\"$id\" id=\"$id\" value=\"$default\">\n";
	print  "<img id=\"IMAGE\" border=\"0\" align=\"center\" src=\"$path$default\" alt=\"$file\" width=\"$selwidth\">\n";
	print  "<div id=\"STATUS\" >\n$pdefault</div>\n";
	print  "$X_SELECTED\n";
	print  "</center>\n";

} 

function DisplayConvertButton()
{
global $DIR_OPS;

	$op = $_SERVER['PHP_SELF'];
	$op = basename($op);
	$op = str_replace(".php","x.php",$op);
    $op = "$DIR_OPS$op";
	RecordCommand("DISPLAYCONVERTBUTTON $op");

	print "<P><BR>";

	print "<center>";
    print "<button class=\"convert\" type=\"input\" onclick=\"submitOpForm()\")>Convert</button>\n";
	print "</center>";
}

$COLORS = array('White', 'Black', 'Red', 'Green', 'Blue');
$COLORS_PLUS_TRANSPARENT = array('Transparent', 'White', 'Black', 'Red', 'Green', 'Blue');

function DisplayColorSelector($text, $setting, $name, $transparent)
{
    global $COLORS, $COLORS_PLUS_TRANSPARENT;

    if ($transparent == True) $colors = $COLORS_PLUS_TRANSPARENT;
    else $colors = $COLORS;
	print  "$text\n";
	print "<select NAME=\"$name\">\n";
    foreach ($colors as $color) 
    {
        //print("$color<br>");
        if ($color == $setting) 
        {
	        print "<option VALUE=$color selected>$color\n";
        }
        else
        {
	        print "<option VALUE=$color>$color\n";
        }

    }
	print "</select>\n";
}

function DisplayColorPicker($text,$setting,$name,$default)
{
	print "<a class=\"navlink\" href=\"javascript: onclick=pickerPopup302('$setting','$name');\">$text</a>\n";
	DisplaySep0();
	print "<input type=\"text\" id=\"$setting\" name=\"$setting\" size=\"9\" value=\"$default\">&nbsp;\n"; 
	print  "<input type=\"text\"  id=\"$name\" size=\"1\" value=\"\" style=\"background-color:$default\">\n";
}

function DisplayLoopPicker($text,$setting)
{
	print  "$text\n";
	DisplaySep0();
	print  "<select name=\"$setting\">\n";
	print  "<option value=0 SELECTED>---\n";
	print  "<option value=0 >Infinite\n";
	print  "<option value=100>100 Loops\n";
	print  "<option value=10>10 Loops\n";
	print  "<option value=5>5 Loops\n";
	print  "<option value=1>1 Loops\n";
	print  "</select>\n";
}

function DisplaySpeedPicker($text,$setting)
{
	print  "$text\n";
	DisplaySep0();
	print  "<select name=\"$setting\">\n";
	print  "<option value=50 SELECTED>---\n";
	print  "<option value=300>3 Seconds\n";
	print  "<option value=200>2 Seconds\n";
	print  "<option value=100>1 Second\n";
	print  "<option value=50>1/2 Second\n";
	print  "<option value=25>1/4 Second\n";
	print  "<option value=20>1/5 Second\n";
	print  "<option value=10>1/10 Second\n";
	//print  "<option value=1>1/100 Second\n";
	print  "</select>\n";
}

function DisplayNumPicker($text,$setting,$min,$max,$selected)
{
	print  "$text\n";
	DisplaySep0();
	print  "<select name=\"$setting\">\n";
	$i = $min;
	while ($i <= $max)
	{
		if ($i == $selected)
			print  "<option value=\"$i\" selected>$i\n";
		else
			print  "<option value=\"$i\">$i\n";

		$i++;
	}
	print  "</select>\n";
}

function DisplayFileNumPicker($text,$setting,$files,$selected)
{
	print  "$text\n";
	DisplaySep0();
	print  "<select name=\"$setting\">\n";

	$i = 1;
	foreach ($files as $file)
	{
		if ($i == $selected)
			print  "<option value=\"$file\" selected>$i\n";
		else
			print  "<option value=\"$file\">$i\n";

		$i++;
	}
	print  "</select>\n";
}

function DisplayFilePicker($text,$setting)
{
    print  "$text\n";
    DisplaySep0();
	print  "<input size=\"50\" maxLength=\"50\" type=\"FILE\" name=\"FILENAME\">\n";
}

function DisplayCheckBox($text,$setting,$checked)
{
	print "$text\n";
	DisplaySep0();
	if ($checked)
		print "<input type=\"checkbox\" name=\"$setting\" checked>\n";
	else
		print  "<input type=\"checkbox\" name=\"$setting\">\n";
}

function DisplaySep0()
{
	print "&nbsp;";
}

function DisplaySep4()
{
	print "&nbsp;";
	print "&nbsp;";
	print "&nbsp;";
	print "&nbsp;";
}

function DisplaySep1()
{
	print  "&nbsp;&nbsp;&nbsp;&nbsp;\n";
}

function DisplayLineSep0()
{
	print  "<br><p>\n";
}

function DisplayLineSep1()
{
	print  "<br><p><br>\n";
}

function DisplayFormStart()
{
    global $DIR_OPS;

	$op = $_SERVER['PHP_SELF'];
	$op = basename($op);
	$op = str_replace(".php","x.php",$op);
	$op = "$DIR_OPS$op";

	print "\n";
	print "<center>\n";
    print  "<form id=\"ID_OP_SUBMITFORM\" enctype=\"multipart/form-data\" action=\"$op\" method=\"post\" target=\"upload_target\">\n";

	$current = $_POST['CURRENTFILE'];
	print  "<input type=\"hidden\" id=\"current\" name=\"CURRENTFILE\" value=\"$current\">\n";
	print  "<input type=\"hidden\" id=\"ARG1\" name = \"ARG1\" value=\"NA\">\n";
	print  "<input type=\"hidden\" id=\"ARG2\" value=\"NA\">\n";

	RecordCommand("DISPLAY FORMSTART CURRENT=$current");
	print "\n";
}

function DisplayFormEnd()
{
	print sprintf("</form>");
	print sprintf("</center>");
}

function DisplayTitle($title)
{
    print "<p>\n";
    print "<hr>\n";
    print "<center><h2 onclick=returnToMainArea()>$title</h2></center>";
}

function DisplayLoadTitle($title)
{
print "<center><h2 onclick=\"toggleLoadDisplay()\">$title</h2></center>";
}

function DisplayTextInput($text, $name, $value, $size)
{
	print $text;
	DisplaySep0();
	print "<input size=\"$size\" MaxLength=\"$size\" Length=\"$size\" value=\"$value\" type=\"text\" name=\"$name\">\n";
}

function DisplayFontPicker($text, $default, $name)
{
global $DIR_FONTS;

    $fonts = array();
    $fh = fopen("$DIR_FONTS","r");
    while(!feof($fh))  
    {
	    $font = fgets($fh);
        $fonts[] = $font;
    }
    fclose($fh);
	sort($fonts);

	print($text);
	DisplaySep0();

	print "<select name=\"$name\">\n";
	foreach ($fonts as $font)
	{
        $font = trim($font);
        if ($font == $default)
        {
		    print "<option value=$font selected>$font\n";
        }
        else
        {
		    print "<option value=$font>$font\n";
        }
	}
	print "</select>\n";
}

function DisplayFontSizePicker($text, $name)
{
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	print "<option>10\n";
	print "<option>12\n";
	print "<option>15\n";
	print "<option>18\n";
	print "<option>20\n";
	print "<option>22\n";
	print "<option>25\n";
	print "<option>30\n";
	print "<option>35\n";
	print "<option selected>40\n";
	print "<option>50\n";
	print "<option>60\n";
	print "<option>70\n";
	print "<option>80\n";
	print "<option>90\n";
	print "<option>100\n";
	print "<option>110\n";
	print "<option>120\n";
	print "<option>130\n";
	print "<option>140\n";
	print "<option>150\n";
	print "</select>\n";
}

function DisplayFontSizePicker2($text, $name)
{
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	print "<option>30\n";
	print "<option>35\n";
	print "<option>40\n";
	print "<option>50\n";
	print "<option selected>60\n";
	print "<option>70\n";
	print "<option>80\n";
	print "<option>90\n";
	print "<option>100\n";
	print "<option>110\n";
	print "<option>120\n";
	print "<option>130\n";
	print "<option>140\n";
	print "<option>150\n";
	print "</select>\n";
}

function DisplayPositionPicker($text, $name, $append)
{
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	print "<option value=\"North\">Top Center\n";
	print "<option value=\"Northwest\">Top Left\n";
	print "<option value=\"Northeast\">Top Right\n";
	print "<option value=\"Center\">Center \n";
	print "<option value=\"West\">Center Left\n";
	print "<option value=\"East\">Center Right\n";
	print "<option value=\"South\" selected>Bottom Center\n";
	print "<option value=\"Southwest\">Bottom Left\n";
	print "<option value=\"Southeast\">Bottom Right\n";
    if ($append == True) print "<option value=\"Append\">Appended\n";

	print "</select>\n";
}


function DisplayBlendPositionPicker($text, $name)
{
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	print "<option value=\"OVERLAY\" selected>Overlay Full Image\n";
	print "<option value=\"TILED\">Tile Image\n";
	print "<option value=\"Northwest\">Top Left\n";
	print "<option value=\"Northeast\">Top Right\n";
	print "<option value=\"Center\">Center \n";
	print "<option value=\"West\">Center Left\n";
	print "<option value=\"East\">Center Right\n";
	print "<option value=\"South\">Bottom Center\n";
	print "<option value=\"Southwest\">Bottom Left\n";
	print "<option value=\"Southeast\">Bottom Right\n";

	print "</select>\n";
}




function DisplayFuzzPicker($text, $name, $default)
{
	print $text;
	DisplaySep0();
	print "<select id=\"$name\" name=\"$name\">\n";
	for ($i = 0; $i < 100; $i++)
	{
		if ($i == $default)
		{
			print "<option value=\"$i%\" SELECTED>$i%\n";
			//RecordCommand("<option value=\"$i%\" SELECTED>$i\n");
		}
		else
		{
			print "<option value=\"$i%\">$i%\n";
			//RecordCommand("<option value=\"$i%\">$i\n");
		}
	}
	print "</select>\n";
}


function DisplayPercentPicker($text, $name)
{


	print  $text;
	DisplaySep0();
	print  "<select name=\"$name\">\n";
	print  "<option value=\"10\">10%\n";
	print  "<option value=\"20\">20%\n";
	print  "<option value=\"30\">30%\n";
	print  "<option value=\"40\" selected>40%\n";
	print  "<option value=\"50\">50%\n";
	print  "<option value=\"60\">60%\n";
	print  "<option value=\"70\">70%\n";
	print  "<option value=\"80\">80%\n";
	print  "<option value=\"90\">90%\n";
	print  "<option value=\"100\">100%\n";
	print  "</select>\n";
}


function DisplayPercentPicker2($text, $name,$setting)
{
	$v = .01;
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	for ($i=1; $i<=100; $i++)
	{
		if ($setting == $i)
			print "<option value=\"$v\" selected>$i%\n";
		else
			print "<option value=\"$v\">$i%\n";
		$v += .01;
	}
	print "</select>\n";

/*
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	print "<option value=\".10\" selected>10%\n";
	print "<option value=\".20\">20%\n";
	print "<option value=\".30\">30%\n";
	print "<option value=\".40\">40%\n";
	print "<option value=\".50\">50%\n";
	print "<option value=\".60\">60%\n";
	print "<option value=\".70\">70%\n";
	print "<option value=\".80\">80%\n";
	print "<option value=\".90\">90%\n";
	print "<option value=\"1.0\">100%\n";
	print "</select>\n";
*/
}



function DisplaySizePicker($text, $name)
{
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	print "<option value=\"10\">10%\n";
	print "<option value=\"20\">20%\n";
	print "<option value=\"30\">30%\n";
	print "<option value=\"40\" selected>40%\n";
	print "<option value=\"50\">50%\n";
	print "<option value=\"60\">60%\n";
	print "<option value=\"70\">70%\n";
	print "<option value=\"80\">80%\n";
	print "<option value=\"90\">90%\n";
	print "<option value=\"110\">110%\n";
	print "<option value=\"120\">120%\n";
	print "<option value=\"130\">130%\n";
	print "<option value=\"140\">140%\n";
	print "<option value=\"150\">150%\n";
	print "<option value=\"160\">160%\n";
	print "<option value=\"170\">170%\n";
	print "<option value=\"180\">180%\n";
	print "<option value=\"190\">190%\n";
	print "<option value=\"200\">200%\n";
	print "<option value=\"250\">250%\n";
	print "<option value=\"300\">300%\n";
	print "<option value=\"350\">350%\n";
	print "<option value=\"400\">400%\n";
	print "</select>\n";
}

function DisplayDegrees($text, $name)
{
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	for ($i=0; $i <= 360; $i++)
	{
		if ($i == 40)
			print "<option value=\"$i\" selected>$i%\n";
		else
			print "<option value=\"$i\">$i%\n";
	}
	print "</select>\n";
}



function DisplayOrientationPicker($text, $name)
{
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	print "<option value=\"landscape\" selected>Landscape\n";
	print "<option value=\"portrait\">Portrait\n";
	print "</select>\n";
}

function DisplayBorderPicker($text, $name)
{
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	print "<option value=\"Single\" selected>Single\n";
	print "<option value=\"Double\">Double\n";
	print "<option value=\"Fancy\">Fancy\n";
	print "</select>\n";
}


function DisplayLMHPicker($text, $name)
{
global $X_LOW,$X_MEDIUM,$X_HIGH;

	//print $text;
	//DisplaySep0();
	print "<select name=\"$name\">\n";
	print "<option value=\"Low\">$X_LOW\n";
	print "<option value=\"Medium\" selected>$X_MEDIUM\n";
	print "<option value=\"High\">$X_HIGH\n";
	print "</select>\n";
}


function DisplayLMHExtPicker($text, $name)
{
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	print "<option value=\"2\">1\n";
	print "<option value=\"3\">2\n";
	print "<option value=\"5\" selected>3\n";
	print "<option value=\"10\">4\n";
	print "<option value=\"20\">5\n";
	print "</select>\n";
}


function DisplayLMHExt2Picker($text, $name)
{
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	print "<option value=\"20%\">1\n";
	print "<option value=\"15%\">2\n";
	print "<option value=\"10%\" selected>3\n";
	print "<option value=\"7%\">4\n";
	print "<option value=\"4%\">5\n";
	print "</select>\n";
}


function DisplayLMHExt3Picker($text, $name)
{
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	print "<option value=\"5\">1\n";
	print "<option value=\"4\">2\n";
	print "<option value=\"3\" selected>3\n";
	print "<option value=\"2\">4\n";
	print "<option value=\"1\">5\n";
	print "</select>\n";
}

function DisplayTransPicker($text, $name)
{
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	print "<option value=\"1.0\">100 (100% Solid)\n";
	print "<option value=\"0.9\">90\n";
	print "<option value=\"0.8\">80\n";
	print "<option value=\"0.7\">70\n";
	print "<option value=\"0.6\" selected>60\n";
	print "<option value=\"0.5\">50\n";
	print "<option value=\"0.4\">40\n";
	print "<option value=\"0.3\">30\n";
	print "<option value=\"0.2\">20\n";
	print "<option value=\"0.1\">10\n";
	print "<option value=\"0.0\">0 (100% Transparent)\n";
	print "</select>\n";
}

function DisplayTextColorPicker($text, $name)
{
	print $text;
	DisplaySep0();
	print "<select name=\"$name\">\n";
	print "<option value=\"#ff0000\" selected>Red\n";
	print "<option value=\"#00ff00\">Green\n";
	print "<option value=\"#0000ff\">Blue\n";
	print "<option value=\"#ffffff\">White\n";
	print "<option value=\"#000000\">Black\n";
	print "</select>\n";
}


function DisplayGenNumPicker($text,$id,$values,$default)
{


	print $text;
	DisplaySep0();
	print "<select name=\"$id\" id=\"$id\">";
	foreach ($values as $v)
	{
		if ($default == $v)
			print ("<option value=\"$v\" selected>$v");
		else
			print ("<option value=\"$v\">$v");
	}
	print ("</select>");
}


function DisplaySelectionPicker($labels, $args)
{
	print "<table class=\"argTable\">\n";
    for ($i = 0; $i < count($labels); $i += 4)
    {
        print("<tr>\n");
        for ($j = 0; $j < 4; $j ++)
        {
            print("<td>\n");
            if (($i + $j) < count($labels))
            {
                $label = $labels[$i + $j];
                $arg = $args[$i + $j];
                print "<button class=\"argHandler\" type=\"button\" name=\"$arg\" onclick=\"selectArg('$arg')\">$label</button>\n";
            }
            else
            {
                print "&nbsp";
            }
            print("</td>\n");
        }
        print("</tr>\n");

    }
	print "</table class>\n";
}


function TestDisplaySelectionPicker($labels, $args)
{
    for ($i = 0; $i < count($labels); $i++)
    {
        $label = $labels[$i];
        $arg = $args[$i];

        print "<button class=\"argHandler\" onclick=\"selectArg('$arg')\">$label</button>\n";

    }
}





function DisplayGenStringPicker($text,$id,$values,$strings,$default)
{


	print  $text;
	DisplaySep0();
	print  "<select name=\"$id\" id=\"$id\">\n";
	$i = 0;
	foreach ($values as $v)
	{
		$s = $strings[$i];
		if ($default == $i)
			print  "<option value=\"$v\" selected>$s\n";
		else
			print  "<option value=\"$v\">$s\n";
		$i++;
	}
	print  "</select>\n";
}


function DisplayAnimCreateTable($current,$max_anim)
{
global $MAX_ANIM_FRAME_SIZE;

	print "<center>\n";
    print "<input type=\"hidden\" name=\"ANIMATION\" value=\"FALSE\">\n";
    print "<input type=\"hidden\" name=\"CURRENTFILE\" value=\"$current\">\n";
	print "<table class=\"selections\" cellspacing=10 cellpadding=10>\n";
	$i = 0;
	$col = 1;


	for ($i = 0; $i < $max_anim; $i++)
	{
		if ($col == 1)
		{
			print "<tr>\n";
		}
		print "<td align=center>\n";

		if ($i == 0)
		{
			print "1\n";
			print "<br>\n";
			print "<img src=\"$current\" width=\"50\" height=\"50\" alt=\"\">\n";
		}
		else
		{
			$j = $i + 1;
			print "$j\n";
			print "<br>\n";
			print "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$MAX_ANIM_FRAME_SIZE\">\n";
			print "<input class=selection size=40 maxLength=80 type=\"FILE\" name=\"FILENAME$j\">\n";

		}
		print "</td>\n";


		if ($col == 3)
		{
			print "</tr>\n";
			$col = 0;
		}
		$col++;
	}
	print "</table>\n";

}


function DisplayAnimInputList($current,$max_anim)
{
global $MAX_ANIM_FRAME_SIZE;

	print "<center>\n";
    print "<input type=\"hidden\" name=\"ANIMATION\" value=\"FALSE\">\n";
    print "<input type=\"hidden\" name=\"CURRENTFILE\" value=\"$current\">\n";
	$i = 0;

	for ($i = 0; $i < $max_anim; $i++)
	{
		if ($i == 0)
		{
			print "1:\n";
			if (strlen($current) > 20)
				print "<img src=\"$current\" width=\"50\" height=\"50\" alt=\"\">\n";
			else
				print "<input class=selection size=40 maxLength=80 type=\"FILE\" name=\"FILENAME$j\">\n";
		}
		else
		{
			$j = $i + 1;
			print "$j\n";
			DisplaySep1();
			print "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$MAX_ANIM_FRAME_SIZE\">\n";
			print "<input class=selection size=40 maxLength=80 type=\"FILE\" name=\"FILENAME$j\">\n";

		}
		print "<br><p>\n";
	}

}



function DisplayAnimModifyTable($current,$max_anim)
{
global $BASE_DIR;
global $MAX_ANIM_FRAME_SIZE;


	$inputFileDir = "$BASE_DIR$current";
	$inputFileName = basename($inputFileDir);

	print "<center>\n";
    print "<input type=\"hidden\" name=\"CURRENTFILE\" value=\"$current\">\n";
	print "<input type=\"hidden\" name=\"ANIMATION\" value=\"TRUE\">\n";

	print "<table class=\"selections\" cellspacing=3 cellpadding=3>\n";
    $imageList = GetAnimatedImages($inputFileDir);
    $col = $i = 1;
    foreach ($imageList as $image)
    {
		if ($col == 1)
		{
			print "<tr>\n";
		}

        $inputFileName = basename($image);
		print "<td align=center>\n";
        print "<input type=\"hidden\" name=\"FILENAME$i\" value=\"$inputFileName\">";
        print "$i \n";
        print "<br>\n";

        $path = GetConversionPath($inputFileName);
        print "<img src=\"$path\" width=\"50\" height=\"50\" alt=\"\">\n";
		print "<br>\n";
        print "Delete: <input type=checkbox name=\"DELETE$i\">\n";
        print "</td>\n";
        $i++;
		if ($col == 3)
		{
			print "</tr>\n";
			$col = 0;
		}
		$col++;
    }
    for ($i = $i; $i <= $max_anim; $i++)
    {
		if ($col == 0)
		{
			print "<tr>\n";
		}
		print "<td align=center>\n";
        print "$i <input type=hidden name=MAX_FILE_SIZE value=$MAX_ANIM_FRAME_SIZE>\n";
		print "<br\n";
        print "<input size=20 maxLength=40 type=\"FILE\" name=\"FILENAME$i\">\n";
        print "</td>\n";
		if ($col == 3)
		{
			print "</tr>\n";
			$col = 0;
		}
		$col++;

    }
	print "</table>\n";
	print "<center>\n";
}


function DisplayHiddenField($id)
{
	print  "<input type=\"hidden\" name=\"$id\" id=\"$id\" value=\"\">";
}

function DisplayFileInput($text)
{


	print  "$text\n";
	print  "<input type=hidden name=MAX_FILE_SIZE value=$MAX_FILE_SIZE>";
	print  "<input size=$XINPUTWIDTH maxLength=280 type=\"FILE\" name=\"FILENAME\">";
}

function DisplayReadOnlyText($text,$name,$size,$default)
{


	print "$text\n";
	DisplaySep0();
	print  "<input class=\"flat\" type=\"text\" id=\"$name\" name=\"$name\" value=\"$default\" size =\"$size\" readonly=\"readonly\">\n";
}

function DisplayHiddenText($text,$name,$size,$default)
{


	print "<input class=\"flat\" type=\"hidden\" id=\"$name\" name=\"$name\" value=\"$default\" size =\"$size\" readonly=\"readonly\">\n";
}


function DisplaySlowNote()
{
global $X_SLOWNOTE;

	print  "<center><P class=\"mild1\">$X_SLOWNOTE</P></center>\n";
}


function DisplayAltNote($text)
{
global $X_SLOWNOTE;

	print  "<center><P class=\"mild1\">$text</P></center>\n";
}





function DisplayCreateTable($current,$max_anim)
{
global $MAX_ANIM_FRAME_SIZE;

    print  "<center>\n";
    print  "<input type=\"hidden\" name=\"ANIMATION\" value=\"FALSE\">\n";
    print  "<input type=\"hidden\" name=\"CURRENTFILE\" value=\"$current\">\n";
    print  "<table class=\"selections\" cellspacing=10 cellpadding=10>\n";
    $i = 0;
    $col = 1;


    for ($i = 0; $i < $max_anim; $i++)
    {
        if ($col == 1)
        {
            print  "<tr>\n";
        }
        print  "<td align=center>\n";

        if ($i == 0)
        {
            print  "1\n";
            print  "<br>\n";
			if (strlen($current) > 20)
			print  "<img src=\"$current\" width=\"50\" height=\"50\" alt=\"\">\n";
			else
            print  "<input class=selection size=40 maxLength=80 type=\"FILE\" id=\"FILENAME$j\" name=\"FILENAME$j\">\n";
        }
        else
        {
            $j = $i + 1;
            print  "$j\n";
            print  "<br>\n";
            print  "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$MAX_ANIM_FRAME_SIZE\">\n";
            print  "<input class=selection size=40 maxLength=60 type=\"FILE\" id=\"FILENAME$j\" name=\"FILENAME$j\">\n";

        }
        print  "</td>\n";


        if ($col == 2)
        {
            print  "</tr>\n";
            $col = 0;
        }
        $col++;
    }
    print  "</table>\n";

}


function DisplayModifyTable($current,$max_anim)
{
global $BASE_DIR;
global $MAX_ANIM_FRAME_SIZE;


    $inputFileDir = "$BASE_DIR$current";
    $inputFileName = basename($inputFileDir);

    print  "<center>\n";
    print  "<input type=\"hidden\" name=\"CURRENTFILE\" value=\"$current\">\n";
    print  "<input type=\"hidden\" name=\"ANIMATION\" value=\"TRUE\">\n";

    print  "<table class=\"selections\" cellspacing=3 cellpadding=3>\n";
    $imageList = GetAnimatedImages($inputFileDir);
    $col = $i = 1;
    foreach ($imageList as $image)
    {
        if ($col == 1)
        {
            print  "<tr>\n";
        }

        $inputFileName = basename($image);
        print  "<td align=center>\n";
        print  "<input type=\"hidden\" name=\"FILENAME$i\" value=\"$inputFileName\">";
        //print  "$i \n";
		$seq = "SEQ$i";
        print  "<input type=\"text\" size=\"2\" maxlength=\"2\" name=\"$seq\" value=\"$i\"></input>\n";
        print  "<br>\n";

        $path = GetConversionPath($inputFileName);
        print  "<img src=\"$path\" width=\"50\" height=\"50\" alt=\"\">\n";
        print  "<br>\n";
        print  "Delete: <input type=checkbox name=\"DELETE$i\">\n";
        print  "</td>\n";
        $i++;
        if ($col == 3)
        {
            print  "</tr>\n";
            $col = 0;
        }
        $col++;
    }
    for ($i = $i; $i <= $max_anim; $i++)
    {
        if ($col == 0)
        {
            print  "<tr>\n";
        }
        print  "<td align=center>\n";
        print  "$i <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$MAX_ANIM_FRAME_SIZE\">\n";
        print  "<br>\n";
        print  "<input size=\"20\" maxLength=\"40\" type=\"FILE\" name=\"FILENAME$i\">\n";
        print  "</td>\n";
        if ($col == 3)
        {
            print  "</tr>\n";
            $col = 0;
        }
        $col++;
    }
    print  "</table>\n";
    print  "<center>\n";
}


function DisplayDivColorPicker()
{

global $BASE_PATH;

	$name = "divpick";
	print  "<center>\n";
    print  "<input type=\"radio\" name=\"$name\" checked onclick=\"changecolor('white');\">\n";
    print  "<img width=\"16\" src=\"$BASE_PATH/wimages/colors/white.png\">\n";
    DisplaySep1();

    print  "<input type=\"radio\" name=\"$name\" onclick=\"changecolor('red');\">\n";
    print  "<img width=\"16\" src=\"$BASE_PATH/wimages/colors/red.png\">\n";
    DisplaySep1();

    print  "<input type=\"radio\" name=\"$name\" onclick=\"changecolor('green');\">\n";
    print  "<img width=\"16\" src=\"$BASE_PATH/wimages/colors/green.png\">\n";
    DisplaySep1();

    print  "<input type=\"radio\" name=\"$name\" onclick=\"changecolor('blue');\">\n";
    print  "<img width=\"16\" src=\"$BASE_PATH/wimages/colors/blue.png\">\n";

	print  "</center>\n";
}


function DisplayCoreColorPicker($title,$name)
{
global $BASE_PATH;

    print "<input type=\"radio\" name=\"$name\" value=\"#ff0000\" checked>\n";
    print "<img width=\"16\" src=\"$BASE_PATH/wimages/colors/red.png\">\n";
    DisplaySep1();

    print "<input type=\"radio\" name=\"$name\" value=\"#00ff00\">\n";
    print "<img width=\"16\" src=\"$BASE_PATH/wimages/colors/green.png\">\n";
    DisplaySep1();

    print "<input type=\"radio\" name=\"$name\" value=\"#0000ff\">\n";
    print "<img width=\"16\" src=\"$BASE_PATH/wimages/colors/blue.png\">\n";
    DisplaySep1();

    print "<input type=\"radio\" name=\"$name\" value=\"#ffff00\">\n";
    print "<img width=\"16\" src=\"$BASE_PATH/wimages/colors/yellow.png\">\n";
    DisplaySep1();

    print "<input type=\"radio\" name=\"$name\" value=\"#00ffff\">\n";
    print "<img width=\"16\" src=\"$BASE_PATH/wimages/colors/cyan.png\">\n";
    DisplaySep1();

    print "<input type=\"radio\" name=\"$name\" value=\"#ff00ff\">\n";
    print "<img width=\"16\" src=\"$BASE_PATH/wimages/colors/purple.png\">\n";
    DisplaySep1();
}


function CompleteWithNoAction()
{

	$current = $_POST['CURRENTFILE'];
	if (strlen($current) < 3)
	{
        RecordCommand($current);
        echo '<html><head><title>-</title></head><body>';
        echo '<script language="JavaScript" type="text/javascript">'."\n";
        echo "parent.completeWithNoAction();";
        echo "\n".'</script></body></html>';
		return TRUE;
	}
	return FALSE;
}

function DisplayImageSelectionTable($id,$dir,$path,$maxcols,$width,$default)
{
global $X_SELECTED;


    $handle = opendir($dir);
    $files = array();
    while ($file = readdir($handle))
    {
        if (($file == '.') || ($file == '..'))
            continue;
     $files[] = $file;
    }
    closedir($handle);
    sort($files);

	$original = "$path"."00.jpg";

    print  "<center>\n";

    print  "<table class=\"selections\" cellspacing=5 cellpadding=5>";
    $i = $j = 0;
    $num = 1;
    $pdefault = 1;
    foreach ($files as $file)
    {
		if (strstr($file,"00") != FALSE)
			continue;
        $j++;
        $psel = $j;

        if ($default == $file)
            $pdefault = $j;

        if ($i == 0)
            print  "<tr>\n";
        print  "<td class=selection onclick=\"selectTableItem('$id','$path','$file','$psel')\">\n";
        print  "<img border=0 align=center src=$path$file alt=$file width=\"$width\">\n";
        print  "<br><center>$psel</center>\n";
        print  "</td>\n";
        $i++;
        if ($i == $maxcols)
        {
            print  "</tr>\n";
            $i = 0;
        }
        $num++;
    }
    print  "</table>\n";
    print  "<p><br>\n";

    print  "<input type=\"hidden\" name=\"$id\" id=\"$id\" value=\"$default\">\n";
    print  "<table class=\"selections\" cellspacing=5 cellpadding=5>";
	print "<tr>\n";
	print "<td><img border=0 src=$original width=$width></td>\n";
	print "<td><img border=0 src=\"../wimages/arrows/rightarrow1.gif\" width=50></td>\n";
    print  "<td><img id=\"IMAGE\" border=\"0\" align=\"center\" src=\"$path$default\" alt=\"$file\" width=\"$width\"></td>\n";
	print "</tr>\n";
    print  "</table>\n";
    print  "<div id=\"STATUS\" >\n$pdefault</div>\n";
	print "<br>\n";
    print  "</center>\n";

}

function GetConversionPath($path)
{
global $CONVERSION_PATH;

    $terms = explode("/",$path);
    $count = count($terms);
    $path = "$CONVERSION_PATH".$terms[$count-1];
    return $path;
}

function GetConversionDir($path)
{
global $CONVERSION_DIR;

    $terms = explode("/",$path);
    $count = count($terms);
    $path = "$CONVERSION_DIR".$terms[$count-1];
    return $path;
}



?>
