<?php
include '../common/common.inc';

DisplayTitle('Reformat: Change Image Format');
DisplayFormStart();

$labels = array('Marble', 'Granite', 'Sand', 'Metal');
$args = array('MARBLE', 'GRANITE', 'SAND', 'METAL');

DisplaySelectionPicker($labels, $args);

DisplayLineSep1();
DisplayFormEnd();
?>
