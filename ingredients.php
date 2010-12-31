<?php
include('functions.php');

function tidyhtml($input)
{
    $config = array(
	   'indent'         => true,
	   'output-xhtml'   => true,
	   'wrap'           => 200);

    $tidy = new tidy();
    $tidy->parseString($input, $config, 'utf8');
    $tidy->cleanRepair();

    // Output
    return $tidy;
}

ob_start('tidyhtml');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--
TODO: add a show ingredient page, possibly with some photos, etc
TODO: add per-ingredient custom units (i.e. 1 glass) (XXX: probably not)
TODO: add optional ingredients as dynamic list of extras, like ingredients in a recipe
 -->
 
<html>
<head>
    <meta name="generator" content="HTML Tidy for Windows (vers 11 August 2008), see www.w3.org">
    <meta http-equiv="Content-Type" content="text/html; charset=us-ascii">

    <title>Ingredients : RecipeMaster</title>
    <style type="text/css" title="currentStyle">

			@import "css/demo_table.css";
    </style>
    <link type="text/css" href="css/style.css" rel="stylesheet">
    <link type="text/css" href="css/ui-lightness/jquery-ui-1.8.7.custom.css" rel="stylesheet">
    <script type="text/javascript" src="js/jquery-1.4.4.min.js">
</script>
    <script src="http://cdn.jquerytools.org/1.2.5/tiny/jquery.tools.min.js"></script>
    
    
    
    
    
    
    <script type="text/javascript" src="js/jquery-ui-1.8.7.custom.min.js">
</script>
    <script type="text/javascript" language="javascript" src="js/jquery.dataTables.js">
</script>
    <script type="text/javascript">
	function addtooltip(input) {
	    img = document.createElement("img");
	    img.alt = '?';
	    img.height = '16';
	    img.width = '16';
	    img.src = 'icons/help.png';
	    img.className = 'boring';
	    img.onmouseover = function() {
		$(input).data('tooltip').show();
	    }
	    img.onmouseout = function() {
		$(input).data('tooltip').hide();
	    }
	    parent = input.parentNode;
	    parent.appendChild(img);
	}
	function editingredient(id) {
	    $.post("ajax_formdata.php", {
		ingredient: id
	    },
	    function(ingredient) {
		// format and output result
		if (ingredient.exception) {
		    alert(ingredient.exception);
		    return;
		}

		document.add_ingredient.ingredient_name.value = decodeURIComponent(ingredient.name);
		document.add_ingredient.ingredient_qty.value = decodeURIComponent(ingredient.qty);
		document.add_ingredient.ingredient_unit.value = decodeURIComponent(ingredient.unit);
		document.add_ingredient.ingredient_typical_qty.value = decodeURIComponent(ingredient.typical_qty);
		document.add_ingredient.ingredient_typical_unit.value = decodeURIComponent(ingredient.typical_unit);
		document.add_ingredient.ingredient_kcal.value = decodeURIComponent(ingredient.kcal);
		document.add_ingredient.ingredient_carb.value = decodeURIComponent(ingredient.carb);
		document.add_ingredient.ingredient_sugar.value = decodeURIComponent(ingredient.sugar);
		document.add_ingredient.ingredient_fat.value = decodeURIComponent(ingredient.fat);
		document.add_ingredient.ingredient_sat_fat.value = decodeURIComponent(ingredient.sat_fat);
		document.add_ingredient.ingredient_protein.value = decodeURIComponent(ingredient.protein);
		document.add_ingredient.ingredient_fibre.value = decodeURIComponent(ingredient.fibre);
		document.add_ingredient.ingredient_sodium.value = decodeURIComponent(ingredient.sodium);
		document.add_ingredient.ingredient_cholesterol.value = decodeURIComponent(ingredient.cholesterol);
		document.add_ingredient.ingredient_others.value = decodeURIComponent(ingredient.others);
		document.add_ingredient.ingredient_id.value = decodeURIComponent(ingredient.id);
		document.add_ingredient.form_type.value = 'edit_ingredient';

		$('#dialog').dialog('option',
		{
		    title: 'Edit ingredient',
		    autoOpen: false,
		    width: 600,
		    buttons: {
			"Submit changes": function() {
			    document.add_ingredient.submit();
			    $(this).dialog("close");
			}
		    }
		});
		$('#dialog').dialog('open');
	    },
	    'json');
	}

	function deleteingredient(id) {
	    document.delete_ingredient.ingredient_id.value = id;
	    document.delete_ingredient.submit();
	}</script>
    <script type="text/javascript">	$(function() {
	    // select all desired input fields and attach tooltips to them
	    $(".has_tooltip").tooltip({
		    position: "top center",
	    	    events: {
			def: ',',
			input: ',',
			widget: ',',
			tooltip: ','
			
		    },
		    effect: "fade",
		    opacity: 0.7
	    });

	    $('#ingredients_data').dataTable({
		//"bJQueryUI": true,
		"sPaginationType": "full_numbers",
		"bServerSide": true,
		"bProcessing": true,
		"sAjaxSource": 'ajax_ingredients.php',
		"aoColumnDefs": [
			{ "aTargets": [ 0 ], "sWidth": '200px' }
		    ]
	    });

	    // Add tooltips
	    $('.has_tooltip').each(function() {
		addtooltip(this);
	    });

	    // Dialog                       
	    $('#dialog').dialog({
		autoOpen: false,
		width: 600,
		buttons: {
		    "Add Ingredient": function() {
			document.add_ingredient.submit();
			$(this).dialog("close");
		    }
		}
	    });

	    // Dialog Link
	    $('#dialog_link').click(function() {
		document.add_ingredient.ingredient_name.value = '';
		document.add_ingredient.ingredient_qty.value = '';
		document.add_ingredient.ingredient_unit.value = '';
		document.add_ingredient.ingredient_typical_qty.value = '';
		document.add_ingredient.ingredient_typical_unit.value = '';
		document.add_ingredient.ingredient_kcal.value = '';
		document.add_ingredient.ingredient_carb.value = '';
		document.add_ingredient.ingredient_sugar.value = '';
		document.add_ingredient.ingredient_fat.value = '';
		document.add_ingredient.ingredient_sat_fat.value = '';
		document.add_ingredient.ingredient_protein.value = '';
		document.add_ingredient.ingredient_fibre.value = '';
		document.add_ingredient.ingredient_sodium.value = '';
		document.add_ingredient.ingredient_cholesterol.value = '';
		document.add_ingredient.ingredient_others.value = '';
		document.add_ingredient.ingredient_id.value = '-1';
		document.add_ingredient.form_type.value = 'add_ingredient';
		
		$('#dialog').dialog('option', {
		    title: 'Add an ingredient',
		    autoOpen: false,
		    width: 600,
		    buttons: {
			"Add Ingredient": function() {
			    document.add_ingredient.submit();
			    $(this).dialog("close");
			}
		    }
		});

		$('#dialog').dialog('open');
		return false;
	    });

	});</script>
    <style type="text/css">

			/*demo page css*/
			.demoHeaders { margin-top: 2em; }
			#dialog_link {padding: .4em 1em .4em 20px;text-decoration: none;position: relative;}
			#dialog_link span.ui-icon {margin: 0 5px 0 0;position: absolute;left: .2em;top: 50%;margin-top: -8px;}
			ul#icons {margin: 0; padding: 0;}
			ul#icons li {margin: 2px; position: relative; padding: 4px 0; cursor: pointer; float: left;  list-style: none;}
			ul#icons span.ui-icon {float: left; margin: 0 4px;}
    </style>
