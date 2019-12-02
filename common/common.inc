<?php
$BASE_PATH = "http://54.71.108.91"; // also set in main.js

// global env for php and javascript
$HOME_DIR = "/var/www/perh/index.html";
$BASE_DIR = "/var/www/perch";
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


$MAX_FILE_SIZE = 10000000;   // 10M. Max image file size. Also set in php.ini (upload_max_filesize)
$MAX_FILE_SIZE_PRINTABLE = "10.0Mb";  //For output in help messages
$RESIZE_MAX_WIDTH = 800;  // Resize target width when resizing overly large files
$RESIZE_MAX_HEIGHT= 800;  // Resize target height when resizing overly large files

$RecordCommandOn=TRUE;
if ((isset($_POST['CURRENTIMAGE'])) == TRUE)
{
    $CurrentImage = $_POST['CURRENTIMAGE'];
    $HomeImage = $_POST['HOMEIMAGE'];
    RecordCommand("Home: $HomeImage Current: $CurrentImage");
}


$IMAGE_SUFFIX = '.png';



/////////////////////////////////////////////////////
//Common Functions
////////////////////////////////////////////////////
//
//
function MakeRandom()
{
    $rand = rand(100000,999999);
    return $rand;
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


function ApplyRegionOperation($originalFileDir, $convertedFileDir, $maskFileDir)
{

    $imageName = NewTMPImageName();
    $cutterFileDir = GetConversionDir($imageName);
    $command = "convert -transparent white -fuzz 40% $maskFileDir $cutterFileDir";
    RecordCommand($command);
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);

    $imageName = NewTMPImageName();
    $tmp1FileDir = GetConversionDir($imageName);
    $command = "composite -geometry +0+0 $cutterFileDir $convertedFileDir $tmp1FileDir";
    RecordCommand($command);
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);

    $imageName = NewTMPImageName();
    $tmp2FileDir = GetConversionDir($imageName);
    $command = "convert -transparent black -fuzz 40% $tmp1FileDir $tmp2FileDir";
    RecordCommand($command);
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);

    $imageName = NewImageName();
    $resultFileDir = GetConversionDir($imageName);
    $command = "composite -geometry +0+0 $tmp2FileDir $originalFileDir $resultFileDir";
    RecordCommand($command);
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);

    return $resultFileDir;
}


function NewImageName()
{
    global $IMAGE_SUFFIX;

    $r = rand(1000000,9999999);
    return "$r$IMAGE_SUFFIX";
}


function NewTMPImageName()
{
    return 'x'.NewImageName();
}


function NewRegionMaskName($imageDir, $objectName, $score)
{
    global $IMAGE_SUFFIX;

    $baseName = basename($imageDir);
    RecordCommand($baseName);
    $baseName = explode('.', $baseName)[0];
    RecordCommand($baseName);
    $maskName = "m$baseName.$objectName.$score$IMAGE_SUFFIX";
    return $maskName;
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


//
// retrieve all region mask files associated with given file
//
function GetImageRegions($outputFileDir)
{
    global $CONVERSION_DIR, $HomeImage;

    $matchName = explode('.', basename($outputFileDir))[0];
    RecordCommand("GetImageRegions $matchName");
    return array_reverse((glob($CONVERSION_DIR.'m'.$matchName.'*')));
}


function DisplayRegionPicker($text, $name)
{
    global $CONVERSION_DIR, $HomeImage;

    $regionList = GetImageRegions($HomeImage);

	print  $text;
	DisplaySep0();
	print  "<select name=\"$name\">\n";
	print  "<option value=\"ALL\" selected>Entire Image\n";

    foreach ($regionList as $regionFile)
    {
        $terms = explode('.', $regionFile);
        $regionName = $terms[1];
	    print  "<option value=\"$regionFile\">$regionName\n";
    }
	print  "</select>\n";

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

    $imageName = basename($inputFileDir);
    $imageName = StripSuffix($imageName);
    //RecordCommand("target Image: $imageName");
    $imageName = TMPPNG();
    $outputFileDir = GetConversionDir($imageName);
    $command = "convert $inputFileDir $outputFileDir";
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);

    $baseFile = "$imageName-0$PNGSUFFIX";

    $baseFileDir = GetConversionDir($baseFile);
    //RecordCommand("Base Image: $baseFileDir ");
    if (file_exists($baseFileDir) == TRUE)
        return $baseFile;
    return FALSE;
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
    $imageName = NewImageName();
    $outputFileDir = GetConversionDir($imageName);
    if ($exactFit == TRUE)
        $command = "convert -resize $width"."x"."$height"."\!"." $imageDir $outputFileDir";
    else
        $command = "convert -resize $width"."x"."$height $imageDir $outputFileDir";

    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    return $outputFileDir;
}


