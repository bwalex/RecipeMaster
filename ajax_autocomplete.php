<?php

include('functions.php');

if(isset($_REQUEST["type"]) && isset($_REQUEST["term"])) {
	$type = $_REQUEST["type"];
	$term = $_REQUEST["term"];
	$maxRows = $_REQUEST["maxRows"];

	$output = '';

	$query = "WHERE name LIKE :filter_name ";
	if (isset($_REQUEST["maxRows"]))
		$query .= " LIMIT ".intval($maxRows);
	$tokens = array(':filter_name' => '%'.$term.'%');

	try {
		if ($type == "ingredients")
			$objs = get_all_ingredients($query, $tokens);
		else if ($type == "recipes")
			$objs = get_all_recipes($query, $tokens);
		else {
			$objs = array();
		}

		$output .= '{';
		$output .= '"objects" : [ ';
		foreach($objs as $obj) {
			$output .= '"'.str_replace('"', '\"', $obj->name).'",';
		}
		/* Remove last comma */
		$output = substr_replace($output, "", -1);
		$output .= ' ]';
		$output .= '}';
	} catch (Exception $e) {
		$output = '{ "exception" : "'.$e->getMessage().'" }';
	}
	header('Content-type: application/json');
	echo $output;
}
?>