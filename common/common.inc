<?php
$BASE_URL = "http://54.71.108.91"; // also set in main.js
$BASE_PATH = "/var/www/perch";

// global env for php and javascript
$HOME_PATH = "/var/www/perh/index.html";
$CONVERSION_URL = "$BASE_URL"."/CONVERSIONS/";
$CONVERSION_PATH = "$BASE_PATH"."/CONVERSIONS/";
$LOGFILE = "/var/www/perch/LOGS/command.log";

// precomputed paths to resources
$URL_IMAGE_ARROW_UP = $BASE_URL."/resources/navs/arrow-up.jpg";
$URL_IMAGE_EX01 = $BASE_URL."/resources/defaults/tab.jpg";
$URL_IMAGE_LINEBREAK = "$BASE_URL:/wimages/tools/graypixel.jpg";
$URL_IMAGE_BUSY = "$BASE_URL/resources/utils/smallbusyicon.gif";
$PATH_IMAGE_EX01 = $BASE_PATH."/resources/defaults/tab.jpg";
$PATH_FONTS = "$BASE_PATH/resources/fonts/fontlist.txt";
$PATH_STYLES = "$BASE_PATH/resources/styles/";
$PATH_TEXTURES = "$BASE_PATH/resources/textures/";

$PATH_OPS = './ops/';

// this value is cross-definied in style1.css for img tags!!
$HEIGHT_IMAGE = 290;   //styled height of all displayed images

putenv("LD_LIBRARY_PATH=/usr/local/lib");

$MAX_FILE_SIZE = 10000000;   // 10M. Max image file size. Also set in php.ini (upload_max_filesize)
$MAX_FILE_SIZE_PRINTABLE = "10.0Mb";  //For output in help messages
$RESIZE_MAX_WIDTH = 800;  // Resize target width when resizing overly large files
$RESIZE_MAX_HEIGHT= 800;  // Resize target height when resizing overly large files

// All image names are randomly selected in this band
$IMAGE_MIN_NUMBER = 1000000;
$IMAGE_MAX_NUMBER = 9999999;

//
// These globals are set indirectly when an operation extracts a region
// for processing. When needed, these globals can then be used to access
// the x,y origin coordinates of the region.
// 
$ExtractedRegionOriginX = 0;
$ExtractedRegionOriginY = 0;

$REGIONS_PREVIOUS = 'PREVIOUS';
$REGIONS_NONE = 'NONE';


//
//
// ***************************************************************
//
//

$CurrentImage = $CurrentRegions = $HomeImage = '';
$SelectedRegion = 'ALL';

if ((isset($_POST['CURRENTIMAGE'])) == TRUE) 
    $CurrentImage = $_POST['CURRENTIMAGE'];
if ((isset($_POST['CURRENTREGIONS'])) == TRUE) 
    $CurrentRegions = trim($_POST['CURRENTREGIONS']);
if ((isset($_POST['SELECTEDREGION'])) == TRUE) 
    $SelectedRegion = trim($_POST['SELECTEDREGION']);
if ((isset($_POST['HOMEIMAGE'])) == TRUE) 
    $HomeImage = $_POST['HOMEIMAGE'];
APPLOG("POST:  CurrentRegions: $CurrentRegions SelectedRegion: $SelectedRegion");

$IMAGE_SUFFIX = '.png';
$GIF_SUFFIX = '.gif';


function ExecScript($script)
{
    return exec("$script 2>&1", $lines, $resultCode);

}

