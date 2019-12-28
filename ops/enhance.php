<?php
include '../common/common.inc';
APPLOG('ENHANCE');

$LABELS = array('Blur', 'Smear', 'Sharpen', 'Unsharpen', 'Enrich', 'Negate', 'HDR', 'Smooth', 'Wash', 'Reduce Colors');
$VALUES = array('BLUR', 'SMEAR', 'SHARPEN', 'UNSHARPEN', 'ENRICH', 'NEGATE', 'HDR', 'SMOOTH', 'WASH', 'REDUCECOLORS');

DisplayTitle('Enhance Image');

DisplayFormStart();
DisplayRegionPicker();
DisplayLineSep1();
DisplayOpPicker($LABELS, $VALUES, 0);
DisplayLineSep1();
DisplayConvertButton();
DisplayFormEnd();
?>
