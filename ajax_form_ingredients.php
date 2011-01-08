<?php

include('functions.php');

$output = array();
$output['error'] = 0;
$output['msg'] = array();
$output['errmsg'] = array();
$output['rowsaffected'] = 0;

function print_error($msg) {
	global $output;
	array_push($output['errmsg'], $msg);
}

function print_msg($msg) {
	global $output;
	array_push($output['msg'], $msg);
}

if ($_REQUEST['ingredient_id']) {
	try {
		$form_type = $_REQUEST['form_type'];
		$ingredient_name = $_REQUEST['ingredient_name'];
		$ingredient_id = $_REQUEST['ingredient_id'];
                $output['whereOk'] = $_REQUEST['where_ok'];
                $output['whereError'] = $_REQUEST['where_error'];
		$output['type'] = $form_type;

		if ($form_type == "add_ingredient") {
			$new = 1;
		} else {
			$new = 0;
		}

		$ingredient = new Ingredient($ingredient_id, $ingredient_name, $new);

		if ($form_type == "add_ingredient") {
			$n = $ingredient->save();
			$output['rowsaffected'] = $n;
			$output['id'] = $ingredient->id;
			
			if ($n > 0)
				print_msg('Successfully added ingredient '.$ingredient_name);
			print_msg("Rows affected: ".$n);
		}
		else if ($form_type == "delete_ingredient") {
			$n = $ingredient->delete();
			$output['rowsaffected'] = $n;
			if ($n > 0)
				print_msg('Successfully deleted ingredient '.$ingredient_name);
			print_msg("Rows affected: ".$n);
		}
	
	} catch (Exception $e) {
		$code = $e->getCode();
		$msg = $e->getMessage();
		if ((($code == "HY000") || ($code == "23000")) && (stripos($msg, 'foreign'))) {
		    print_error('Cannot delete this ingredient, at least one recipe still depends on it.');
		} else {
		    print_error('Exception: '.$msg);
		}
		$output['error'] = 1;
	}
}

header('Content-type: application/json');

echo json_encode($output);

?>