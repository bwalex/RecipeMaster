<?php

include('functions.php');

$output = array();
$output['error'] = 0;
$output['msg'] = '';
$output['errmsg'] = '';
$output['rowsaffected'] = 0;
$output['id'] = -1;
$output['type'] = '';
$output['errorCol'] = -1;
$output['errorRow'] = -1;

try {
    $recipe_id = $_REQUEST['recipe_id'];
    $edit_type = $_REQUEST['edit_type'];

    $recipe = new Recipe($recipe_id);

    if ($edit_type == 'edit_name') {
        $recipe_name = $_REQUEST['edit_val'];
        $recipe->setName($recipe_name);
    } else if ($edit_type == 'edit_preparation') {
        $recipe_preparation = $_REQUEST['edit_val'];
        $recipe->instructions = $recipe_preparation;
    }  else if ($edit_type == 'edit_serves') {
        $recipe_serves = $_REQUEST['edit_val'];
        $recipe->setServes($recipe_serves);
    } else if ($edit_type == 'edit_ingredients') {
        $recipe->clearIngredients();
	if (!empty($_REQUEST['ing_name'])) {
	    foreach ($_REQUEST['ing_name'] as $key => $ingredient_name) {
		    if (empty($ingredient_name))
			continue;
                    $output['errorRow'] = $key;
		    $ingredient_qty = $_REQUEST['ing_qty'][$key];
		    $ingredient_unit = $_REQUEST['ing_unit'][$key];
		    $method = $_REQUEST['ing_method'][$key];
		    $output['errorCol'] = 0;
		    $elem = $recipe->addIngredient($ingredient_qty, $ingredient_unit,
			$method, -1, $ingredient_name, 0 /* don't validate units */);

                    /* validate units, etc */
                    $output['errorCol'] = 1;
		    if ((!is_numeric($ingredient_qty)) || ($ingredient_qty<= 0)) {
			throw new Exception('"qty" needs to be a positive number');
		    }
                    $elem['Ingredient']->getNutriInfo($elem['qty'], $elem['unit']);
	    }
	    /* XXX: maybe throw error? */
	}
    }

    $output['rowsaffected'] = $recipe->save(1);
} catch (Exception $e) {
    $output['errmsg'] = $e->getMessage();
    $output['error'] = 1;
}

header('Content-type: application/json');

echo json_encode($output);

?>

