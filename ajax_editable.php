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
    $edit_type = $_REQUEST['edit_type'];
    if ($_REQUEST['type'] == 'recipe') {
        $recipe_id = $_REQUEST['id'];
    
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
                $output['errorRow'] = -1;
                /* XXX: maybe throw error? */
            }
        }
    
        $output['rowsaffected'] = $recipe->save(1);
    } else if($_REQUEST['type'] == 'ingredient') {
        $ingredient_id = $_REQUEST['id'];
    
        $ingredient = new Ingredient($ingredient_id);

        if ($edit_type == 'edit_name') {
            $ingredient_name = $_REQUEST['edit_val'];
            $ingredient->setName($ingredient_name);
        } else if ($edit_type == 'edit_info') {
            $ingredient_info = $_REQUEST['edit_val'];
            $ingredient->info = $ingredient_info;
        } else if ($edit_type == 'edit_nutritional') {
            $output['errorRow'] = '';
            $fields = array("kcal", "carb", "fat", "protein", "fibre", "sodium", "cholesterol", "sugar", "sat_fat");
            foreach ($fields as $field) {
                $output['errorRow'] = $field;
                $val = $_REQUEST['field_'.$field];
                $ingredient->setNutri($field, $val);
            }
            $output['errorRow'] = '';
        } else if ($edit_type == 'edit_qtys') {
            $output['errorRow'] = 'qty';
            $qty = $_REQUEST['field_qty'];
            $unit = $_REQUEST['field_unit'];
            $typical_qty = $_REQUEST['field_typical_qty'];
            $typical_unit = $_REQUEST['field_typical_unit'];
            $ingredient->setQtyUnit($qty, $unit, $typical_qty, $typical_unit);
            $output['errorRow'] = '';
        } else if ($edit_type == 'edit_nutrients') {
            $ingredient->clearNutrients();
            if (!empty($_REQUEST['nut_name'])) {
                foreach ($_REQUEST['nut_name'] as $key => $nutrient_name) {
                        if (empty($nutrient_name))
                            continue;
                        $output['errorRow'] = $key;
                        $nutrient_qty = $_REQUEST['nut_qty'][$key];
                        $elem = $ingredient->addNutrient($nutrient_qty, '', -1, $nutrient_name);
                }
                $output['errorRow'] = -1;
                /* XXX: maybe throw error? */
            }
        }

        $output['rowsaffected'] = $ingredient->save(1);
    }
} catch (Exception $e) {
    $output['errmsg'] = $e->getMessage();
    $output['error'] = 1;
}

header('Content-type: application/json');

echo json_encode($output);

?>

