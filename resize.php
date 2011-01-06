<?php

$width = $_REQUEST['width'];
$height = $_REQUEST['height'];
$path = $_REQUEST['photo'];

$info = getimagesize($path);
$width_orig = $info[0];
$height_orig = $info[1];
$ratio_orig = $width_orig/$height_orig;
if ($width/$height > $ratio_orig) {
	$width = $height*$ratio_orig;
} else {
	$height = $width/$ratio_orig;
}
$image_p = imagecreatetruecolor($width, $height);
switch($info['mime']) {
	case "image/gif":
		$image = imagecreatefromgif($path);
		break;
	case "image/jpeg":
		$image = imagecreatefromjpeg($path);
		break;
	case "image/png":
		$image = imagecreatefrompng($path);
		break;
	case "image/xbm":
		$image = imagecreatefromxbm($path);
		break;
	default:
		throw new Exception('Unknown mime type in Photo->store(), this should have never happened!');
}
imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
imagedestroy($image);

# Prints out all the figures and picture and frees memory 
header('Content-type: image/jpeg'); 

imagejpeg($image_p);
imagedestroy($image_p);

?>