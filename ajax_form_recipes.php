<?php

/*
$.ajax({
   type: "POST",
   url: "ajax_form_recipes.php",
   dataType: "json",
   data: {
       type: "ingredients",
       maxRows: 12,
       term: request.term
   },
   success: function(data) {
       if (data.exception) {
           response([]);
           alert(data.exception);
           return;
       }

       response(data.objects);
   }
});
*/


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
		$ingredient_count = $_REQUEST['ingredient_count'];
                $output['type'] = $form_type;
		if (($form_type == "add_recipe") || ($form_type == "edit_recipe")) {
			$new = 1;
                        $output['where'] = 'dialog-messages';
                } else {
			$new = 0;
                        $output['where'] = 'global-messages';
                }

		$recipe = new Recipe($recipe_id, $recipe_name, $new,
		    $recipe_description, $recipe_instructions,
		    $recipe_time_estimate);
		
		if ($form_type == "add_recipe") {
			if (!empty($_REQUEST['ing_name'])) {
			    foreach ($_REQUEST['ing_name'] as $key => $ingredient_name) {
				    if (empty($ingredient_name))
					continue;
				    $ingredient_id = -1;
				    $ingredient_qty = $_REQUEST['ing_qty'][$key];
				    $ingredient_unit = $_REQUEST['ing_unit'][$key];
				    $method = $_REQUEST['ing_method'][$key];
				    
				    $recipe->addIngredient($ingredient_qty, $ingredient_unit,
					$method, $ingredient_id, $ingredient_name);
			    }
			    /* XXX: maybe throw error */
			}

			$n = $recipe->save();
                        $output['id'] = $recipe->id;
                        $output['rowsaffected'] = $n;
/*
			if (!empty($_FILES['recipe_photo']['tmp_name'])) {
			    foreach ($_FILES['recipe_photo']['tmp_name'] as $key => $file) {
				$photo = new Photo("recipe", -1, $recipe->id, $_REQUEST['photo_caption'][$key], $file);
				$m = $photo->store();
				if ($m == 0) {
				    $types = '';
				    foreach($photo->mime_types as $mime) {
					$types .= $mime . ', ';
				    }
				    $types = substr_replace( $types, "", -2 );
				    print_error("Error processing image '".$_FILES['recipe_photo']['name'][$key]."', supported image types are: ".$types);
				}
			    }
			}
*/
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
				    
				    $recipe->addIngredient($ingredient_qty, $ingredient_unit,
					$method, $ingredient_id, $ingredient_name);
			    }
			    /* XXX: maybe throw error? */
			}
			$n = $recipe->save(/* update = */1);
                        $output['id'] = $recipe->id;
                        $output['rowsaffected'] = $n;
/*
			$keep = array();
			if (!empty($_REQUEST['photo_id'])) {
			    foreach ($_REQUEST['photo_id'] as $key => $photo_id) {
				if ($photo_id >= 0) {
				    // Edit caption and keep
				    $photo = new Photo("recipe", $photo_id);
				    $photo->updateCaption($_REQUEST['photo_caption'][$key]);
				    array_push($keep, $photo_id);
				}
			    }
			    delete_photos("recipe", $recipe->id, $keep);

			    // No, we don't do this in the same loop as to avoid deleting the new photos whose ids we don't have at that point
			    $file_no = 0;
			    foreach ($_REQUEST['photo_id'] as $key => $photo_id) {
				if ($photo_id == -1) {
				    // new photo
				    $photo = new Photo("recipe", -1, $recipe->id, $_REQUEST['photo_caption'][$key], $_FILES['recipe_photo']['tmp_name'][$file_no++]);
				    $m = $photo->store();
				    if ($m == 0) {
					$types = '';
					foreach($photo->mime_types as $mime) {
					    $types .= $mime . ', ';
					}
					$types = substr_replace( $types, "", -2 );
					print_error("Error processing image '".$_FILES['recipe_photo']['name'][$file_no-1]."', supported image types are: ".$types);
				    }
				}
			    }
			}
*/

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