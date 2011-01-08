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
		else if ($type == "nutrients")
			$objs = get_all_nutrients($query, $tokens);
		else {
			$objs = array();
		}

		if ($type == "nutrients") {
			$arr = array();
			foreach ($objs as $nut) {
				$m['label'] = $nut->name;
				$m['value'] = $m['label'].' ('.$nut->unit.')';
				array_push($arr, $m);
			}
			$out['objects'] = $arr;
			$output = json_encode($out);
		} else {
			$output .= '{';
			$output .= '"objects" : [ ';
			foreach($objs as $obj) {
				$output .= '"'.str_replace('"', '\"', $obj->name).'",';
			}
			/* Remove last comma */
			$output = substr_replace($output, "", -1);
			$output .= ' ]';
			$output .= '}';
		}
	} catch (Exception $e) {
		$output = '{ "exception" : "'.$e->getMessage().'" }';
	}
	header('Content-type: application/json');
	echo $output;
}
?>