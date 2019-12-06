<?php
include '../common/common.inc';
APPLOG('LABEL');

$DEFAULT="ariali.ttf.png";

DisplayTitle('Label Image');
DisplayFormStart();
DisplayRegionPicker('Target Region','REGION');
DisplaySep1();
DisplayTextInput('Line 1','LABEL1','text',60);
DisplayLineSep0();
DisplayTextInput('Line 2','LABEL2','',60);
DisplayLineSep1();
DisplayPositionPicker('Position', 'POSITION', True);
DisplayLineSep1();
DisplayFontPicker('Font', 'Helvetica', 'FONTS');
DisplaySep4();
DisplayFontSizePicker('Size','FONTSIZE',20);
DisplayLineSep1();
DisplayColorSelector('Font Color', 'White', 'LABELCOLOR', False);
DisplaySep4();
DisplayColorSelector('Background Color', 'Black', 'BACKGROUNDCOLOR', True);
DisplaySep1();
DisplayConvertButton();
DisplayFormEnd();

?>
