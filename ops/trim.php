<?php
include '../zcommon/common.inc';

RecordCommand('ENTER');
$Title = $X_TRIM;
$LastOperation = $X_TRIM;

DisplayMainPageReturn();
DisplayTitle($Title);
DisplayFormStart();
$v = array();
$s = array();
for ($i=1; $i < 20; $i += 1)
{
    $s[] = "$i%";
    $v[] = $i;

}
DisplayGenStringPicker('','PERCENT',$v,$s,9);
DisplayConvertButton();
DisplayFormEnd();
?>
