<?php
include '../common/common.inc';

$Title = 'Tint';

$current = $_POST['CURRENTFILE'];

DisplayTitle($Title);
DisplayFormStart();
DisplayColorPicker('Tint Color','COLOR','COLOR1','#ff0000');
DisplaySep1();
DisplayPercentPicker('Tint Level','TINTLEVEL','TINTLEVEL');
DisplayConvertButton();
DisplayFormEnd();
?>