</head>

<body>
    <?php print_header(); ?>
    <div class="spacer container_16"></div>
    <div id="content" class="container_16">
	<div class="container_16">
	    <h1>Ingredients<a class="boring" href="#" id="dialog_link" name="dialog_link"><img class="boring" src="icons/add.png" width="16" height="16" alt="Add Ingredient"></a></h1>
	</div>
	
	<?php

		function print_msg($msg) {
			echo '<div class="ui-widget">
					<div class="ui-state-highlight ui-corner-all" style="margin-top: 5px; padding: 0 .7em;"> 
						<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>'.$msg.'</p>
					</div>
				</div>';
		}

		function print_error($msg) {
			echo '<div class="ui-widget">
				<div class="ui-state-error ui-corner-all" style="margin-top: 5px;padding: 0 .7em;"> 
						<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>'.$msg.'</p>
					</div>
				</div>';
		}

		if ($_POST['ingredient_id']) {
			try {
				$form_type = $_POST['form_type'];
				$ingredient_name = $_POST['ingredient_name'];
				$ingredient_id = $_POST['ingredient_id'];

				$ingredient_unit = trim($_POST['ingredient_unit']);
				if (ord($ingredient_unit) == 194)
				    $ingredient_unit = '';

				$ingredient_qty = $_POST['ingredient_qty'];
				$ingredient_typical_unit = $_POST['ingredient_typical_unit'];
				$ingredient_typical_qty = $_POST['ingredient_typical_qty'];
				$ingredient_kcal = $_POST['ingredient_kcal'];
				$ingredient_carb = $_POST['ingredient_carb'];
				$ingredient_sugar = $_POST['ingredient_sugar'];
				$ingredient_fibre = $_POST['ingredient_fibre'];
				$ingredient_protein = $_POST['ingredient_protein'];
				$ingredient_fat = $_POST['ingredient_fat'];
				$ingredient_sat_fat = $_POST['ingredient_sat_fat'];
				$ingredient_sodium = $_POST['ingredient_sodium'];
				$ingredient_cholesterol = $_POST['ingredient_cholesterol'];
				$ingredient_others = $_POST['ingredient_others'];
				if (($form_type == "add_ingredient") || ($form_type == "edit_ingredient"))
					$new = 1;
				else
					$new = 0;

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
					print_msg("Rows affected: ".$n."<br/>");
				}
				else if ($form_type == "edit_ingredient") {
					$n = $ingredient->save(1);

					if ($n > 0)
						print_msg('Successfully edited ingredient '.$ingredient_name);
					print_msg("Rows affected: ".$n."<br/>");
				}
				else if ($form_type == "delete_ingredient") {
					$n = $ingredient->delete();
					if ($n > 0)
						print_msg('Successfully deleted ingredient '.$ingredient_name);
					print_msg("Rows affected: ".$n."<br/>");
				}
			
			} catch (Exception $e) {
				$code = $e->getCode();
				$msg = $e->getMessage();
				if (($code == "HY000") && (stripos($msg, 'foreign'))) {
				    print_error('Cannot delete this ingredient, at least one recipe still depends on it.');
				} else {
				    print_error('Exception: '.$msg);
				}
			}
		}

		/* http://www.pengoworks.com/workshop/jquery/autocomplete.htm */
		?>


	<form name="delete_ingredient" action="ingredients.php" method="post" id="delete_ingredient">
	    <input type="hidden" name="ingredient_name" value=""> <input type="hidden" name="ingredient_id" value="-1"> <input type="hidden" name="form_type" value="delete_ingredient">
	</form><!-- ui-dialog -->

	<div id="dialog" title="Add an ingredient">
	    <form name="add_ingredient" action="ingredients.php" method="post" id="add_ingredient">
		<div class="row">
		    <span class="left">
			<span class="label">Name:</span>
			<span class="formw">
			    <input type="text" name="ingredient_name">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">Qty (100):</span>
			<span class="formw">
			    <input size="12" type="text" name="ingredient_qty" class="has_tooltip" title="The quantity of this ingredient that the nutritional information is for">
			    <select name="ingredient_unit">
				<option>&nbsp;</option><option>g</option><option>ml</option><option>mg</option><option>kg</option><option>l</option>
			    </select>
			</span>
		    </span>
		</div>
		
		<div class="row">
		    <span class="left">
			<span class="label">Typical unit weight:</span>
			<span class="formw">
			    <input size="12" type="text" name="ingredient_typical_qty">
			    <select name="ingredient_typical_unit" class="has_tooltip" title="The typical weight of one unit of this ingredient (i.e. the typical weight of 1 tomato)">
				<option>g</option><option>mg</option><option>kg</option>
			    </select>
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">kcal:</span>
			<span class="formw">
			    <input type="text" name="ingredient_kcal">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">Carbohydrates (g):</span>
			<span class="formw">
			    <input type="text" name="ingredient_carb">
			</span>
		    </span>
		    <span class="right">
			<span class="label">of which sugars (g):</span>
			<span class="formw">
			    <input type="text" name="ingredient_sugar">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">Fat (g):</span>
			<span class="formw">
			    <input type="text" name="ingredient_fat">
			</span>
		    </span>
		    <span class="right">
			<span class="label">of which saturates (g):</span>
			<span class="formw">
			    <input type="text" name="ingredient_sat_fat">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">Protein (g):</span>
			<span class="formw">
			    <input type="text" name="ingredient_protein">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">Fibre (g):</span>
			<span class="formw">
			    <input type="text" name="ingredient_fibre">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">Sodium (mg):</span>
			<span class="formw">
			    <input type="text" name="ingredient_sodium">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">Cholesterol (mg):</span>
			<span class="formw">
			    <input type="text" name="ingredient_cholesterol">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="labelfull">Others (foo=bar, moh=meh):</span>
		    <span class="formfull">
			<textarea cols="100" rows="3" style="width: 90%;" name="ingredient_others"></textarea>
		    </span>
		</div>

		<input type="hidden" name="ingredient_id" value="-1">
		<input type="hidden" name="form_type" value="add_ingredient">
		<!--<input type="submit"/>-->
	    </form>
	</div>

	<div id="demo">
	    <table cellpadding="0" cellspacing="0" border="0" class="display" id="ingredients_data">
		<thead>
		    <tr>
			<th>Ingredient</th>

			<th>Quantity</th><!--<th>Unit</th> -->

			<th>kcal</th>

			<th>Carbs (g)</th>

			<th>Sugars (g)</th>

			<th>Fibre (g)</th>

			<th>Protein (g)</th>

			<th>Total Fat (g)</th>

			<th>Sat. Fat (g)</th>

			<th>Sodium (g)</th>

			<th>Cholesterol (g)</th>
		    </tr>
		</thead>

		<tfoot>
		    <tr>
			<th>Ingredient</th>

			<th>Quantity</th><!--<th>Unit</th> -->

			<th>kcal</th>

			<th>Carbs (g)</th>

			<th>Sugars (g)</th>

			<th>Fibre (g)</th>

			<th>Protein (g)</th>

			<th>Total Fat (g)</th>

			<th>Sat. Fat (g)</th>

			<th>Sodium (g)</th>

			<th>Cholesterol (g)</th>
		    </tr>
		</tfoot>
	    </table>
	</div>
    </div>

    <div class="spacer container_16"></div>
    <?php print_footer(); ?>
</body>
</html>
