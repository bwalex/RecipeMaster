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
		$recipe_instructions = $_REQUEST['recipe_instructions'];
		$recipe_description = '';
		$recipe_time_estimate = 60;
                $recipe_serves = $_REQUEST['recipe_serves'];
		$ingredient_count = $_REQUEST['ingredient_count'];
                $output['type'] = $form_type;
                $output['whereOk'] = $_REQUEST['where_ok'];
                $output['whereError'] = $_REQUEST['where_error'];
		if (($form_type == "add_recipe") || ($form_type == "edit_recipe")) {
			$new = 1;
                } else {
			$new = 0;
                }

		$recipe = new Recipe($recipe_id, $recipe_name, $new,
		    $recipe_description, $recipe_instructions,
		    $recipe_time_estimate, $recipe_serves);
		
		if ($form_type == "add_recipe") {
			if (!empty($_REQUEST['ing_name'])) {
			    foreach ($_REQUEST['ing_name'] as $key => $ingredient_name) {
				    if (empty($ingredient_name))
					continue;
				    $ingredient_id = -1;
				    $ingredient_qty = $_REQUEST['ing_qty'][$key];
				    $ingredient_unit = $_REQUEST['ing_unit'][$key];
				    $method = $_REQUEST['ing_method'][$key];
				    
				    $elem = $recipe->addIngredient($ingredient_qty, $ingredient_unit,
					$method, $ingredient_id, $ingredient_name);
                                    $elem['Ingredient']->getNutriInfo($elem['qty'], $elem['unit']);
			    }
			    /* XXX: maybe throw error */
			}

			$n = $recipe->save();
                        $output['id'] = $recipe->id;
                        $output['rowsaffected'] = $n;

			if ($n > 0)
				print_msg('Successfully added recipe '.$recipe_name);
			print_msg("Rows affected: ".($n + $m));
		} else if ($form_type == "edit_recipe") {
			if (!empty($_REQUEST['ing_name'])) {
			    foreach ($_REQUEST['ing_name'] as $key => $ingredient_name) {
				    if (empty($ingredient_name))
					continue;
				    $ingredient_id = -1;
				    $ingredient_qty = $_REQUEST['ing_qty'][$key];
				    $ingredient_unit = $_REQUEST['ing_unit'][$key];
				    $method = $_REQUEST['ing_method'][$key];
				    
				    $elem = $recipe->addIngredient($ingredient_qty, $ingredient_unit,
					$method, $ingredient_id, $ingredient_name);
                                    $elem['Ingredient']->getNutriInfo($elem['qty'], $elem['unit']);
			    }
			    /* XXX: maybe throw error? */
			}
			$n = $recipe->save(/* update = */1);
                        $output['id'] = $recipe->id;
                        $output['rowsaffected'] = $n;

			if (!empty($_REQUEST['photo_id'])) {
			    foreach ($_REQUEST['photo_id'] as $key => $photo_id) {
				if ($photo_id >= 0) {
				    // Edit caption and keep
				    $photo = new Photo("recipe", $photo_id);
				    $photo->updateCaption($_REQUEST['photo_caption'][$key]);
				}
			    }
                        }

			if ($n > 0)
				print_msg('Successfully edited recipe '.$recipe_name);
			print_msg("Rows affected: ".$n);
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