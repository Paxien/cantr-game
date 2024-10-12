<?php

$width = intval($_REQUEST['width']);
$height = intval($_REQUEST['height']);
$proportion = $_REQUEST['proportion'];

$width = $width ? $width : 200;
$height = $height ? $height : 20;
$proportion = $proportion ? $proportion : 0;


$output = imagecreate ( $width, $height );
    
$color_background = imagecolorallocate ($output, 0,102,0);
$color_border = imagecolorallocate ($output, 0,0,0);
$color_filling = imagecolorallocate ($output, 255,0,0);

imagefilledrectangle ($output, 0, 0, $width, $height, $color_background);
imagerectangle ($output, 0, 0, $width, $height, $color_border);
imagerectangle ($output, 1, 1, $width - 1, $height - 1, $color_border);
imagefilledrectangle ($output, 2, 2, ($width - 4) * $proportion + 2, $height - 2, $color_filling);

imagepng ($output);
