<?php
include '../common/common.inc';


DisplayTitle('Trim Image');
DisplayFormStart();
DisplayNumPicker("Pixels",'SETTING',1,100,10);
DisplaySep1();
$v= array('NORTH','SOUTH','NORTHSOUTH','WEST','EAST','WESTEAST','ALL');
$s = array('Top','Bottom','Top & Bottom','Left','Right','Left & Right','All Sides');
DisplayGenStringPicker('Side(s) to Trim','GRAVITY',$v,$s,0);
DisplayConvertButton();
DisplayFormEnd();

?>
