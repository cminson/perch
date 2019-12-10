<?php
include '../common/common.inc';
APPLOG('CARTOON');

DisplayTitle('Cartoon');
DisplayFormStart();

DisplayRegionPicker('Target Region','REGION');
DisplaySep1();

$v = array(1,2,3,4,5,6,7,8,9,10);
DisplayGenNumPicker('Level','LEVEL',$v,6);

DisplaySep1();
$v = array(1,2,3,4,5,6,7,8,9,10);
DisplayGenNumPicker('Edge','EDGE',$v,4);

DisplaySep1();
$v = array(10,20,30,40,50,60,70,80,90,100,110,120,130,140,150,160,170,180,190,200);
DisplayGenNumPicker('Brightness','BRIGHTNESS', $v,130);

DisplaySep1();
$v = array(10,20,30,40,50,60,70,80,90,100,110,120,130,140,150,160,170,180,190,200);
DisplayGenNumPicker('Saturation','SATURATION',$v,150);

DisplayConvertButton();

DisplayFormEnd();
?>
