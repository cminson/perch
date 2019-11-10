<?php
include '../common/common.inc';

DisplayTitle('Reformat: Change Image Format');
DisplayFormStart();

$labels = array('BMP', 'GIF', 'JPG', 'PNG');
$args = array('BMP', 'GIF', 'JPG', 'PNG');

DisplaySelectionPicker($labels, $args);

DisplayLineSep1();
DisplayFormEnd();
?>
