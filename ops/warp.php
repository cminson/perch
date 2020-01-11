<?php
include '../common/common.inc';
APPLOG('WARP');

$LABELS = array('Explode', 'Fractalize', 'Implode', 'Kaleidoscope', 'Pixellate', 'Splice', 'Stretch', 'Swirl', 'Tunnel', 'Wave');
$VALUES = array('EXPLODE', 'FRACTALIZE', 'IMPLODE', 'KAL', 'PIXEL', 'SPLICE', 'STRETCH', 'SWIRL', 'TUNNEL', 'WAVE');

DisplayTitle('Warp Image');
DisplayFormStart();
DisplayRegionPicker();
DisplayLineSep1();
DisplayOpPicker($LABELS, $VALUES);
DisplaySep4();
DisplaySettingPicker(1, 10);
DisplayLineSep1();
DisplayConvertButton();
DisplayFormEnd();
?>
