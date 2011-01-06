<?php

include('functions.php');

$aColumns = array( 'name', 'time_estimate', 'kcal', 'carb', 'sugar', 'fibre', 'protein', 'fat', 'sat_fat', 'sodium', 'cholesterol' );

$query = '';
$tokens = NULL;

if ( $_REQUEST['sSearch'] != "" ) {
    $query = "WHERE name LIKE :filter_name";
    $tokens = array(':filter_name' => '%'.$_REQUEST['sSearch'].'%');
}

$iFilteredTotal = get_recipes_count($query, $tokens);

/* 
 * Paging
 */
if ( isset( $_REQUEST['iDisplayStart'] ) && $_REQUEST['iDisplayLength'] != '-1' )
{
    $query .= " LIMIT ".intval($_REQUEST['iDisplayStart']).", ".intval($_REQUEST['iDisplayLength']);
}

try {
//echo $query;
$recipes = get_all_recipes($query, $tokens);

$iTotal = get_recipes_count();

$output = '{';
$output .= '"sEcho": '.intval($_REQUEST['sEcho']).', ';
$output .= '"iTotalRecords": '.intval($iTotal).', ';
$output .= '"iTotalDisplayRecords": '.intval($iFilteredTotal).', ';
$output .= '"aaData": [ ';

$sort_col = '';
$sort_dir = '';

function recipe_sort($a, $b)
{
    global $sort_col;
    global $sort_dir;

    /* XXX: http://bugs.php.net/bug.php?id=50688 */
    try {
	$nutri_info_a = $a->getNutriInfo(1);
	$nutri_info_b = $b->getNutriInfo(1);
    } catch(Exception $e) {
	return 0;
    }


    switch ($sort_col) {
	case "name":
	    if ($sort_dir == 'ASC')
		return strcasecmp($a->name, $b->name);
	    else {
		$ret = strcasecmp($a->name, $b->name);
		if ($ret > 0)
		    $ret = -1;
		else if ($ret < 0)
		    $ret = 1;
		return $ret;
	    }
	case "time_estimate":
	    $val_a = $a->time_estimate;
	    $val_b = $b->time_estimate;
	    break;
	case "kcal":
	case "carb":
	case "sugar":
	case "fibre":
	case "protein":
	case "fat":
	case "sat_fat":
	case "sodium":
	case "cholesterol":
	    $val_a = $nutri_info_a[$sort_col];
	    $val_b = $nutri_info_b[$sort_col];
	    break;
	default:
	    return 0;
    }
    if ($val_a == $val_b) {
        return 0;
    }
    if ($sort_dir == 'ASC')
	return ($val_a < $val_b) ? -1 : 1;
    else
	return ($val_a < $val_b) ? 1 : -1;
}

if (!empty($recipes)) {
    /*
     * Ordering
     */
    if ( isset( $_REQUEST['iSortCol_0'] ) )
    {
	    for ( $i=0 ; $i<intval( $_REQUEST['iSortingCols'] ) ; $i++ )
	    {
		    if ( $_REQUEST[ 'bSortable_'.intval($_REQUEST['iSortCol_'.$i]) ] == "true" )
		    {
			    $sort_dir = strtoupper($_REQUEST['sSortDir_'.$i]);
			    if ($sort_dir != 'ASC' && $sort_dir != 'DESC')
				continue;
			    $sort_col = $aColumns[ intval( $_REQUEST['iSortCol_'.$i] ) ];
			    
			    usort($recipes, "recipe_sort");
		    }
	    }
    }

    foreach ($recipes as $recipe) {
	$nutri_info = $recipe->getNutriInfo(1);
    
	$output .= '[';
	$output .= '"'.str_replace('"', '\"','<a class="recipe" href="show_recipe.php?recipe_id='.$recipe->id.'">'.$recipe->name.'</a>'.
				   '<a class="boring" href="#" title="Copy Recipe" onclick="copyrecipe(\''.$recipe->id.'\');" ><img class="boring" src="icons/page_copy.png" width="16" height="16" alt="(edit)"/></a>'.
				   '<a class="boring" href="#" title="Delete Recipe" onclick="deleterecipe(\''.$recipe->id.'\');" ><img class="boring" src="icons/cross.png" width="16" height="16" alt="(delete)"/></a>'
				    ).'",';
	//$output .= '"'.str_replace('"', '\"',$recipe->getTimeEstimate()).'",';
	
	$output .= '"'.$nutri_info['kcal'].'",';
	$output .= '"'.$nutri_info['carb'].'",';
	$output .= '"'.$nutri_info['sugar'].'",';
	$output .= '"'.$nutri_info['fibre'].'",';
	$output .= '"'.$nutri_info['protein'].'",';
	$output .= '"'.$nutri_info['fat'].'",';
	$output .= '"'.$nutri_info['sat_fat'].'",';
	$output .= '"'.$nutri_info['sodium'].'",';
	$output .= '"'.$nutri_info['cholesterol'].'",';
	$output .= '"'.str_replace('"', '\"',$recipe->getMTime()).'"';
	$output .= '],';
    }
    /* Remove last comma */
    $output = substr_replace( $output, "", -1 );
    

}

$output .= ']';
$output .= '}';
    
echo $output;
} catch (Exception $e) {
    $output = '{';
    $output .= '"sEcho": '.intval($_REQUEST['sEcho']).', ';
    $output .= '"iTotalRecords": 0, ';
    $output .= '"iTotalDisplayRecords": 0, ';
    $output .= '"aaData": [ ';
    
    $output .= ']';
    $output .= '}';
    echo $output;
}
?>

