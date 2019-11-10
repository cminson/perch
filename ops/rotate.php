<?php
include '../common/common.inc';

$Title = 'Rotate Image';
$LastOperation = 'Rotated';

DisplayTitle($Title);
DisplayFormStart();

$v= array('CLOCKWISE','COUNTERCLOCKWISE');
$s= array('&rarr;','&larr;');
DisplayGenStringPicker('Direction','DIRECTION',$v,$s,1);
DisplaySep1();
$v = array();
$s = array();
for ($i=1; $i < 360; $i += 1)
{
	$v[] = $i;
	$s[] = "$i%";

}
DisplayGenStringPicker('Degrees','DEGREES',$v,$s,15);
DisplaySep1();
DisplayCheckBox('Adjust Space','ADJUST',false);
DisplayConvertButton();
DisplayFormEnd();
?>
