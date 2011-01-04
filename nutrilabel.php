<?php

$carbs = round($_GET['carb'], 1);
$protein = $_GET['protein'];
$fat = $_GET['fat'];
$sat_fat = $_GET['sat_fat'];
$calories = intval($_GET['kcal']);
$cholesterol = intval($_GET['cholesterol']);
$sodium = intval($_GET['sodium']);
$fibre = $_GET['fibre'];
$sugars = $_GET['sugar'];

/* nutrilabel.php?carbs=74&protein=45&fat=27&sat_fat=6&calories=720&cholesterol=90&sodium=1790&fibre=5&sugars=16 */


$carb_pct = intval(100*$carbs*4/($carbs*4 + $protein*4 + $fat*9));
$protein_pct = intval(100*$protein*4/($carbs*4 + $protein*4 + $fat*9));
$fat_pct = intval(100*$fat*9/($carbs*4 + $protein*4 + $fat*9));
$fat_cal = intval($fat*9);

/* https://www.purelifestyle.co.uk/Article.aspx?Id=37 */
/* http://www.food.gov.uk/multimedia/pdfs/nutrientinstitution.pdf */
/* http://www.food.gov.uk/multimedia/pdfs/nutguideuk.pdf */
/* Jamie Oliver Magazine, Guideline Daily Amounts for UK only */
$fat_rdi = 70;
$sat_fat_rdi = 20;
$cholesterol_rdi = 300;
$sodium_rdi = 2400;
$carbs_rdi = 230;
$sugars_rdi = 90;
$fibre_rdi = 24;
$protein_rdi = 45;

$fat_rdi_pct = intval($fat/$fat_rdi*100);
$sat_fat_rdi_pct = intval($sat_fat/$sat_fat_rdi*100);
$cholesterol_rdi_pct = intval($cholesterol/$cholesterol_rdi*100);
$sodium_rdi_pct = intval($sodium/$sodium_rdi*100);
$carbs_rdi_pct = intval($carbs/$carbs_rdi*100);
$sugars_rdi_pct = intval($sugars/$sugars_rdi*100);
$fibre_rdi_pct = intval($fibre/$fibre_rdi*100);
$protein_rdi_pct = intval($protein/$protein_rdi*100);

function bbox_get_height($bbox) {
	return -($bbox[7] + $bbox[1]);
}

function bbox_get_width($bbox) {
	return ($bbox[2] - $bbox[0]);
}

$width = 260;
$height = 520;

// create a image
$im = imagecreate($width, $height);

$background_color = imagecolorallocate ($im, 255, 255, 255); //white background
$text_color = imagecolorallocate ($im, 0, 0,0);//black text
$trans_color = $background_color;//transparent colour

/* Draw border */
imagerectangle($im, 0, 0, $width-1, $height-1, $text_color);

//imagestring ($im, 10, 6, 5, 'Nutrition Facts', $text_color);
//imagettftext ($image , $size ,$angle , int $x , int $y , int $color , string $fontfile , string $text )

$font_size_big = 20;
$font_size_normal = 9;
$side_margin = 6;
$file_font_bold = "DroidSans-Bold.ttf";
$file_font_regular = "DroidSans.ttf";

$fontpath = realpath('./');
putenv('GDFONTPATH='.$fontpath);


$bbox = imagettfbbox($font_size_big, 0, $file_font_bold, 'Nutrition Facts');
$y = bbox_get_height($bbox) + 5;
$x = intval(($width - bbox_get_width($bbox))/2);
imagettftext($im, $font_size_big, 0, $x, $y, $text_color, $file_font_bold, 'Nutrition Facts');

$y += 10;
imagefilledrectangle($im, $side_margin, $y, $width-$side_margin, $y+8, $text_color);
$y += 12;

$bbox = imagettfbbox($font_size_normal, 0, $file_font_bold, 'Amount Per Serving');
$y += bbox_get_height($bbox);
$normal_height = bbox_get_height($bbox);
imagettftext($im, $font_size_normal, 0, $side_margin, $y, $text_color, $file_font_bold, 'Amount Per Serving');

$y += 4;
imageline($im, $side_margin, $y, $width-$side_margin, $y, $text_color);
$y += 5 + $normal_height;

$bbox = imagettfbbox($font_size_normal, 0, $file_font_bold, 'Calories ');
imagettftext($im, $font_size_normal, 0, $side_margin, $y, $text_color, $file_font_bold, 'Calories');
imagettftext($im, $font_size_normal, 0, $side_margin+bbox_get_width($bbox), $y, $text_color, $file_font_regular, $calories);

