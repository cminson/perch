<?php
include '../common/common.inc';
APPLOG('EXTRACT');

$regionList = GetImageRegions($CurrentImage);

DisplayTitle('Extract Image Elements');
DisplayFormStart();
DisplayRegionPicker('Target Region','REGION', $regionList);
DisplayLineSep1();
DisplayLineSep1();
DisplayConvertButton();

DisplayFormEnd();
?>
