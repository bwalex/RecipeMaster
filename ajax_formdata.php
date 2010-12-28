<?php

include('functions.php');

if(isset($_REQUEST["ingredient"])) {
	$id = $_REQUEST["ingredient"];
	$output = '';
	try {
	    $ingredient = new Ingredient($id);
	    $output .= '{';
	    $output .= '"id" : "'.str_replace('"', '\"', $ingredient->id).'" ,';
	    $output .= '"name" : "'.str_replace('"', '\"', $ingredient->name).'" ,';
	    $output .= '"qty" : "'.str_replace('"', '\"', $ingredient->qty).'" ,';
	    $output .= '"unit" : "'.str_replace('"', '\"', $ingredient->unit).'" ,';
	    $output .= '"kcal" : "'.str_replace('"', '\"', $ingredient->kcal).'" ,';
	    $output .= '"carb" : "'.str_replace('"', '\"', $ingredient->carb).'" ,';
	    $output .= '"sugar" : "'.str_replace('"', '\"', $ingredient->sugar).'" ,';
	    $output .= '"fibre" : "'.str_replace('"', '\"', $ingredient->fibre).'" ,';
	    $output .= '"protein" : "'.str_replace('"', '\"', $ingredient->protein).'" ,';
	    $output .= '"fat" : "'.str_replace('"', '\"', $ingredient->fat).'" ,';
	    $output .= '"sat_fat" : "'.str_replace('"', '\"', $ingredient->sat_fat).'" ,';
	    $output .= '"sodium" : "'.str_replace('"', '\"', $ingredient->sodium).'" ,';
	    $output .= '"cholesterol" : "'.str_replace('"', '\"', $ingredient->cholesterol).'" ,';
	    $output .= '"others" : "'.str_replace('"', '\"', $ingredient->others).'"';
	    $output .= '}';
	} catch (Exception $e) {
	    $output = '{ "exception" : "'.$e->getMessage().'" }';
	}
	header('Content-type: application/json');
	echo $output;
} else if(isset($_REQUEST["recipe"])) {
	$id = $_REQUEST["recipe"];
	$output = '';

	try {
	    $recipe = new Recipe($id);
	    $output .= '{';
	    $output .= '"id" : "'.str_replace('"', '\"', $recipe->id).'" ,';
	    $output .= '"name" : "'.str_replace('"', '\"', $recipe->name).'" ,';
	    $output .= '"time" : "'.str_replace('"', '\"', $recipe->time_estimate).'" ,';
	    $output .= '"description" : "'.rawurlencode($recipe->description).'" ,';
	    $output .= '"instructions" : "'.rawurlencode($recipe->instructions).'" ,';
	    $output .= '"ingredients" : [';
	    foreach ($recipe->ingredients as $ingredient) {
		$output .= '{';
		$output .= '"qty" : "'.str_replace('"', '\"', $ingredient['qty']).'",';
		$output .= '"unit" : "'.str_replace('"', '\"', $ingredient['unit']).'",';
		$output .= '"name" : "'.str_replace('"', '\"', $ingredient['Ingredient']->name).'",';
		$output .= '"method" : "'.str_replace('"', '\"', $ingredient['method']).'"';
		$output .= '},';
	    }
	    /* Remove last comma */
	    $output = substr_replace($output, "", -1);
	    $output .= ']';
	    $output .= '}';
	} catch (Exception $e) {
	    $output = '{ "exception" : "'.$e->getMessage().'" }';
	}
	header('Content-type: application/json');
	echo $output;
}
?>