$bbox = imagettfbbox($font_size_normal, 0, $file_font_regular, 'Calories from Fat: '.$fat_cal);
imagettftext($im, $font_size_normal, 0, $width-6-bbox_get_width($bbox), $y, $text_color, $file_font_regular, 'Calories from Fat: '.$fat_cal);
$y += 4;
imagefilledrectangle($im, $side_margin, $y, $width-$side_margin, $y+2, $text_color);
$y += 6 + $normal_height;

$bbox = imagettfbbox($font_size_normal, 0, $file_font_bold, '% Daily Value*');
imagettftext($im, $font_size_normal, 0, $width-6-bbox_get_width($bbox), $y, $text_color, $file_font_bold, '% Daily Value*');

$y += 4;
imageline($im, $side_margin, $y, $width-$side_margin, $y, $text_color);
$y += 5 + $normal_height;

$bbox = imagettfbbox($font_size_normal, 0, $file_font_bold, 'Total Fat ');
imagettftext($im, $font_size_normal, 0, $side_margin, $y, $text_color, $file_font_bold, 'Total Fat');
imagettftext($im, $font_size_normal, 0, $side_margin+bbox_get_width($bbox), $y, $text_color, $file_font_regular, $fat.'g');

$bbox = imagettfbbox($font_size_normal, 0, $file_font_regular, $fat_rdi_pct.'%');
imagettftext($im, $font_size_normal, 0, $width-6-bbox_get_width($bbox), $y, $text_color, $file_font_regular, $fat_rdi_pct.'%');


$y += 4;
imageline($im, $side_margin, $y, $width-$side_margin, $y, $text_color);
$y += 5 + $normal_height;

imagettftext($im, $font_size_normal, 0, $side_margin, $y, $text_color, $file_font_regular, '     Saturated Fat '.$sat_fat.'g');

$bbox = imagettfbbox($font_size_normal, 0, $file_font_regular, $sat_fat_rdi_pct.'%');
imagettftext($im, $font_size_normal, 0, $width-6-bbox_get_width($bbox), $y, $text_color, $file_font_regular, $sat_fat_rdi_pct.'%');

$y += 4;
imageline($im, $side_margin, $y, $width-$side_margin, $y, $text_color);
$y += 5 + $normal_height;

$bbox = imagettfbbox($font_size_normal, 0, $file_font_bold, 'Cholesterol ');
imagettftext($im, $font_size_normal, 0, $side_margin, $y, $text_color, $file_font_bold, 'Cholesterol');
imagettftext($im, $font_size_normal, 0, $side_margin+bbox_get_width($bbox), $y, $text_color, $file_font_regular, $cholesterol.'mg');

$bbox = imagettfbbox($font_size_normal, 0, $file_font_regular, $cholesterol_rdi_pct.'%');
imagettftext($im, $font_size_normal, 0, $width-6-bbox_get_width($bbox), $y, $text_color, $file_font_regular, $cholesterol_rdi_pct.'%');

$y += 4;
imageline($im, $side_margin, $y, $width-$side_margin, $y, $text_color);
$y += 5 + $normal_height;

$bbox = imagettfbbox($font_size_normal, 0, $file_font_bold, 'Sodium ');
imagettftext($im, $font_size_normal, 0, $side_margin, $y, $text_color, $file_font_bold, 'Sodium');
imagettftext($im, $font_size_normal, 0, $side_margin+bbox_get_width($bbox), $y, $text_color, $file_font_regular, $sodium.'mg');

$bbox = imagettfbbox($font_size_normal, 0, $file_font_regular, $sodium_rdi_pct.'%');
imagettftext($im, $font_size_normal, 0, $width-6-bbox_get_width($bbox), $y, $text_color, $file_font_regular, $sodium_rdi_pct.'%');

$y += 4;
imageline($im, $side_margin, $y, $width-$side_margin, $y, $text_color);
$y += 5 + $normal_height;

$bbox = imagettfbbox($font_size_normal, 0, $file_font_bold, 'Total Carbohydrate ');
imagettftext($im, $font_size_normal, 0, $side_margin, $y, $text_color, $file_font_bold, 'Total Carbohydrate');
imagettftext($im, $font_size_normal, 0, $side_margin+bbox_get_width($bbox), $y, $text_color, $file_font_regular, $carbs.'g');

