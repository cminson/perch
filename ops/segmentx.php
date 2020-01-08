<?php
include "../common/common.inc";

$inputFilePath = GetCurrentImagePath();
APPLOG("SEGMENT $inputFilePath");


function ExitWithError($error)
{
    global $LastOperation;

	$errorReport = "Error: $error";
    $LastOperation = $errorReport;
	echo '<html><head><title>-</title></head><body>';
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo "parent.reportLoadError(\"$errorReport\");";
	echo "\n".'</script></body></html>';
    exit();
}


//
// Exec AI segment analysis of uploaded file
//
// specify path to downgraded venv tensorflow (1.14)
// how to do this as current mrcnn deosn't work in tf 2.0
//
//

$regions = "";
$script = escapeshellcmd("/var/www/perch/VENV/bin/python3 ./mlsegment.py $inputFilePath");
shell_exec($script);
APPLOG("XLOAD SEGMENT ANALYSIS: $script");

$regionList = GetImageRegions($inputFilePath, null);
$regions = Implode(',', $regionList);
APPLOG("SEGMENTX REGIONS: $regions");

// inform javascript caller that image regions are complete
echo trim($regions);

/*
echo '<html><head><title>-</title></head><body>';
echo '<script language="JavaScript" type="text/javascript">'."\n";
echo "parent.completeImageAnalysis(\"$inputFilePath\", \"$regions\");";
echo "\n".'</script></body></html>';
 */



?>
