<?php
// 
// common code for image loading
// this includes the default image and the file chooser

// frame load image and choose button
print  "<img onclick=\"chooseFrameFile(1)\" style=\"border:1px solid black\" src=\"$PATH_IMAGE_EX01\" width=\"80\"  id=\"FRAME1\" alt=\"\">\n";
print  "<input type=\"hidden\" name=\"FRAMEPATH1\" id=\"FRAMEPATH1\" value=\"$DIR_IMAGE_EX01\">\n";
print "<br>\n";
print "<img onclick=\"chooseFrameFile(1)\" border=\"0\" src=\"$PATH_IMAGE_ARROW_UP\" width=\"20\" alt=\"\">\n";

?>


