<?php
include '../common/common.inc';
APPLOG('EXTRACT');

$Title = 'Extract Image';

DisplayTitle('Extract Image Elements');
DisplayFormStart();
DisplayRegionPicker('Target Region','REGION');
DisplayLineSep1();
DisplayLineSep1();
DisplayConvertButton();
DisplayFormEnd();
?>
