<?php
include '../common/common.inc';
APPLOG('LABEL');

DisplayTitle('Label Image');

DisplayFormStart();
DisplayRegionPicker('Target Region','REGION');
DisplayLineSep1();
DisplayTextInput('Label:&nbsp;','LABEL1','text', 30);
DisplaySep4();
DisplayPositionPicker('Position:&nbsp;', 'POSITION', True);
DisplayLineSep1();
DisplayColorSelector('Font Color:&nbsp;', 'White', 'LABELCOLOR', False);
DisplaySep4();
DisplayColorSelector('Background Color:&nbsp', 'Black', 'BACKGROUNDCOLOR', True);
DisplayLineSep1();
DisplaySep4();
DisplayFontPicker('Font:&nbsp;', 'Helvetica', 'FONTS');
DisplaySep4();
DisplayFontSizePicker('Size:&nbsp;','FONTSIZE',20);
DisplayLineSep1();
DisplaySep1();
DisplayConvertButton();
DisplayFormEnd();

?>
