<?php
include '../common/common.inc';

DisplayTitle('Apply Color Effects');
DisplayFormStart();

$labels = array('Bleach', 'Wash', 'Heat', 'Back&White', 'Charcoal', 'Sepia', 'Paint');
$args = array('BLEACH', 'WASH', 'HEAT', 'BLACKWHITE', 'CHARCOAL', 'SEPIA', 'PAINT');

DisplayRegionPicker('Target Region','REGION');
DisplayLineSep1();
DisplayNumPicker('Setting','SETTING',1, 10, 1);
DisplayLineSep1();
DisplaySelectionPicker($labels, $args);
DisplayLineSep1();
DisplayLineSep1();

DisplayFormEnd();
?>
