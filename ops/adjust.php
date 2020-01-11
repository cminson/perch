<?php
include '../common/common.inc';
APPLOG('ADJUST');

DisplayTitle('Adjust Image');
DisplayFormStart();

DisplayRegionPicker();
DisplayLineSep0();

print "<table class=\"argTable\">\n";
print("<tr>\n");

print("<td>\n");
print("<img onclick=\"executeWithArg('UP_BRIGHT')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-up.jpg\" alt=\"\"></a>\n");
print("<img onclick=\"executeWithArg('DOWN_BRIGHT')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-down.jpg\" alt=\"\"></a>\n");
print("</td>\n");

print("<td>\n");
print("<img onclick=\"executeWithArg('UP_CONTRAST')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-up.jpg\" alt=\"\"></a>\n");
print("<img onclick=\"executeWithArg('DOWN_CONTRAST')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-down.jpg\" alt=\"\"></a>\n");
print("</td>\n");

print("<td>\n");
print("<img onclick=\"executeWithArg('UP_HUE')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-up.jpg\" alt=\"\"></a>\n");
print("<img onclick=\"executeWithArg('DOWN_HUE')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-down.jpg\" alt=\"\"></a>\n");
print("</td>\n");

print("<td>\n");
print("<img onclick=\"executeWithArg('UP_SATURATE')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-up.jpg\" alt=\"\"></a>\n");
print("<img onclick=\"executeWithArg('DOWN_SATURATE')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-down.jpg\" alt=\"\"></a>\n");
print("</td>\n");

/*
print("<td>\n");
print("<img onclick=\"executeWithArg('UP_SHARP')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-up.jpg\" alt=\"\"></a>\n");
print("<img onclick=\"executeWithArg('DOWN_SHARP')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-down.jpg\" alt=\"\"></a>\n");
print("</td>\n");
 */

print("</tr>\n");

print("<tr>\n");
print("<td><center>Brigtness</center></td>");
print("<td><center>Contrast</center></td>");
print("<td><center>Hue</center></td>");
print("<td><center>Saturation</center></td>");
//print("<td><center>Sharpnss</center></td>");
print("</tr>\n");


print "</table\n";

DisplayFormEnd();
?>