$bbox = imagettfbbox($font_size_normal, 0, $file_font_regular, $carbs_rdi_pct.'%');
imagettftext($im, $font_size_normal, 0, $width-6-bbox_get_width($bbox), $y, $text_color, $file_font_regular, $carbs_rdi_pct.'%');

$y += 4;
imageline($im, $side_margin, $y, $width-$side_margin, $y, $text_color);
$y += 5 + $normal_height;

imagettftext($im, $font_size_normal, 0, $side_margin, $y, $text_color, $file_font_regular, '     Dietary Fibre '.$fibre.'g');

$bbox = imagettfbbox($font_size_normal, 0, $file_font_regular, $fibre_rdi_pct.'%');
imagettftext($im, $font_size_normal, 0, $width-6-bbox_get_width($bbox), $y, $text_color, $file_font_regular, $fibre_rdi_pct.'%');

$y += 4;
imageline($im, $side_margin, $y, $width-$side_margin, $y, $text_color);
$y += 5 + $normal_height;

imagettftext($im, $font_size_normal, 0, $side_margin, $y, $text_color, $file_font_regular, '     Sugars '.$sugars.'g');

$bbox = imagettfbbox($font_size_normal, 0, $file_font_regular, $sugars_rdi_pct.'%');
imagettftext($im, $font_size_normal, 0, $width-6-bbox_get_width($bbox), $y, $text_color, $file_font_regular, $sugars_rdi_pct.'%');

$y += 4;
imageline($im, $side_margin, $y, $width-$side_margin, $y, $text_color);
$y += 5 + $normal_height;

$bbox = imagettfbbox($font_size_normal, 0, $file_font_bold, 'Protein ');
imagettftext($im, $font_size_normal, 0, $side_margin, $y, $text_color, $file_font_bold, 'Protein');
imagettftext($im, $font_size_normal, 0, $side_margin+bbox_get_width($bbox), $y, $text_color, $file_font_regular, $protein.'g');

$bbox = imagettfbbox($font_size_normal, 0, $file_font_regular, $protein_rdi_pct.'%');
imagettftext($im, $font_size_normal, 0, $width-6-bbox_get_width($bbox), $y, $text_color, $file_font_regular, $protein_rdi_pct.'%');

$y += 4;
imageline($im, $side_margin, $y, $width-$side_margin, $y, $text_color);
imagefilledrectangle($im, $side_margin, $y, $width-$side_margin, $y+8, $text_color);
$y += 12 + $normal_height;
imagettftext($im, $font_size_normal, 0, $side_margin, $y, $text_color, $file_font_regular, '* Based on a 2000 calorie diet');
$y += 20;

$lower_y = $y + intval(($width-60)*1.73/2);
$triangle_height = $lower_y - $y;
$mini_triangle_height = $triangle_height/5;
$mini_triangle_width = ($width-60)/5;
$x_movement = $mini_triangle_width/2;

$red = 255;
$color_step_basic = 255/5;
$color_step_mini = 255/8;

$n = 1;
$y_initial = $y;
$x_start = intval($width/2);
for ($i = 0; $i < 5; $i++) {
	$x = $x_start;
	$color_max = ($n-1) * $color_step_basic;
	if ($n*$color_step_basic >= 255) {
		$color_step = $color_step_mini;
	} else {
		$color_step = $color_step_basic;
	}
	if ($i == 0) {
		$red = 255;
		$green = $color_step;
		$blue = $color_step;
	} else if ($i == 4) {
		$red -= (255/5);
		$green = 255;
		$blue = 0;
	} else {
		$red -= (255/5);
		$green = (($n-1)*$color_step)+$color_max/2;
		$green = ($n-1)*$color_step;
		$blue = (($n-1)*$color_step)-$color_max/2;
		$blue = 255-($n+1)*$color_step+1;
	}
	$actual_color_step = $color_max/$n;
	for ($j = 0; $j < $n; $j++) {
		$points = array();
		if ($j%2 == 0) {
			$points = array(
				$x, $y,
				$x-$x_movement, $y+$mini_triangle_height,
				$x+$x_movement, $y+$mini_triangle_height
			);
		} else {
			$points = array(
				intval($x), $y+$mini_triangle_height,
				$x-$x_movement, $y,
				$x+$x_movement, $y
			);
		}
		$color = imagecolorallocate($im, $red, $green, $blue);
		imagefilledpolygon($im, $points, 3, $color);
		$x += $x_movement;
		$green -= $color_step;
		$blue += $color_step;
	}
	$y += $mini_triangle_height;
	$x_start -= $x_movement;
	$n += 2;
}


