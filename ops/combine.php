<?php
include '../common/common.inc';
include '../common/frameform.inc';

DisplayTitle('Combine Images');
DisplayFormStart();

include '../common/framesubmit.inc';

DisplayLineSep0();
DisplayBlendPositionPicker('Position','POSITION');
DisplaySep1();
DisplayPercentPicker('Blend Level', 'BLENDLEVEL');

$x1 = $y1 = $x2 = $y2 = 0;  
DisplayHiddenText("X1","X1",4,$x1);
DisplaySep1();
DisplayHiddenText("Y1","Y1",4,$y1);
DisplaySep1();
DisplayHiddenText("X2","X2",4,$x2);
DisplaySep1();
DisplayHiddenText("Y2","Y2",4,$y2);
DisplaySep1();
DisplayHiddenText("W","w",4,$y2);
DisplaySep1();
DisplayHiddenText("H","h",4,$y2);

DisplayConvertButton();
DisplayFormEnd();
?>
