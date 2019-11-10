<?php

// 
// common code for image loading
// this includes the hidden load image area and the hidden submit form
// 

// hidden frame image load area
print "<iframe id=\"frameupload_target\" name=\"frameupload_target\" src=\"#\" style=\"width:0;height:0px;border:1px solid #fff; display: none\"></iframe>\n\n";


// hidden submit form
print "<form enctype=\"multipart/form-data\" id=\"ID_FRAME_LOADFORM\" action=\"./ops/loadsingleframex.php\" method=\"post\" target=\"frameupload_target\">\n";
print "<input type=hidden name=\"MAX_FILE_SIZE\" value=\"8000000\">\n";
print "<div style=\"height:0px;overflow:hidden\">\n";
print  "<input onchange=\"submitFrameFile();\" size=\"90\" maxLength=\"200\" type=\"FILE\" id=\"ID_SUBMIT_FRAMEFILE\" name=\"FRAMEFILENAME\">\n";
print "</div>\n";
print "</form>\n";

?>

