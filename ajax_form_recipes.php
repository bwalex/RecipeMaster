<?php

include('functions.php');

$output = array();
$output['error'] = 0;
$output['msg'] = array();
$output['errmsg'] = array();
$output['rowsaffected'] = 0;
$output['id'] = -1;
$output['type'] = '';

function print_error($msg) {
	global $output;
	array_push($output['errmsg'], $msg);
}

function print_msg($msg) {
	global $output;
	array_push($output['msg'], $msg);
}


if ($_REQUEST['recipe_id']) {
	try {
		$form_type = $_REQUEST['form_type'];
		$recipe_name = $_REQUEST['recipe_name'];
		$recipe_id = $_REQUEST['recipe_id'];
                $output['type'] = $form_type;
                $output['whereOk'] = $_REQUEST['where_ok'];
                $output['whereError'] = $_REQUEST['where_error'];
		if ($form_type == "add_recipe") {
			$new = 1;
                } else {
			$new = 0;
                }

		$recipe = new Recipe($recipe_id, $recipe_name, $new);

                if ($form_type == "copy_recipe") {
                    $recipe->id = -1;
                    $recipe->setName($recipe->name.' (copy)');
                    $n = $recipe->save();
                    $output['id'] = $recipe->id;
                    $output['rowsaffected'] = $n;

                    if ($n > 0)
			print_msg('Successfully copied recipe '.$recipe_name);
		    print_msg("Rows affected: ".($n + $m));
                } else if ($form_type == "add_recipe") {
                    $n = $recipe->save();
                    $output['id'] = $recipe->id;
                    $output['rowsaffected'] = $n;
                    
                    if ($n > 0)
                        print_msg('Successfully added recipe '.$recipe_name);
                    print_msg("Rows affected: ".($n + $m));
		} else if ($form_type == "delete_recipe") {
			$n = $recipe->delete();
			if ($n > 0)
				print_msg('Successfully deleted recipe '.$recipe_name);
			print_msg("Rows affected: ".$n);
		}
	} catch (Exception $e) {
		print_error('Exception: '.$e->getMessage());
                $output['error'] = 1;
	}
}

header('Content-type: application/json');

echo json_encode($output);

?>