<?php

include '../common/common.inc';
APPLOG('TINT');

DisplayTitle('Tint Image');
DisplayFormStart();
DisplayRegionPicker('Target Region','REGION');
DisplaySep1();
DisplayColorPicker('Tint Color','COLOR','COLOR1','#ff0000');
DisplaySep1();
DisplayPercentPicker('Tint Level','TINTLEVEL','TINTLEVEL');
DisplayConvertButton();
DisplayFormEnd();

?>
