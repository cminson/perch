<?php
include '../common/common.inc';

$Title = 'Tint';

RecordCommand("TINT REGION $current");
$regionList = GetImageRegions();

DisplayTitle($Title);
DisplayFormStart();
DisplayRegionPicker('Target Region','REGION',$regionList);
DisplaySep1();
DisplayColorPicker('Tint Color','COLOR','COLOR1','#ff0000');
DisplaySep1();
DisplayPercentPicker('Tint Level','TINTLEVEL','TINTLEVEL');
DisplayConvertButton();
DisplayFormEnd();
?>
