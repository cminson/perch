<?php
include '../common/common.inc';

$regionList = GetImageRegions();

DisplayTitle('Reformat: Change Image Format');
DisplayFormStart();

$labels = array('Marble', 'Granite', 'Sand', 'Metal');
$args = array('MARBLE', 'GRANITE', 'SAND', 'METAL');

DisplayRegionPicker('Target Region','REGION',$regionList);
DisplayLineSep1();
DisplaySelectionPicker($labels, $args);

DisplayLineSep1();
DisplayFormEnd();
?>