function ExtractRegionImage($imageFilePath, $region)
{
    global $ExtractedRegionOriginX, $ExtractedRegionOriginY;

    if ($region == 'ALL') return $imageFilePath;

    $regionTerms = explode('.', $region);
    $termList = $regionTerms[3];
    $dims = explode('_', $termList);
    $x = intval($dims[0]);
    $y = intval($dims[1]);
    $w = intval($dims[2]);
    $h = intval($dims[3]);
    $cropDim = $w."x$h+$x+$y";
    APPLOG("$cropDim $x $y $w $h");
    $ExtractedRegionOriginX = $x;
    $ExtractedRegionOriginY = $y;

    $outputFilePath = NewImagePath();
    $maskFilePath = GetConversionPath($region);
    APPLOG("maskFilePath $maskFilePath");

    $cutterFilePath = NewTMPImagePath();
    $script = "convert -transparent white -fuzz 40% $maskFilePath $cutterFilePath";
    ExecScript($script);
    APPLOG("$script");

    $outputFilePath = NewTMPImagePath();
    $script = "composite -geometry +0+0 $cutterFilePath $imageFilePath $outputFilePath";
    ExecScript($script);
    APPLOG("$script");

    $tmpFilePath = $outputFilePath;
    $outputFilePath = NewImagePath();
    $script = "convert -fill white -opaque black -fuzz 1% $tmpFilePath $outputFilePath";
    ExecScript($script);
    APPLOG("$script");

    $tmpFilePath = $outputFilePath;
    $outputFilePath = NewImagePath();
    $script = "convert -crop $cropDim -transparent white $tmpFilePath $outputFilePath";
    ExecScript($script);
    APPLOG("$script");

    return $outputFilePath;

}

function GetConversionURL($path)
{
    global $CONVERSION_URL;

    $terms = explode("/",$path);
    $count = count($terms);
    return "$CONVERSION_URL".$terms[$count-1];
}


function GetConversionPath($path)
{
    global $CONVERSION_PATH;

    $terms = explode("/",$path);
    $count = count($terms);
    return "$CONVERSION_PATH".$terms[$count-1];
}


function GetCurrentImagePath() 
{
    return GetConversionPath($_POST['CURRENTIMAGE']);
}


function CompleteWithNoAction()
{
    global $CurrentImage;

	if (strlen($CurrentImage) < 3)
	{
        APPLOG($CurrentImage);
        echo '<html><head><title>-</title></head><body>';
        echo '<script language="JavaScript" type="text/javascript">'."\n";
        echo "parent.completeWithNoAction();";
        echo "\n".'</script></body></html>';
		return TRUE;
	}
	return FALSE;
}


function ApplyRegionOperation($originalFilePath, $convertedFilePath, $maskFilePath)
{
    global $LastOperation;
    global $Region;

    $transColor = "'#fe00fe'";
    $tmpFilePath = NewTMPImagePath();
    $script = "convert -fill $transColor -opaque black -fuzz 1% $maskFilePath $tmpFilePath";
    ExecScript($script);
    APPLOG($script);

    $cutterFilePath = NewImagePath();
    $script = "convert -transparent white $tmpFilePath $cutterFilePath";
    ExecScript($script);
    APPLOG($script);

    $tmp1FilePath = NewTMPImagePath();
    $script = "composite -geometry +0+0 $cutterFilePath $convertedFilePath $tmp1FilePath";
    ExecScript($script);
    APPLOG($script);

    $tmp2FilePath = NewTMPImagePath();
    $script = "convert -transparent $transColor  $tmp1FilePath $tmp2FilePath";
    ExecScript($script);
    APPLOG($script);

    $resultFilePath = NewImagePath();
    $script = "composite -geometry +0+0 $tmp2FilePath $originalFilePath $resultFilePath";
    ExecScript($script);
    APPLOG($script);

    /*
    $cutterFilePath = NewImagePath();
    $script = "convert -transparent white -fuzz 1% $maskFilePath $cutterFilePath";
    ExecScript($script);
    APPLOG($script);

    $tmp1FilePath = NewTMPImagePath();
    $script = "composite -geometry +0+0 $cutterFilePath $convertedFilePath $tmp1FilePath";
    ExecScript($script);
    APPLOG($script);

    $tmp2FilePath = NewTMPImagePath();
    $script = "convert -transparent black -fuzz 0% $tmp1FilePath $tmp2FilePath";
    ExecScript($script);
    APPLOG($script);

    $resultFilePath = NewImagePath();
    $script = "composite -geometry +0+0 $tmp2FilePath $originalFilePath $resultFilePath";
    ExecScript($script);
    APPLOG($script);

     */

    APPLOG("APPLY FINAL $resultFilePath");
    $regionName = explode('.', $Region)[2];
    $LastOperation .=  "  $regionName";

    return $resultFilePath;
}


