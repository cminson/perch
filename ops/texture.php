<?php

include '../common/common.inc';
APPLOG('Texture');

$labels = array('Glass Tiles', 'Golden', 'Granite', 'History', 'Ice', 'Marble', 'Metal', 'Old Paper', 'Sand', 'Sketch', 'Silk', 'Wet Clay', 'Ripples', 'Snakes', 'Curves');
$args = array('GLASSTILES', 'GOLD', 'GRANITE', 'HISTORY', 'ICE', 'MARBLE', 'METAL', 'OLDPAPER', 'SAND', 'SKETCH', 'SILK', 'WETCLAY', 'RIPPLES', 'SNAKES', 'CURVES');

DisplayTitle('Reformat: Change Image Format');
DisplayFormStart();
DisplayRegionPicker('Target Region','REGION');
DisplayLineSep1();
DisplaySelectionPicker($labels, $args);
DisplayLineSep1();
DisplayFormEnd();

?>
