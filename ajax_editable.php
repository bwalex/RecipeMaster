<?php

include('functions.php');

$output = array();
$output['error'] = 0;
$output['msg'] = '';
$output['errmsg'] = '';
$output['rowsaffected'] = 0;
$output['id'] = -1;
$output['type'] = '';

try {
    $recipe_id = $_REQUEST['recipe_id'];
    $edit_type = $_REQUEST['edit_type'];

    $recipe = new Recipe($recipe_id);

    if ($edit_type == 'edit_name') {
        $recipe_name = $_REQUEST['edit_val'];
        $recipe->setName($recipe_name);
    }

    $output['rowsaffected'] = $recipe->save(1);
} catch (Exception $e) {
    $output['errmsg'] = $e->getMessage();
    $output['error'] = 1;
}

header('Content-type: application/json');

echo json_encode($output);

?>

