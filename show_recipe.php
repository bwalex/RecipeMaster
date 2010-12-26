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

<div style="width:800px;">
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

if ($_GET['recipe_id']) {
	try {
		$recipe = new Recipe($_GET['recipe_id']);
		$info = $recipe->getNutriInfo();
		echo '<h1>'.$recipe->name.'</h1>';
		echo '<span style="float:right;">';
		echo '<img src="nutrilabel.php?carbs='.$info['carb'].'&protein='.$info['protein'].'&fat='.$info['fat'].'&sat_fat='.$info['sat_fat'].'&calories='.$info['kcal'].'&cholesterol='.$info['cholesterol'].'&sodium='.$info['sodium'].'&fibre='.$info['fibre'].'&sugars='.$info['sugar'].'"/>';
		echo '</span>';
		echo '<h2>Ingredients</h2>';
		echo '<ul>';
		foreach ($recipe->ingredients as $ingredient) {
			echo '<li>'.$ingredient['qty'].$ingredient['unit'].' '.$ingredient['Ingredient']->name.' ('.$ingredient['method'].')</li>';
		}
		echo '</ul>';
		
		echo '<h2>Preparation</h2>';
		echo $recipe->instructions;
		echo 'jieajfoieafjieajfoaifje ifoisajfajpoairjg posargjoirsjg psargjposajgapoisjgrpoaisjgrpaorsigjsa irgpo isarjgpoisajgposajgsapoijgsag sagoisajgoisajgsapoirjggr8wgohgsglirsh goipahaghaoisrpsahgaoghaspoihsg oihsagrhapoishgposahgsap ';
	} catch (Exception $e) {
		print_error('Exception: '.$e->getMessage());
	}
} else {
	print_error('No recipe id specified!');
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
	

</div>



	</body>

</html>
