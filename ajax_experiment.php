<?php

include('functions.php');

$error_row = -1;
$error_col = 0;
$deleted_rows = array();

try {
    //echo $query;
    $recipe = new Recipe($_REQUEST['recipe_id']);
    
    $iTotal = count($recipe->ingredients);
    $iFilteredTotal = $iTotal;
    
    $output = '{';
    $output .= '"sEcho": '.intval($_REQUEST['sEcho']).', ';
    $output .= '"error": 0, ';
    $output .= '"iTotalRecords": '.intval($iTotal).', ';
    $output .= '"iTotalDisplayRecords": '.intval($iFilteredTotal).', ';
    $output .= '"aaData": [ ';
    
    $sort_col = '';
    $sort_dir = '';
    
    function prepareOutput($name, $nutri_info, $qty_unit, $method) {
            $output = '';
            $output .= '[';
            $output .= '"'.str_replace('"', '\"',$name).'",';
            $output .= '"'.str_replace('"', '\"',$qty_unit).'",';
            $output .= '"'.str_replace('"', '\"',$method).'",';
            $output .= '"'.$nutri_info['kcal'].'",';
            $output .= '"'.$nutri_info['carb'].'",';
            $output .= '"'.$nutri_info['sugar'].'",';
            $output .= '"'.$nutri_info['fibre'].'",';
            $output .= '"'.$nutri_info['protein'].'",';
            $output .= '"'.$nutri_info['fat'].'",';
            $output .= '"'.$nutri_info['sat_fat'].'",';
            $output .= '"'.$nutri_info['sodium'].'",';
            $output .= '"'.$nutri_info['cholesterol'].'"';
            $output .= '],';
            return $output;
    }
    
    if ($_REQUEST['data']) {
        $data = $_REQUEST['data'];
        $recipe->clearIngredients();
        foreach($data as $idx => $row) {
            $error_col = 0;
            $error_row = $idx;
            if ($row[0] == '') {
                array_push($deleted_rows, $idx);
                continue;
            }
            list($qty, $unit) = split(' ', $row[1]);
            $elem = $recipe->addIngredient($qty, $unit, $row[2], -1, $row[0]);
            $error_col = 1;
            /* validate units, etc */
            $elem['Ingredient']->getNutriInfo($elem['qty'], $elem['unit']);
        }
        //function addIngredient($qty, $unit, $method, $id, $name = '') {
    }
    
    if (!empty($recipe->ingredients)) {
        foreach ($recipe->ingredients as $elem) {
            $ingredient = $elem['Ingredient'];
            $nutri_info = $ingredient->getNutriInfo($elem['qty'], $elem['unit']);
            $output .= prepareOutput($ingredient->name, $nutri_info, $elem['qty'].' '.$elem['unit'], $elem['method']);
        }
        if ($_REQUEST['adding_row'])
            $output .= "['', '', '', '','','','','','','','', ''],";
        /* Remove last comma */
        $output = substr_replace( $output, "", -1 );
    }
    
    
    $output .= '], ';
    $output .= '"deletedRows": '.json_encode($deleted_rows).',';
    $output .= '"nutriInfo": [ ';
    $parseMe = array();
    foreach ($recipe->getNutriInfo() as $name => $val) {
        $cur = array();
        $cur['name'] = $name;
        $cur['value'] = $val;
        array_push($parseMe, $cur);
    }
    $output .= json_encode($parseMe);
    $output .= '],';
    
    $n = 0;

    if ($_REQUEST['save_changes'] == 1) {
        $n = $recipe->save(1);
        $output .= '"msg": "Successfully edited recipe '.$recipe->name.'",';
    }

    $output .= '"rowsaffected": '.$n;
    $output .= '}';

    echo $output;

} catch (Exception $e) {
    $output = '{';
    $output .= '"sEcho": '.intval($_REQUEST['sEcho']).', ';
    $output .= '"iTotalRecords": 0, ';
    $output .= '"error": 1, ';
    $output .= '"errorRow": '.$error_row.', ';
    $output .= '"errorCol": '.$error_col.', ';
    $output .= '"errmsg": "'.str_replace('"', '\"', $e->getMessage()).'", ';
    $output .= '"iTotalDisplayRecords": 0, ';
    $output .= '"aaData": [ ';
    
    $output .= ']';
    $output .= '}';
    echo $output;
}
?>

