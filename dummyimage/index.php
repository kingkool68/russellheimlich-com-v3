<?php

$x = strtolower($_GET["x"]);
list($width, $height) = split('x', $x);

$angle = 0;
$fontsize = $width/16;
if($fontsize<= 5) {
	$fontsize = 5;
}

$font = "FrizQuadrataBT.ttf";

$im = imageCreate($width,$height);
$gray = imageColorAllocate($im, 204, 204, 204);
$black = imageColorAllocate($im, 0, 0, 0);

$text = $width." X ".$height;

$textBox = imagettfbbox_t($fontsize, $angle, $font, $text);
$textWidth = $textBox[4] - $textBox[1];
$textHeight = abs($textBox[7])+abs($textBox[1]);

$textX = ($width - $textWidth)/2;
$textY = ($height - $textHeight)/2 + $textHeight;

$text = $textX . " X " . $textY;
//$text = $textHeight . " X " . $textY;

imageFilledRectangle($im, 0, 0, $width, $height, $gray);	
imagettftext($im, $fontsize, 0, $textX, $textY, $black, $font, $text);	
header('Content-type: image/gif');
	
imagegif($im);
imageDestroy($im);
	
function imagettfbbox_t($size, $angle, $fontfile, $text){
    // compute size with a zero angle
    $coords = imagettfbbox($size, 0, $fontfile, $text);
    // convert angle to radians
    $a = deg2rad($angle);
    // compute some usefull values
    $ca = cos($a);
    $sa = sin($a);
    $ret = array();
    // perform transformations
    for($i = 0; $i < 7; $i += 2){
        $ret[$i] = round($coords[$i] * $ca + $coords[$i+1] * $sa);
        $ret[$i+1] = round($coords[$i+1] * $ca - $coords[$i] * $sa);
    }
    return $ret;
}

?>