$points = array(
	intval($width/2), $y_initial,
	30, $lower_y, 
	$width-30, $lower_y
	);

imagepolygon($im, $points, 3, $text_color);

$big_triangle_height = $lower_y - $y_initial;

$y_pos = $lower_y - $big_triangle_height/100 * $fat_pct;

$x_range = tan(0.52359)*($y_pos - $y_initial)*2;
$x = $width/2 + $x_range/2*(($protein_pct - $carb_pct)/($protein_pct + $carb_pct));


$color = imagecolorallocate($im, 255, 255, 255);
imagerectangle($im, $x-10, $y_pos-10, $x+10, $y_pos+10, $color);
imagefilledrectangle($im, $x-1, $y_pos-1, $x+1, $y_pos+1, $color);


$y += 10;
$side_margin = 6;

$actual_width = $width-$side_margin*2;
$third_width = $actual_width/3;
$rectangle_height = $normal_height*2 + 20;
imagerectangle($im, $side_margin, $y, $side_margin+$third_width, $y+$rectangle_height, $text_color);
imagerectangle($im, $side_margin+$third_width, $y, $side_margin+2*$third_width, $y+$rectangle_height, $text_color);
imagerectangle($im, $side_margin+2*$third_width, $y, $side_margin+3*$third_width, $y+$rectangle_height, $text_color);
$color = imagecolorallocate($im, 51, 255, 0);
imagefilledrectangle($im, $side_margin+1, $y+1, $side_margin+$third_width-1, $y+$rectangle_height/2, $color);
$color = imagecolorallocate($im, 255, 51, 51);
imagefilledrectangle($im, $side_margin+$third_width+1, $y+1, $side_margin+2*$third_width-1, $y+$rectangle_height/2, $color);
$color = imagecolorallocate($im, 51, 0, 255);
imagefilledrectangle($im, $side_margin+2*$third_width+1, $y+1, $side_margin+3*$third_width-1, $y+$rectangle_height/2, $color);

$line_y = $y + $rectangle_height/2;
$y += $normal_height+5;

$color = imagecolorallocate($im, 255, 255, 255);

// Fat
$bbox = imagettfbbox($font_size_normal, 0, $file_font_bold, 'Carbs');
$x = $side_margin+($third_width-bbox_get_width($bbox))/2;
imagettftext($im, $font_size_normal, 0, $x, $y, $color, $file_font_bold, 'Carbs');

// Carbs
$bbox = imagettfbbox($font_size_normal, 0, $file_font_bold, 'Fat');
$x = $side_margin+$third_width+($third_width-bbox_get_width($bbox))/2;
imagettftext($im, $font_size_normal, 0, $x, $y, $color, $file_font_bold, 'Fat');

// Protein
$bbox = imagettfbbox($font_size_normal, 0, $file_font_bold, 'Protein');
$x = $side_margin+$third_width*2+($third_width-bbox_get_width($bbox))/2;
imagettftext($im, $font_size_normal, 0, $x, $y, $color, $file_font_bold, 'Protein');

$y = $line_y;
imageline($im, $side_margin, $y, $width-$side_margin, $y, $text_color);

$y += $normal_height+5;

// Carbs
$bbox = imagettfbbox($font_size_normal, 0, $file_font_regular, $carb_pct.'%');
$x = $side_margin+($third_width-bbox_get_width($bbox))/2;
imagettftext($im, $font_size_normal, 0, $x, $y, $text_color, $file_font_regular, $carb_pct.'%');

// Fat
$bbox = imagettfbbox($font_size_normal, 0, $file_font_regular, $fat_pct.'%');
$x = $side_margin+$third_width+($third_width-bbox_get_width($bbox))/2;
imagettftext($im, $font_size_normal, 0, $x, $y, $text_color, $file_font_regular, $fat_pct.'%');

// Protein
$bbox = imagettfbbox($font_size_normal, 0, $file_font_regular, $protein_pct.'%');
$x = $side_margin+$third_width*2+($third_width-bbox_get_width($bbox))/2;
imagettftext($im, $font_size_normal, 0, $x, $y, $text_color, $file_font_regular, $protein_pct.'%');

# Prints out all the figures and picture and frees memory 
header('Content-type: image/png'); 



//imagettfbbox — Give the bounding box of a text using TrueType fonts


imagepng($im);
imagedestroy($im);

?>