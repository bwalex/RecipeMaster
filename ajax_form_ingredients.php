<?php


/*
$.ajax({
   type: "POST",
   url: "ajax_form_ingredients.php",
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

		$ingredient_unit = trim($_REQUEST['ingredient_unit']);
		if (ord($ingredient_unit) == 194)
		    $ingredient_unit = '';

		$ingredient_qty = $_REQUEST['ingredient_qty'];
		$ingredient_typical_unit = $_REQUEST['ingredient_typical_unit'];
		$ingredient_typical_qty = $_REQUEST['ingredient_typical_qty'];
		$ingredient_kcal = $_REQUEST['ingredient_kcal'];
		$ingredient_carb = $_REQUEST['ingredient_carb'];
		$ingredient_sugar = $_REQUEST['ingredient_sugar'];
		$ingredient_fibre = $_REQUEST['ingredient_fibre'];
		$ingredient_protein = $_REQUEST['ingredient_protein'];
		$ingredient_fat = $_REQUEST['ingredient_fat'];
		$ingredient_sat_fat = $_REQUEST['ingredient_sat_fat'];
		$ingredient_sodium = $_REQUEST['ingredient_sodium'];
		$ingredient_cholesterol = $_REQUEST['ingredient_cholesterol'];
		$ingredient_others = $_REQUEST['ingredient_others'];
		if (($form_type == "add_ingredient") || ($form_type == "edit_ingredient")) {
			$new = 1;
			$output['where'] = 'dialog-messages';
		} else {
			$new = 0;
			$output['where'] = 'global-messages';
		}

		$ingredient = new Ingredient($ingredient_id, $ingredient_name, $new,
		    $ingredient_unit, $ingredient_qty,
		    $ingredient_typical_unit, $ingredient_typical_qty, $ingredient_kcal,
		    $ingredient_carb, $ingredient_sugar, $ingredient_fibre,
		    $ingredient_protein, $ingredient_fat, $ingredient_sat_fat,
		    $ingredient_sodium, $ingredient_cholesterol,
		    $ingredient_others);

		if ($form_type == "add_ingredient") {
			$n = $ingredient->save();
			
			if ($n > 0)
				print_msg('Successfully added ingredient '.$ingredient_name);
			print_msg("Rows affected: ".$n);
		}
		else if ($form_type == "edit_ingredient") {
			$n = $ingredient->save(1);

			if ($n > 0)
				print_msg('Successfully edited ingredient '.$ingredient_name);
			print_msg("Rows affected: ".$n);
		}
		else if ($form_type == "delete_ingredient") {
			$n = $ingredient->delete();
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