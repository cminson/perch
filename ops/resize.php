<?php
include '../common/common.inc';

$current = $_GET['CURRENTFILE'];

GetImageAttributes($current,$width,$height,$size);
RecordCommand("Resize $current $width $height");


DisplayTitle('Resize Image');
DisplayFormStart();
DisplayTextInput('Width','CLIENTX',$width,5);
DisplaySep1();
DisplayTextInput('Height','CLIENTY',$height,5);
DisplaySep1();
DisplayCheckBox('Preserve Aspect Ratio','ASPECT',false);
DisplaySep1();
DisplayConvertButton();
DisplayFormEnd();
?>
