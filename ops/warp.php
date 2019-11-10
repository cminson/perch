<?php
include '../common/common.inc';

DisplayTitle('Warp Image');
DisplayFormStart();

$labels = array('Encircle', 'Explode', 'Fractalize', 'Implode', 'Kaleidoscope', 'Pixellate', 'Splice', 'Stretch', 'Swirl', 'Tunnel', 'Wave');
$args = array('ENCIRCLE', 'EXPLODE', 'FRACTALIZE', 'IMPLODE', 'KAL', 'PIXEL', 'SPLICE', 'STRETCH', 'SWIRL', 'TUNNEL', 'WAVE');

DisplayNumPicker('Setting','SETTING',1, 10, 1);
DisplayLineSep1();
DisplaySelectionPicker($labels, $args);
DisplayLineSep1();
DisplayFormEnd();
?>
