<?php

$string = $_GET['string'] ? strtolower($_GET['string']) : "";
$hash = md5($string);
$color = substr($hash, 2, 6);
$pixels = array();

for ($i = 0; $i < 5; $i++) {
    for ($j = 0; $j < 5; $j++) {
        $pixels[$i][$j] = hexdec(substr($hash, ($i * 5) + $j + 6, 1))%2 === 0;
    }
}

$image = imagecreatetruecolor(400, 400);
$color = imagecolorallocate($image, hexdec(substr($color,0,2)), hexdec(substr($color,2,2)), hexdec(substr($color,4,2)));
$bg = imagecolorallocate($image, 238, 238, 238);

for ($k = 0; $k < count($pixels); $k++) {
    for ($l = 0; $l < count($pixels[$k]); $l++) {
        $pixelColor = $bg;

        if ($pixels[$k][$l]) {
            $pixelColor = $color;
        }
        imagefilledrectangle($image, $k * 80, $l * 80, ($k + 1) * 80, ($l + 1) * 80, $pixelColor);
    }
}

header('Content-type: image/png');
imagepng($image);

?>