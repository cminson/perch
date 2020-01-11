<?php 
include '../common/common.inc';
APPLOG('Texture');

$LABELS = array('Glass Tiles', 'Golden', 'Granite', 'History', 'Ice', 'Marble', 'Metal', 'Old Paper', 'Sand', 'Sketch', 'Silk', 'Wet Clay', 'Ripples', 'Snakes', 'Curves');
$VALUES = array('GLASSTILES', 'GOLD', 'GRANITE', 'HISTORY', 'ICE', 'MARBLE', 'METAL', 'OLDPAPER', 'SAND', 'SKETCH', 'SILK', 'WETCLAY', 'RIPPLES', 'SNAKES', 'CURVES');

DisplayTitle('Texture Image');

DisplayFormStart();
DisplayRegionPicker();
DisplayLineSep1();
DisplayOpPicker($LABELS, $VALUES);
DisplayLineSep1();
DisplayConvertButton();
DisplayFormEnd();

?>
