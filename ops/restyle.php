<?php
include '../common/common.inc';

DisplayTitle('Apply Color Effects');

DisplayFormStart();

$labels = array('Esher');
$args = array('ESHER');

DisplayNumPicker('Setting','SETTING',1, 10, 1);
DisplayLineSep1();
DisplaySelectionPicker($labels, $args);
DisplayLineSep1();
DisplayLineSep1();

DisplayFormEnd();
?>
