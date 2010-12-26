<!DOCTYPE html>

<html>

	<head>

		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Recipes : RecipeMaster</title>
        
		<style type="text/css" title="currentStyle"> 
			@import "css/demo_table.css";
		</style> 

        
		<link type="text/css" href="css/style.css" rel="stylesheet" />	
		<link type="text/css" href="css/ui-lightness/jquery-ui-1.8.7.custom.css" rel="stylesheet" />	
		<script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.7.custom.min.js"></script>
		<script type="text/javascript" language="javascript" src="js/jquery.dataTables.js"></script> 
		<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
		<script type="text/javascript">
			var ning = 0;
			function addingredient()
			{
				ning = ning+1;
				var input = document.createElement("input");
				input.type = "text";
				input.size = "10";
				input.name = "ing_qty_"+ ning;
				input.value = "100g";

				var elem = document.getElementById("ingredient_inputs");
				elem.appendChild(input);

				var input = document.createElement("input");
				input.type = "text";
				input.size = "50";
				input.name = "ing_name_"+ ning;

				var elem = document.getElementById("ingredient_inputs");
				elem.appendChild(input);

				$(input).autocomplete({
					source: [
					<?php
						$db = new PDO("mysql:host=localhost;dbname=recipemaster", "root", "");

						$i = 0;
						$result = $db->query('SELECT * FROM ingredients');
						foreach ($result as $row) {
							echo 'decodeURIComponent(\''.rawurlencode($row['name']).'\'),';
						}
					?>	
					]
				});

				var input2 = document.createElement("input");
				input2.type = "text";
				input2.size = "20";
				input2.name = "ing_method_"+ ning;
				input2.value = "diced";

				var elem = document.getElementById("ingredient_inputs");
				elem.appendChild(input2);

				var br = document.createElement("br");
				var elem = document.getElementById("ingredient_inputs");
				elem.appendChild(br);
				document.add_recipe.ingredient_count.value = ning;
				input.focus();
			}
		</script>
		<script type="text/javascript">
			$(function(){
				$('#ingredients_data').dataTable();
				
				// Accordion
				$("#accordion").accordion({ header: "h3" });

				// Tabs
				$('#tabs').tabs();

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
				$('#dialog_link').click(function(){
					$('#dialog').dialog('open');
					return false;
				});
				
				
				
				
				// Dialog			
				$('#dialog_edit').dialog({
					autoOpen: false,
					width: 600,
					buttons: {
						"Submit changes": function() {
							document.edit_ingredient.submit();
							$(this).dialog("close");
						}
					}
				});

				// Dialog Link
				$('#dialog_edit_link').click(function(){
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
				$('#dialog_link, ul#icons li').hover(
					function() { $(this).addClass('ui-state-hover'); }, 
					function() { $(this).removeClass('ui-state-hover'); }
				);

				

			});

		</script>

		<style type="text/css">

			/*demo page css*/
			body{ font: 75.5% "Trebuchet MS", sans-serif; margin: 50px;}
			.demoHeaders { margin-top: 2em; }
			#dialog_link {padding: .4em 1em .4em 20px;text-decoration: none;position: relative;}
			#dialog_link span.ui-icon {margin: 0 5px 0 0;position: absolute;left: .2em;top: 50%;margin-top: -8px;}
			ul#icons {margin: 0; padding: 0;}
			ul#icons li {margin: 2px; position: relative; padding: 4px 0; cursor: pointer; float: left;  list-style: none;}
			ul#icons span.ui-icon {float: left; margin: 0 4px;}
		</style>	

	</head>
<body>

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
/*
CREATE TABLE  `recipemaster`.`ingredients` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` TEXT NOT NULL ,
`unit` TEXT NOT NULL ,
`qty` INT NOT NULL ,
`kcal` INT NOT NULL ,
`carb` FLOAT NOT NULL ,
`sugar` FLOAT NOT NULL ,
`fibre` FLOAT NOT NULL ,
`protein` FLOAT NOT NULL ,
`fat` FLOAT NOT NULL ,
`sat_fat` FLOAT NOT NULL ,
`sodium` INT NOT NULL ,
`cholesterol` INT NOT NULL ,
`others` TEXT NOT NULL
) ENGINE = MYISAM ;


CREATE TABLE  `recipemaster`.`recipes` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` TEXT NOT NULL ,
`description` TEXT NOT NULL ,
`instructions` TEXT NOT NULL ,
`main_photo_id` INT NOT NULL
) ENGINE = MYISAM ;


CREATE TABLE  `recipemaster`.`rec_ing` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`recipe_id` INT NOT NULL ,
`ingredient_id` INT NOT NULL ,
`ingredient_qty` INT NOT NULL ,
`method` INT NOT NULL
) ENGINE = MYISAM ;


*/

/* http://www.pengoworks.com/workshop/jquery/autocomplete.htm */
?>
		<p><a href="#" id="dialog_link" class="ui-state-default ui-corner-all"><span class="ui-icon ui-icon-newwin"></span>Add Ingredient</a></p>


        <form name="delete_ingredient" action="ingredients.php" method="post">
          <input type="hidden" name="ingredient_name" value="">
          <input type="hidden" name="ingredient_id" value="-1">
          <input type="hidden" name="form_type" value="delete_ingredient">
        </form>




<div id="tabs"> 
	<ul> 
		<li><a href="#tabs-1">Recipes</a></li> 
		<li><a href="#tabs-2">Add a recipe</a></li> 
	</ul> 
	<div id="tabs-1"> 
		<div id="demo"> 
<table cellpadding="0" cellspacing="0" border="0" class="display" id="ingredients_data"> 
	<thead> 
		<tr> 
			<th>Recipe</th> 
			<th>Time Estimate</th> 
			<!--<th>Unit</th> -->
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
	<tbody>
    <?php
		$i = 0;
		$recipes = get_all_recipes();
		foreach ($recipes as $recipe) {
			$i = !$i;
			if ($i)
				echo '<tr class="even">';
			else
				echo '<tr class="odd">';


			echo '<td>'.$recipe->name.' <a href="#" onclick="
				document.edit_ingredient.ingredient_name.value=decodeURIComponent(\''.rawurlencode($recipe->name).'\');
				document.edit_ingredient.ingredient_id.value=decodeURIComponent(\''.rawurlencode($recipe->id).'\');
				$(\'#dialog_edit\').dialog(\'open\');
			">(edit)</a>
			<a href="#" onclick="
				document.delete_ingredient.ingredient_name.value=decodeURIComponent(\''.rawurlencode($recipe->name).'\');
				document.delete_ingredient.ingredient_id.value=decodeURIComponent(\''.rawurlencode($recipe->id).'\');
				document.delete_ingredient.submit();
			">(delete)</a></td>';
			echo '<td class="center">'.$recipe->getTimeEstimate().'</td>';
			$nutri_info = $recipe->getNutriInfo();
			/*echo '<td class="center">'.$row['unit'].'</td>'; */
			echo '<td class="center">'.$nutri_info['kcal'].'</td>';
			echo '<td class="center">'.$nutri_info['carb'].'</td>';
			echo '<td class="center">'.$nutri_info['sugar'].'</td>';
			echo '<td class="center">'.$nutri_info['fibre'].'</td>';
			echo '<td class="center">'.$nutri_info['protein'].'</td>';
			echo '<td class="center">'.$nutri_info['fat'].'</td>';
			echo '<td class="center">'.$nutri_info['sat_fat'].'</td>';
			echo '<td class="center">'.$nutri_info['sodium'].'</td>';
			echo '<td class="center">'.$nutri_info['cholesterol'].'</td>';
			echo '</tr>';
		}
	?>

	</tbody> 
	<tfoot> 
		<tr> 
			<th>Recipe</th> 
			<th>Time Estimate</th> 
			<!--<th>Unit</th> -->
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
		<div id="tabs-2"> 
			<form name="add_recipe" action="recipes.php" method="post">
			<label for="recipe_name">Recipe Name: </label><br/><input type="text" name="recipe_name" size="100" id="recipe_name"/>
			<!-- This <div> holds alert messages to be display in the sample page. -->
			<br/><hr/>List of ingredients:
			<div id="ingredient_inputs">
			</div>
			<a href="#" onclick="addingredient();">add ingredient field </a>
			<br/><hr/>
			<div id="alerts">
				<noscript>
					<p>
						<strong>CKEditor requires JavaScript to run</strong>. In a browser with no JavaScript
						support, like yours, you should still see the contents (HTML data) and you should
						be able to edit it normally, without a rich editor interface.
					</p>
				</noscript>
			</div>
			
				<p>
					<label for="editor1">
						Instructions:</label><br />
					<textarea class="ckeditor" cols="80" id="editor1" name="recipe_instructions" rows="10"></textarea>
				</p>

				<p>
					<input type="submit" value="Submit" />
				</p>

			<input type="hidden" name="form_type" value="add_recipe">
			<input type="hidden" name="ingredient_count" value="0">
			<input type="hidden" name="recipe_id" value="-1">
			</form>
		</div> 
</div>



	</body>

</html>