function ResizeTMPImage($imageDir, $width, $height, $exactFit)
{

    $imageName = basename($imageDir);
    $imageName = TMPName($imageName);
    $outputFileDir = GetConversionDir($imageName);
    if ($exactFit == TRUE)
        $command = "convert -resize $width"."x"."$height"."\!"." $imageDir $outputFileDir";
    else
        $command = "convert -resize $width"."x"."$height $imageDir $outputFileDir";

    RecordCommand("XRESIZE: $command");
    $execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
    return $outputFileDir;
}


function DisplayCurrentClickableImageReturnMain($current,$returnToMainPage)
{
	global $WIDTH_IMAGE;

	print "<center>\n";
	print "<A class=navlink HREF=\"$BASE_PATH/index.html?CURRENTIMAGE=$current\">$returnToMainPage</A>\n";

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

	$imageName = basename($inputFileDir);
	$outputFilePath = GetConversionPath($inputFileDir);

    GetImageAttributes($inputFileDir,$width,$height,$size);
    RecordCommand("CHECKFILESIZE PRE REDUCE $size ");
    if ($size > 800000)
    {
        $imageName = NewImageName();
		if (($width > 650) || ($height > 650))
		{
			$outputFileDir = GetConversionDir($imageName);
			$outputFilePath = GetConversionPath($imageName);

			$command = "convert -resize 650x650 $inputFileDir $outputFileDir";
			$execResult = exec("$command 2>&1", $lines, $ConvertResultCode);
			RecordCommand("CHECKFILE POST REDUCE  $command");
		}
    }
	return $outputFilePath;
}

function DisplayConvertButton()
{
global $DIR_OPS;

	print "<P><BR>";
	print "<center>";
    print "<button class=\"convert\" type=\"button\" onclick=\"submitOpForm()\")>Convert</button>\n";
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
    global $DIR_OPS, $CurrentImage, $HomeImage;

	$op = $_SERVER['PHP_SELF'];
	$op = basename($op);
	$op = str_replace(".php","x.php",$op);
	$op = "$DIR_OPS$op";

	print "\n";
	print "<center>\n";
    print  "<form id=\"ID_OP_SUBMITFORM\" enctype=\"multipart/form-data\" action=\"$op\" method=\"post\" target=\"upload_target\">\n";

	print  "<input type=\"hidden\" id=\"current\" name=\"CURRENTIMAGE\" value=\"$CurrentImage\">\n";
	print  "<input type=\"hidden\" id=\"home\" name=\"HOMEIMAGE\" value=\"$HomeImage\">\n";
	print  "<input type=\"hidden\" id=\"ARG1\" name = \"ARG1\" value=\"NA\">\n";
	print  "<input type=\"hidden\" id=\"ARG2\" value=\"NA\">\n";

	RecordCommand("DISPLAY FORMSTART CURRENT=$CurrentImage");
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
    global $CurrentImage;

	if (strlen($CurrentImage) < 3)
	{
        RecordCommand($CurrentImage);
        echo '<html><head><title>-</title></head><body>';
        echo '<script language="JavaScript" type="text/javascript">'."\n";
        echo "parent.completeWithNoAction();";
        echo "\n".'</script></body></html>';
		return TRUE;
	}
	return FALSE;
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
