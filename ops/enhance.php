<?php
include '../common/common.inc';

DisplayFormStart();

$labels = array('Toon1', 'Toon2', 'Toon3', 'Turbelence', 'Vibrancy', 'WoodCut', 'ZeroSketch', 'Outline', 'Flip Vertical', 'Flip Horizonal', 'Reduce Colors', 'Blender', 'Twilight', 'Pencil', 'Wash', 'Fossil', 'Sketch', 'Normalize', 'Negate', 'Hardlight', 'Softlight', 'Abstract', 'Paint', 'Colorize', 'Charcoal', 'Sepia', 'Vignette', 'Instant Enhance', 'HDR', 'Smooth', 'Enrich', 'Blur');
$args = array('TOON1', 'TOON2', 'TOON3', 'TURBULENCE', 'VIBRANCY', 'WOODCUT', 'ZERO', 'OUTLINE', 'FLIPVERT', 'FLIPHORI', 'REDUCECOLORS', 'BLENDER', 'TWILIGHT', 'PENCIL', 'WASH', 'FOSSIL', 'SKETCH', 'NORMALIZE', 'NEGATE', 'HARDLIGHT', 'SOFTLIGHT', 'ABSTRACT', 'PAINT', 'COLORIZE', 'CHARCOAL', 'SEPIA', 'VIGNETTE', 'INSTANT', 'HDR', 'SMOOTH', 'ENRICH', 'BLUR');

DisplayRegionPicker('Target Region','REGION');
DisplayLineSep1();
DisplayNumPicker('Setting','SETTING',1, 10, 1);
DisplayLineSep1();
DisplaySelectionPicker($labels, $args);
DisplayLineSep1();
DisplayLineSep1();

DisplayFormEnd();



DisplayFormEnd();
?>
