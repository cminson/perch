<?php

include '../common/common.inc';
APPLOG('Texture');

$labels = array('Glass Tiles', 'Granite', 'History', 'Ice', 'Marble', 'Metal', 'Old Paper', 'Sand', 'Silk', 'Wet Clay', 'Ripples', 'Snakes', 'Curves');
$args = array('GLASSTILES', 'GRANITE', 'HISTORY', 'ICE', 'MARBLE', 'METAL', 'OLDPAPER', 'SAND', 'SILK', 'WETCLAY', 'RIPPLES', 'SNAKES', 'CURVES');

DisplayTitle('Reformat: Change Image Format');
DisplayFormStart();
DisplayRegionPicker('Target Region','REGION');
DisplayLineSep1();
DisplaySelectionPicker($labels, $args);
DisplayLineSep1();
DisplayFormEnd();

?>