function RemoveTransparency($imagePath)
{
    global $IMAGE_SUFFIX, $IMAGE_MIN_NUMBER, $IMAGE_MAX_NUMBER;
    $suffix = '.jpg';

    $imageName = 'x'.rand($IMAGE_MIN_NUMBER,$IMAGE_MAX_NUMBER).$suffix;
    $outputFilePath = GetConversionPath($imageName);
    $script = "convert $imagePath $outputFilePath";
    ExecScript($script);
    APPLOG($script);

    $inputFilePath = $outputFilePath;
    $imageName = rand($IMAGE_MIN_NUMBER,$IMAGE_MAX_NUMBER).$IMAGE_SUFFIX;
    $outputFilePath = GetConversionPath($imageName);
    $script = "convert $inputFilePath $outputFilePath";
    ExecScript($script);
    APPLOG($script);

    return $outputFilePath;
}

function ReshapeToRegion($region, $imagePath)
{
    $regionTerms = explode('.', $region);
    $termList = $regionTerms[3];
    $dims = explode('_', $termList);
    $x = intval($dims[0]);
    $y = intval($dims[1]);
    $w = intval($dims[2]);
    $h = intval($dims[3]);
    $resizeDim = $w."x$h+$x+$y!";
    $outputFilePath = NewTMPImagePath();
    $script = "convert -transparent White -resize $resizeDim $imagePath $outputFilePath";
    ExecScript($script);
    APPLOG($script);

    return $outputFilePath;
}


function NewImageName()
{
    global $IMAGE_SUFFIX, $IMAGE_MIN_NUMBER, $IMAGE_MAX_NUMBER;

    $r = rand($IMAGE_MIN_NUMBER,$IMAGE_MAX_NUMBER);
    return "$r$IMAGE_SUFFIX";
}


function NewImagePath()
{
    global $CONVERSION_PATH;

    return $CONVERSION_PATH.NewImageName();
}


function NewTMPImageName()
{
    return 'x'.NewImageName();
}


function NewTMPImagePath()
{
    global $CONVERSION_PATH;

    return $CONVERSION_PATH.NewTMPImageName();
}


function NewRegionMaskName($imagePath, $objectName, $score)
{
    global $IMAGE_SUFFIX;

    $baseName = basename($imagePath);
    APPLOG($baseName);
    $baseName = explode('.', $baseName)[0];
    APPLOG($baseName);
    $maskName = "m$baseName.$objectName.$score$IMAGE_SUFFIX";
    return $maskName;
}


