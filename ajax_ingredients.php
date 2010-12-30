<?php

include('functions.php');

$aColumns = array( 'name', 'qty', 'kcal', 'carb', 'sugar', 'fibre', 'protein', 'fat', 'sat_fat', 'sodium', 'cholesterol' );

$query = '';
$tokens = NULL;

if ( $_REQUEST['sSearch'] != "" ) {
    $query = "WHERE name LIKE :filter_name";
    $tokens = array(':filter_name' => '%'.$_REQUEST['sSearch'].'%');
}

/*
 * Ordering
 */
if ( isset( $_REQUEST['iSortCol_0'] ) )
{
	$sOrder = "ORDER BY  ";
	for ( $i=0 ; $i<intval( $_REQUEST['iSortingCols'] ) ; $i++ )
	{
		if ( $_REQUEST[ 'bSortable_'.intval($_REQUEST['iSortCol_'.$i]) ] == "true" )
		{
                        $sort_dir = strtoupper($_REQUEST['sSortDir_'.$i]);
                        if ($sort_dir != 'ASC' && $sort_dir != 'DESC')
                            continue;
			$sOrder .= $aColumns[ intval( $_REQUEST['iSortCol_'.$i] ) ]." ".$sort_dir.", ";
		}
	}
	
	$sOrder = substr_replace( $sOrder, "", -2 );
	if ( $sOrder == "ORDER BY" )
	{
		$sOrder = "";
	}
        $query .= ' '.$sOrder;
}

/* 
 * Paging
 */
if ( isset( $_REQUEST['iDisplayStart'] ) && $_REQUEST['iDisplayLength'] != '-1' )
{
    $query .= " LIMIT ".intval($_REQUEST['iDisplayStart']).", ".intval($_REQUEST['iDisplayLength']);
}


//echo $query;
$ingredients = get_all_ingredients($query, $tokens);


$iTotal = get_ingredients_count();
$iFilteredTotal = count($ingredients);

$output = '{';
$output .= '"sEcho": '.intval($_REQUEST['sEcho']).', ';
$output .= '"iTotalRecords": '.intval($iTotal).', ';
$output .= '"iTotalDisplayRecords": '.intval($iFilteredTotal).', ';
$output .= '"aaData": [ ';

if (!empty($ingredients)) {
    foreach ($ingredients as $ingredient) {
        $output .= '[';
        $output .= '"'.str_replace('"', '\"',$ingredient->name.
                                   '<a href="#" class="boring" onclick="editingredient(\''.$ingredient->id.'\');" ><img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)"/></a>'.
                                   '<a href="#" class="boring" onclick="deleteingredient(\''.$ingredient->id.'\');" ><img class="boring" src="icons/cross.png" width="16" height="16" alt="(delete)"/></a>'
                                    ).'",';
        $output .= '"'.str_replace('"', '\"',$ingredient->qty.$ingredient->unit).'",';
        $output .= '"'.$ingredient->kcal.'",';
        $output .= '"'.$ingredient->carb.'",';
        $output .= '"'.$ingredient->sugar.'",';
        $output .= '"'.$ingredient->fibre.'",';
        $output .= '"'.$ingredient->protein.'",';
        $output .= '"'.$ingredient->fat.'",';
        $output .= '"'.$ingredient->sat_fat.'",';
        $output .= '"'.$ingredient->sodium.'",';
        $output .= '"'.$ingredient->cholesterol.'"';
        $output .= '],';
    }
    /* Remove last comma */
    $output = substr_replace( $output, "", -1 );
    

}

$output .= ']';
$output .= '}';
    
echo $output;

?>