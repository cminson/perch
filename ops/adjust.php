<?php
include '../common/common.inc';


DisplayTitle('Adjust Image');
DisplayFormStart();

$arg = 'UP_CONTRAST';

DisplayRegionPicker();
DisplayLineSep0();

print "<table class=\"argTable\">\n";
print("<tr>\n");

print("<td>\n");
print("<img onclick=\"selectArg('UP_BRIGHT')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-up.jpg\" alt=\"\"></a>\n");
print("<img onclick=\"selectArg('DOWN_BRIGHT')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-down.jpg\" alt=\"\"></a>\n");
print("</td>\n");

print("<td>\n");
print("<img onclick=\"selectArg('UP_CONTRAST')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-up.jpg\" alt=\"\"></a>\n");
print("<img onclick=\"selectArg('DOWN_CONTRAST')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-down.jpg\" alt=\"\"></a>\n");
print("</td>\n");

print("<td>\n");
print("<img onclick=\"selectArg('UP_HUE')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-up.jpg\" alt=\"\"></a>\n");
print("<img onclick=\"selectArg('DOWN_HUE')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-down.jpg\" alt=\"\"></a>\n");
print("</td>\n");

print("<td>\n");
print("<img onclick=\"selectArg('UP_SATURATE')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-up.jpg\" alt=\"\"></a>\n");
print("<img onclick=\"selectArg('DOWN_SATURATE')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-down.jpg\" alt=\"\"></a>\n");
print("</td>\n");

/*
print("<td>\n");
print("<img onclick=\"selectArg('UP_SHARP')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-up.jpg\" alt=\"\"></a>\n");
print("<img onclick=\"selectArg('DOWN_SHARP')\" style=\"border:0\" width=\"60\" src=\"$BASE_URL/resources/navs/arrow-down.jpg\" alt=\"\"></a>\n");
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
