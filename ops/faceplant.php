<?php
include '../common/common.inc';
APPLOG('FACEPLANT');



// hidden frame image load area
print "<iframe id=\"upload_target\" name=\"upload_target\" src=\"#\" style=\"width:0;height:0px;border:1px solid #fff; display: none\"></iframe>\n\n";

print "<form enctype=\"multipart/form-data\" id=\"ID_LOAD_SECONDARY_IMAGE\" action=\"./ops/loadsecondaryimagex.php\" method=\"post\" target=\"upload_target\">\n";
print "<input type=hidden name=\"MAX_FILE_SIZE\" value=\"8000000\">\n";
print "<div style=\"height:0px;overflow:hidden\">\n";
print  "<input onchange=\"submitSecondaryImage();\" size=\"90\" maxLength=\"200\" type=\"FILE\" id=\"SUBMITIMAGE\" name=\"FILENAME\">\n";
print "</div>\n";
print "</form>\n";



DisplayTitle('Extract Face');
DisplayFormStart();
DisplayRegionPicker();
DisplayLineSep1();

$imageURL = "$BASE_URL/resources/banners/banner01.jpg";
$image = "/var/www/perch/resources/banners/banner01.jpg";

print  "<img onclick=\"chooseSecondaryImage()\" style=\"border:1px solid black\" src=\"$imageURL\" width=\"80\"  id=\"ID_SECONDARY_IMAGE\" alt=\"\">\n";
print  "<input type=\"hidden\" name=\"FRAMEPATH1\" id=\"FRAMEPATH1\" value=\"$image\">\n";
print "<br>\n";
DisplayHiddenField('ID_SECONDARY_IMAGE_PATH');
DisplayHiddenField('ID_SECONDARY_REGION_PATH');


DisplayConvertButton();
DisplayFormEnd();
?>
