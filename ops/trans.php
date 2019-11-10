<?php
include '../common/common.inc';

$Title = "Make Part Of Image Transparent";

DisplayLineSep0();
DisplayDivColorPicker();
DisplayLineSep0();
DisplayLineSep0();

DisplayTitle($Title);
DisplayLineSep0();
DisplayFormStart();
DisplayColorPicker('Color','PICKCOLOR','COLOR1','');
DisplaySep1();
DisplayFuzzPicker('Approximate Color (Fuzz)','FUZZ',1);
DisplayHiddenField('CLIENTX');
DisplayHiddenField('CLIENTY');
DisplayConvertButton();
DisplayLineSep0();

if (IsHandHeld() == FALSE)
{
    $text = "Click on the image and then click Convert. The color at the point you selected will be the transparent color.  <P> <strong>- OR -</strong> <P> Manually pick a color and click Convert. The color chosen will be the transparent color.  <P><strong>- OR -</strong><P>Just type in the color to make transparent (red, yellow etc)  <P><BR> If your target color is not 100% pure, use the Approximate Color Fuzz option to include colors that are adjacent or similiar <P>";
    print $text;
}
DisplayFormEnd();
?>
