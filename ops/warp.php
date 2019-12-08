<?php
include '../common/common.inc';
APPLOG('WARP');

$labels = array('Explode', 'Fractalize', 'Implode', 'Kaleidoscope', 'Pixellate', 'Splice', 'Stretch', 'Swirl', 'Tunnel', 'Wave');
$args = array('EXPLODE', 'FRACTALIZE', 'IMPLODE', 'KAL', 'PIXEL', 'SPLICE', 'STRETCH', 'SWIRL', 'TUNNEL', 'WAVE');

DisplayTitle('Warp Image');
DisplayFormStart();
DisplayRegionPicker('Target Region','REGION');
DisplayLineSep1();
DisplayNumPicker('Setting','SETTING',1, 10, 1);
DisplayLineSep1();
DisplaySelectionPicker($labels, $args);
DisplayLineSep1();
DisplayFormEnd();
?>
