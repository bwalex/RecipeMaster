<?php
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
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!--
TODO: ability to remove ingredients from recipe ingredient list
TODO: clean list of ingredients each time
TODO: add photo stuff
 -->

<html>
<head>
    <meta name="generator" content="HTML Tidy for Windows (vers 11 August 2008), see www.w3.org">
    <meta http-equiv="Content-Type" content="text/html; charset=us-ascii">

    <title>Recipes : RecipeMaster</title>
    <style type="text/css" title="currentStyle">
	    @import "css/demo_table.css";
    </style>
    <link type="text/css" href="css/style.css" rel="stylesheet">
    <link type="text/css" href="css/ui-lightness/jquery-ui-1.8.7.custom.css" rel="stylesheet">
    <script type="text/javascript" src="js/jquery-1.4.4.min.js">
</script>
    <script type="text/javascript" src="js/jquery-ui-1.8.7.custom.min.js">
</script>
    <script type="text/javascript" language="javascript" src="js/jquery.dataTables.js">
</script>
    <script type="text/javascript" src="ckeditor/ckeditor.js">
</script>
    <script type="text/javascript">	    function deleteallingredients(form, id) {
		document.getElementById(form).ingredient_count.value = 0;
		var elem = document.getElementById(id);
		while (elem.hasChildNodes()) {
		    elem.removeChild(elem.firstChild);
		}
	    }

	    function deletelastingredient(form, id) {
		var elem = document.getElementById(id);
		if (elem.hasChildNodes()) {
		    ning = Number(document.getElementById(form).ingredient_count.value) - 1;
		    elem.removeChild(elem.lastChild);
		    document.getElementById(form).ingredient_count.value = ning;
		}
	    }

	    function addingredient(form, id, qty, name, method) {
		ning = Number(document.getElementById(form).ingredient_count.value) + 1;
		var input = document.createElement("input");
		input.type = "text";
		input.size = "10";
		input.name = "ing_qty_" + ning;
		input.value = qty;

		var elem = document.getElementById(id);
		elem.appendChild(input);

		var input = document.createElement("input");
		input.type = "text";
		input.size = "50";
		input.name = "ing_name_" + ning;
		input.value = name;

		var elem = document.getElementById(id);
		elem.appendChild(input);

		$(input).autocomplete({
		    source: function(request, response) {
			$.ajax({
			    url: "ajax_autocomplete.php",
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
		    },
		    minLength: 2
		});

		var input2 = document.createElement("input");
		input2.type = "text";
		input2.size = "20";
		input2.name = "ing_method_" + ning;
		input2.value = method;

		var elem = document.getElementById(id);
		elem.appendChild(input2);

		var br = document.createElement("br");
		var elem = document.getElementById(id);
		elem.appendChild(br);
		document.getElementById(form).ingredient_count.value = ning;
		input.focus();
	    }

	    function editrecipe(id) {
		$.post("ajax_formdata.php", {
		    recipe: id
		},
		function(recipe) {
		    // format and output result
		    if (recipe.exception) {
			alert(recipe.exception);
			return;
		    }

		    deleteallingredients('edit_recipe', 'ingredient_edit_inputs');
		    document.edit_recipe.recipe_name.value = recipe.name;
		    document.edit_recipe.recipe_instructions.value = decodeURIComponent(recipe.instructions);
		    CKEDITOR.instances.edit_instructions_editor.setData(decodeURIComponent(recipe.instructions),
		    function() {
			this.checkDirty(); // true
		    });
		    for (var i in recipe.ingredients) {
			addingredient('edit_recipe', 'ingredient_edit_inputs', recipe.ingredients[i].qty + recipe.ingredients[i].unit, recipe.ingredients[i].name, recipe.ingredients[i].method);
		    }
		    document.edit_recipe.recipe_id.value = recipe.id;

		    $('#dialog_edit').dialog('open');
		},
		'json');
	    }

	    function deleterecipe(id) {
		document.delete_recipe.recipe_id.value = id;
		document.delete_recipe.submit();
	    }</script>
    <script type="text/javascript">	    $(function() {
		$('#recipe_data').dataTable({
		    //"bJQueryUI": true,
		    "sPaginationType": "full_numbers",
		    "bServerSide": true,
		    "bProcessing": true,
		    "sAjaxSource": 'ajax_recipes.php'
		});

		// Accordion
		$("#accordion").accordion({
		    header: "h3"
		});

		// Tabs
		$('#tabs').tabs();

		// Dialog                       
		$('#dialog').dialog({
		    autoOpen: false,
		    width: 800,
		    buttons: {
			"Add Recipe": function() {
			    document.add_recipe.submit();
			    $(this).dialog("close");
			}
		    }
		});

		// Dialog Link
		$('#dialog_link').click(function() {
		    $('#dialog').dialog('open');
		    return false;
		});

		// Dialog                       
		$('#dialog_edit').dialog({
		    autoOpen: false,
		    width: 800,
		    buttons: {
			"Submit changes": function() {
			    document.edit_recipe.submit();
			    $(this).dialog("close");
			}
		    }
		});

		// Dialog Link
		$('#dialog_edit_link').click(function() {
		    $('#dialog_edit').dialog('open');
		    return false;
		});

		// Datepicker
		$('#datepicker').datepicker({
		    inline: true
		});

		// Slider
		$('#slider').slider({
		    range: true,
		    values: [17, 67]
		});

		// Progressbar
		$("#progressbar").progressbar({
		    value: 20
		});

		//hover states on the static widgets
		$('#dialog_link, ul#icons li').hover(function() {
		    $(this).addClass('ui-state-hover');
		},
		function() {
		    $(this).removeClass('ui-state-hover');
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
    <div id="header">
	<h1>RecipeMaster</h1>
    </div>

    <div id="main">
	<?php
			include('functions.php');

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

			if ($_POST['recipe_id'] && $_POST['recipe_name']) {
				try {
					$form_type = $_POST['form_type'];
					$recipe_name = $_POST['recipe_name'];
					$recipe_id = $_POST['recipe_id'];
					$recipe_instructions = $_POST['recipe_instructions'];
					$recipe_description = '';
					$recipe_time_estimate = 60;
					$ingredient_count = $_POST['ingredient_count'];
					if (($form_type == "add_recipe") || ($form_type == "edit_recipe"))
						$new = 1;
					else
						$new = 0;

					$recipe = new Recipe($recipe_id, $recipe_name, $new,
					    $recipe_description, $recipe_instructions,
					    $recipe_time_estimate);
					
					if ($form_type == "add_recipe") {
						for ($i = 1; $i <= $ingredient_count; $i++) {
							$ingredient_id = -1;
							$ingredient_name = $_POST['ing_name_'.$i];
							$ingredient_qty_unit = $_POST['ing_qty_'.$i];
							$method = $_POST['ing_method_'.$i];
							$ingredient_qty = preg_replace('/[^0-9]*/','', $ingredient_qty_unit);
							$ingredient_unit = preg_replace('/[0-9]*/','', $ingredient_qty_unit);
							
							$recipe->addIngredient($ingredient_qty, $ingredient_unit,
							    $method, $ingredient_id, $ingredient_name);
						}
						
						$n = $recipe->save();
						if ($n > 0)
							print_msg('Successfully added recipe '.$ingredient_name);
						print_msg("Rows affected: ".($n + $m)."<br/>");
					} else if ($form_type == "edit_recipe") {
						for ($i = 1; $i <= $ingredient_count; $i++) {
							$ingredient_id = -1;
							$ingredient_name = $_POST['ing_name_'.$i];
							$ingredient_qty_unit = $_POST['ing_qty_'.$i];
							$method = $_POST['ing_method_'.$i];
							$ingredient_qty = preg_replace('/[^0-9]*/','', $ingredient_qty_unit);
							$ingredient_unit = preg_replace('/[0-9]*/','', $ingredient_qty_unit);
							
							$recipe->addIngredient($ingredient_qty, $ingredient_unit,
							    $method, $ingredient_id, $ingredient_name);
						}
						$n = $recipe->save(1);

						if ($n > 0)
							print_msg('Successfully edited recipe '.$recipe_name);
						print_msg("Rows affected: ".$n."<br/>");
					} else if ($form_type == "delete_recipe") {
						$n = $recipe->delete();
						if ($n > 0)
							print_msg('Successfully deleted recipe '.$recipe_name);
						print_msg("Rows affected: ".$n."<br/>");
					}
				} catch (Exception $e) {
					print_error('Exception: '.$e->getMessage());
				}
			}
			/* http://www.pengoworks.com/workshop/jquery/autocomplete.htm */
			?>

	<form name="delete_recipe" action="recipes.php" method="post" id="delete_recipe">
	    <input type="hidden" name="recipe_name" value=""> <input type="hidden" name="recipe_id" value="-1"> <input type="hidden" name="form_type" value="delete_recipe">
	</form>

	<div id="dialog" title="Add a recipe">
	    <form name="add_recipe" id="add_recipe" action="recipes.php" method="post">
		<label for="recipe_name">Recipe Name:</label><br>
		<input type="text" name="recipe_name" size="100" id="recipe_name"> <!-- This <div> holds alert messages to be display in the sample page. -->
		<br>
		<hr>
		List of ingredients:

		<div id="ingredient_add_inputs"></div><a href="#" onclick="addingredient('add_recipe', 'ingredient_add_inputs', '100g', '', 'diced');"><img src="add-icon.png" width="16" height="16" alt="add ingredient field"></a><br>
		<hr>

		<div id="alerts">
		    <noscript>
		    <p><strong>CKEditor requires JavaScript to run</strong>. In a browser with no JavaScript support, like yours, you should still see the contents (HTML data) and you should be able to edit it normally, without a rich editor interface.</p></noscript>
		</div>

		<p><label for="add_instructions_editor">Instructions:</label><br>
		<textarea class="ckeditor" cols="80" id="add_instructions_editor" name="recipe_instructions" rows="10">
</textarea></p>

		<p><input type="submit" value="Submit"></p><input type="hidden" name="form_type" value="add_recipe"> <input type="hidden" name="ingredient_count" value="0"> <input type="hidden" name="recipe_id" value="-1">
	    </form>
	</div>

	<div id="dialog_edit" title="Edit recipe">
	    <form name="edit_recipe" id="edit_recipe" action="recipes.php" method="post">
		<label for="recipe_name">Recipe Name:</label><br>
		<input type="text" name="recipe_name" size="100" id="recipe_name"> <!-- This <div> holds alert messages to be display in the sample page. -->
		<br>
		<hr>
		List of ingredients:

		<div id="ingredient_edit_inputs"></div><a href="#" onclick="addingredient('edit_recipe', 'ingredient_edit_inputs', '100g', '', 'diced');">add ingredient field</a><br>
		<hr>

		<div id="alerts">
		    <noscript>
		    <p><strong>CKEditor requires JavaScript to run</strong>. In a browser with no JavaScript support, like yours, you should still see the contents (HTML data) and you should be able to edit it normally, without a rich editor interface.</p></noscript>
		</div>

		<p><label for="edit_instructions_editor">Instructions:</label><br>
		<textarea cols="80" class="ckeditor" id="edit_instructions_editor" name="recipe_instructions" rows="10">
</textarea></p>

		<p><input type="submit" value="Submit"></p><input type="hidden" name="form_type" value="edit_recipe"> <input type="hidden" name="ingredient_count" value="0"> <input type="hidden" name="recipe_id" value="-1">
	    </form>
	</div>

	<p><a href="#" id="dialog_link" class="ui-state-default ui-corner-all" name="dialog_link">Add Recipe</a></p>

	<div id="demo">
	    <table cellpadding="0" cellspacing="0" border="0" class="display" id="recipe_data">
		<thead>
		    <tr>
			<th>Recipe</th>

			<th>Time Estimate</th><!--<th>Unit</th> -->

			<th>kcal</th>

			<th>Carbs (g)</th>

			<th>Sugars (g)</th>

			<th>Fibre (g)</th>

			<th>Protein (g)</th>

			<th>Total Fat (g)</th>

			<th>Sat. Fat (g)</th>

			<th>Sodium (mg)</th>

			<th>Cholesterol (mg)</th>
		    </tr>
		</thead>

		<tfoot>
		    <tr>
			<th>Recipe</th>

			<th>Time Estimate</th><!--<th>Unit</th> -->

			<th>kcal</th>

			<th>Carbs (g)</th>

			<th>Sugars (g)</th>

			<th>Fibre (g)</th>

			<th>Protein (g)</th>

			<th>Total Fat (g)</th>

			<th>Sat. Fat (g)</th>

			<th>Sodium (mg)</th>

			<th>Cholesterol (mg)</th>
		    </tr>
		</tfoot>
	    </table>
	</div>
    </div>

    <div id="footer">
	<span style="margin-top: 10px; float: left;"><a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Transitional" height="31" width="88"></a></span>

	<h4>&copy; 2010, Alex Hornung</h4>
    </div>
</body>
</html>
