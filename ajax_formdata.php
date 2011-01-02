<?php

include('functions.php');

if(isset($_REQUEST["ingredient"])) {
	$id = $_REQUEST["ingredient"];
	$output = '';
	try {
	    $ingredient = new Ingredient($id);
	    $output = $ingredient->getJSON();
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
	    $output = $recipe->getJSON();
	} catch (Exception $e) {
	    $output = '{ "exception" : "'.$e->getMessage().'" }';
	}
	header('Content-type: application/json');
	echo $output;
}
?>