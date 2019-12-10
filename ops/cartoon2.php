<?php
include '../zcommon/common.inc';

$Title = $X_PHOTOTOCARTOON;
$DEFAULT="01.jpg";
$EX_DIR = "$BASE_DIR/wimages/examples/cartoon/";
$EX_PATH = "$BASE_PATH/wimages/examples/cartoon/";

DisplayMainPageReturn();
DisplayTitle($Title);
DisplayFormStart();
DisplaySelectionTable('SETTING',$EX_DIR,$EX_PATH,6,100,100,FALSE,$DEFAULT);
DisplayConvertButton();
DisplayFormEnd();
?>