function APPLOG($text)
{
	global $LOGFILE;

    $page = $_SERVER['PHP_SELF'];
	$page = str_ireplace(".php", "", $page); 
	$page = strtoupper(basename($page));


	$date = date('m . d . y ');
	$date = date("H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'];
    $fp = fopen($LOGFILE, 'a+');
	fputs($fp, "$ip $date $text");
	fputs($fp, "\n");
	fclose($fp);
}


//
// retrieve all region mask files associated with given file
//
//
//
function compare_area($a, $b)
{
    return strnatcmp($a['area'], $b['area']);
}

function GetImageRegions($imagePath)
{
    global $CONVERSION_PATH;

    $matchName = explode('.', basename($imagePath))[0];
    $regionPathList = glob($CONVERSION_PATH.'m'.$matchName.'*');
    $count = count($regionPathList);
    APPLOG("GetImageRegions: $matchName  Count: $count");

    $regionList = array();
    foreach ($regionPathList as $regionPath)
    {
        $region = basename($regionPath);
        $dim = explode('.', $region)[3];
        $width = explode('_', $dim)[2];
        $height = explode('_', $dim)[3];
        $area = $width * $height;
        APPLOG("DIM $region $area");

        $regionArea = array('region' => $region, 'area' => $area);
        array_push($regionList, $regionArea);
    }

    usort($regionList, 'compare_area');
    $rankedRegionList = array();
    foreach ($regionList as $region)
    {
        array_push($rankedRegionList, $region['region']);
    }
    return array_reverse($rankedRegionList);
}


function DisplayRegionPicker($text, $name)
{
    global $CurrentRegions;
    global $CurrentImage;
    global $SelectedRegion;

    $regionList = explode(',', $CurrentRegions);
    APPLOG("DisplayRegionPicker SelectedRegion:  $SelectedRegion");

	print  $text;
	DisplaySep0();
	print  "<select onchange=\"saveRegionSelection()\" id=\"ID_SELECT_REGION\" name=\"$name\">\n";

    if ($SelectedRegion == "ALL")
	    print  "<option value=\"ALL\" selected>Entire Image\n";
    else
	    print  "<option value=\"ALL\">Entire Image\n";

    $regionCount = 0;
    foreach ($regionList as $regionFile)
    {
        APPLOG('DisplayRegionPicker Region Value: ', $regionFile);
        $terms = explode('.', $regionFile);
        $regionName = $terms[2];
        $regionDimensions = explode('_', $terms[3]);
        $x = $regionDimensions[0];
        $y = $regionDimensions[1];
        $width = $regionDimensions[2];
        $height = $regionDimensions[3];

        if (strpos($regionFile, 'background') == true) 
        {
            $code = ' ';
        }
        else 
        {
            $code = chr($regionCount+65).')';
            $regionCount += 1;
        }

        $regionAttribute = $code.'  '.$regionName.'  '.$width.'x'.$height;

        if ($SelectedRegion == $regionFile)
	        print  "<option value=\"$regionFile\" selected>$regionAttribute\n";
        else
	        print  "<option value=\"$regionFile\">$regionAttribute\n";
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


function GetStatString($outputFilePath)
{
    global $X_SIZE, $X_DIMENSIONS, $X_COLORCOUNT, $X_HISTORY, $LastOperation;

	GetImageAttributes($outputFilePath,$width,$height,$size);

    /*
    $psize = round(($size / 1000),0);
    $psize = $psize."kb";
    $pdim = "$width"."x"."$height";
    $stats = "$X_SIZE: $psize $pdim &nbsp;&nbsp;[$LastOperation]";

	$stats = "$stats";
     */

    // DEV
    $stats = $LastOperation;
	return ($stats);
}


function ReportError($error)
{
	$error = "Error: $error";
	APPLOG("ReportError: $error");
	echo '<html><head><title>-</title></head><body>';
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo "parent.reportOpError(\"$error\");";
	echo "\n".'</script></body></html>';
	exit(0);
}


function InformUILayer($op,$outputFilePath,$regions)
{
global $LastOperation;


    $outputFileURL = GetConversionURL($outputFilePath);
	APPLOG("InformUILayer FINAL: $outputFileURL $outputFilePath");

    if (file_exists($outputFilePath) == TRUE)
	{
        // DEV
		$stats = GetStatString($outputFilePath);
	}
	else
	{
		// no valid output image, so output an error banner instead
		$stats = "Error";
		APPLOG("InformUILayer: Error Image = $outputFilePath");
	}

    if ($regions == null) $regions = '0';

	APPLOG("$outputFilePath $stats $regions");
	echo '<html><head><title>-</title></head><body>';
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo "parent.completeImageOp(\"$outputFileURL\",\"$stats\",\"$regions\");";
	echo "\n".'</script></body></html>';

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
global $BASE_URL;

	$return = $X_RETURNTOMAINPAGE;
	print "<center>\n";
	print "<A class=navlink HREF=\"$BASE_URL\">$return</A>\n";
	print "</center>\n";
}


function DisplayOp($category, $text, $file, $target)
{
	global $BASE_URL;
	global $fileLoaded;
	global $LastOperation;
	global $PATH_OPS;

	print "<tr>\n";
    print "<td style=\"height: 12px; padding: 4px; text-align: left; white-space: nowrap; font-size: 10px;\">\n";
	print "$category";
	print "</td>\n";
    print "<td style=\"height: 12px; padding: 4px; text-align: center; white-space: nowrap; font-size: 10px;\">\n";

    print "<img height=1 src=\"$BASE_URL/wimages/tools/pixel.gif\" alt=\"\">\n";
	print "</td>\n";
    print "<td style=\"height: 12px; padding: 4px; text-align: left; white-space: nowrap; font-size: 10px;\">\n";

	if (strlen($target) < 1)
	{
		$op = "$PATH_OPS$file";
		print "<A class=navlink href=\"#home\" onclick=\"displayOp('$op')\" >$text</A>\n";
	}
	else
	{
		$op = "$PATH_OPS$file";
		print "<A class=navlink href=\"#home\" onclick=\"execSimpleOp('$op','$target')\">$text</A>\n";

	}

	print "</td></tr>\n\n\n";
	$LastOperation = $text;
}


function DisplayOpButton($label, $opHandler)
{
	global $BASE_URL;
	global $PATH_OPS;

	print "<tr>\n";

    /*
    print "<td style=\"height: 12px; padding: 4px; text-align: center; white-space: nowrap; font-size: 10px;\">\n";
    print "<img height=1 src=\"$BASE_URL/wimages/tools/pixel.gif\" alt=\"\">\n";
	print "</td>\n";
     */

    print "<td style=\"height: 12px; padding: 4px; text-align: left; white-space: nowrap; font-size: 10px;\">\n";

	$opHandler = "$PATH_OPS$opHandler";
    print "<button class=\"opHandler\" onclick=\"displayOp('$opHandler')\">$label</button>\n";

	print "</td></tr>\n\n\n";
}


function DetermineImageSize($imagePath,$w,$h)
{
    $w = $h = 0;
    $script = "identify $imagePath";
    $execResult = exec("$script 2>&1", $lines, $ConvertResultCode);
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

function GetColorCount($imagePath)
{
    if (file_exists($imagePath) == FALSE)
        return 0;

    $script = "identify -format \"%k \" $imagePath";
    $count = ExecScript($script);
    $spc = substr_count($count,' ');
    if ($spc >= 1)
    {
        $a = explode(' ',$count);
        $count = $a[0];
    }
    return $count;
}


function GetImageAttributes($imagePath, &$width, &$height, &$size)
{
    $script = "identify $imagePath";
    $execResult = exec("$script 2>&1", $lines, $ConvertResultCode);
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
    if (file_exists($imagePath) == TRUE)
		$size = filesize($imagePath);
}


function ResizeImage($imagePath, $width, $height, $exactFit)
{
    $outputFilePath = NewImagePath();
    if ($exactFit == TRUE)
        $script = "convert -resize $width"."x"."$height"."\!"." $imagePath $outputFilePath";
    else
        $script = "convert -resize $width"."x"."$height $imagePath $outputFilePath";

    ExecScript("$script 2>&1", $lines, $ConvertResultCode);
    return $outputFilePath;
}


function CheckFileSize($inputFilePath)
{

	$imageName = basename($inputFilePath);
	$outputFilePath = GetConversionURL($inputFilePath);

    GetImageAttributes($inputFilePath,$width,$height,$size);
    APPLOG("CHECKFILESIZE PRE REDUCE $size ");
    if ($size > 800000)
    {
        $imageName = NewImageName();
		if (($width > 650) || ($height > 650))
		{
			$outputFilePath = GetConversionPath($imageName);
			$outputFileURLh = GetConversionURL($imageName);

			$script = "convert -resize 650x650 $inputFilePath $outputFilePath";
            ExecScript($script);
			APPLOG("CHECKFILE POST REDUCE  $script");
		}
    }
	return $outputFilePath;
}

function DisplayConvertButton()
{
global $PATH_OPS;

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
    global $PATH_OPS, $CurrentImage, $HomeImage;

	$op = $_SERVER['PHP_SELF'];
	$op = basename($op);
	$op = str_replace(".php","x.php",$op);
	$op = "$PATH_OPS$op";

	print "\n";
	print "<center>\n";
    print  "<form id=\"ID_OP_SUBMITFORM\" enctype=\"multipart/form-data\" action=\"$op\" method=\"post\" target=\"upload_target\">\n";

	print  "<input type=\"hidden\" id=\"current\" name=\"CURRENTIMAGE\" value=\"$CurrentImage\">\n";
	print  "<input type=\"hidden\" id=\"home\" name=\"HOMEIMAGE\" value=\"$HomeImage\">\n";
	print  "<input type=\"hidden\" id=\"ARG1\" name = \"ARG1\" value=\"NA\">\n";
	print  "<input type=\"hidden\" id=\"ARG2\" value=\"NA\">\n";

	APPLOG("DISPLAY FORMSTART CURRENT=$CurrentImage");
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
global $PATH_FONTS;

    $fonts = array();
    $fh = fopen("$PATH_FONTS","r");
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
			//APPLOG("<option value=\"$i%\" SELECTED>$i\n");
		}
		else
		{
			print "<option value=\"$i%\">$i%\n";
			//APPLOG("<option value=\"$i%\">$i\n");
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



function NewDisplayRegionPicker($regions, $args)
{
	print "<table class=\"argTable\">\n";
    for ($i = 0; $i < count($regions); $i += 4)
    {
        print("<tr>\n");
        for ($j = 0; $j < 4; $j ++)
        {
            print("<td>\n");
            if (($i + $j) < count($regions))
            {
                $region = $regions[$i + $j];
                //$arg = $args[$i + $j];
                DisplayCheckBox($region, '', false);
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



function DisplayDivColorPicker()
{

global $BASE_URL;

	$name = "divpick";
	print  "<center>\n";
    print  "<input type=\"radio\" name=\"$name\" checked onclick=\"changecolor('white');\">\n";
    print  "<img width=\"16\" src=\"$BASE_URL/wimages/colors/white.png\">\n";
    DisplaySep1();

    print  "<input type=\"radio\" name=\"$name\" onclick=\"changecolor('red');\">\n";
    print  "<img width=\"16\" src=\"$BASE_URL/wimages/colors/red.png\">\n";
    DisplaySep1();

    print  "<input type=\"radio\" name=\"$name\" onclick=\"changecolor('green');\">\n";
    print  "<img width=\"16\" src=\"$BASE_URL/wimages/colors/green.png\">\n";
    DisplaySep1();

    print  "<input type=\"radio\" name=\"$name\" onclick=\"changecolor('blue');\">\n";
    print  "<img width=\"16\" src=\"$BASE_URL/wimages/colors/blue.png\">\n";

	print  "</center>\n";
}


function DisplayCoreColorPicker($title,$name)
{
global $BASE_URL;

    print "<input type=\"radio\" name=\"$name\" value=\"#ff0000\" checked>\n";
    print "<img width=\"16\" src=\"$BASE_URL/wimages/colors/red.png\">\n";
    DisplaySep1();

    print "<input type=\"radio\" name=\"$name\" value=\"#00ff00\">\n";
    print "<img width=\"16\" src=\"$BASE_URL/wimages/colors/green.png\">\n";
    DisplaySep1();

    print "<input type=\"radio\" name=\"$name\" value=\"#0000ff\">\n";
    print "<img width=\"16\" src=\"$BASE_URL/wimages/colors/blue.png\">\n";
    DisplaySep1();

    print "<input type=\"radio\" name=\"$name\" value=\"#ffff00\">\n";
    print "<img width=\"16\" src=\"$BASE_URL/wimages/colors/yellow.png\">\n";
    DisplaySep1();

    print "<input type=\"radio\" name=\"$name\" value=\"#00ffff\">\n";
    print "<img width=\"16\" src=\"$BASE_URL/wimages/colors/cyan.png\">\n";
    DisplaySep1();

    print "<input type=\"radio\" name=\"$name\" value=\"#ff00ff\">\n";
    print "<img width=\"16\" src=\"$BASE_URL/wimages/colors/purple.png\">\n";
    DisplaySep1();
}



?>

