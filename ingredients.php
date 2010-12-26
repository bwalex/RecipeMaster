<!DOCTYPE html>

<html>

	<head>

		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Ingredients : RecipeMaster</title>
        
		<style type="text/css" title="currentStyle"> 
			@import "css/demo_table.css";
		</style> 

		<link type="text/css" href="css/style.css" rel="stylesheet" />	
		<link type="text/css" href="css/ui-lightness/jquery-ui-1.8.7.custom.css" rel="stylesheet" />	
		<script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.7.custom.min.js"></script>
		<script type="text/javascript" language="javascript" src="js/jquery.dataTables.js"></script> 

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

if ($_POST['ingredient_id'] && $_POST['ingredient_name']) {
	try {
		$form_type = $_POST['form_type'];
		$ingredient_name = $_POST['ingredient_name'];
		$ingredient_id = $_POST['ingredient_id'];
		$ingredient_unit = $_POST['ingredient_unit'];
		$ingredient_qty = $_POST['ingredient_qty'];
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

		/* http://stackoverflow.com/questions/60174/best-way-to-stop-sql-injection-in-php */
		//$db = new PDO("sqlite:recipes.db");
		$db = new PDO("mysql:host=localhost;dbname=recipemaster", "root", "");
		if ($form_type == "add_ingredient") {

			$preparedStatement = $db->prepare('SELECT * FROM ingredients WHERE name LIKE :name');
			$preparedStatement->execute(array(':name' => $ingredient_name));

			if ($preparedStatement->fetch()) {
				print_error('Ingredient '.$ingredient_name.' already exists in the database');
			} else {
				$preparedStatement = $db->prepare("INSERT INTO ingredients (name, unit, qty, kcal, carb, sugar, fibre, protein, fat, sat_fat, sodium, cholesterol, others) ".
					"VALUES (:ingredient_name, :ingredient_unit, :ingredient_qty, :ingredient_kcal, :ingredient_carb, :ingredient_sugar, :ingredient_fibre, :ingredient_protein, :ingredient_fat, :ingredient_sat_fat, :ingredient_sodium,  :ingredient_cholesterol,  :ingredient_others);");
				$preparedStatement->execute(array(
					':ingredient_name' => $ingredient_name,
					':ingredient_unit' => $ingredient_unit,
					':ingredient_qty' => $ingredient_qty,
					':ingredient_kcal' => $ingredient_kcal,
					':ingredient_carb' => $ingredient_carb,
					':ingredient_sugar' => $ingredient_sugar,
					':ingredient_fibre' => $ingredient_fibre,
					':ingredient_protein' => $ingredient_protein,
					':ingredient_fat' => $ingredient_fat,
					':ingredient_sat_fat' => $ingredient_sat_fat,
					':ingredient_sodium' => $ingredient_sodium,
					':ingredient_cholesterol' => $ingredient_cholesterol,
					':ingredient_others' => $ingredient_others
					));
				$n = $preparedStatement->rowCount();
				if ($n > 0)
					print_msg('Successfully added ingredient '.$ingredient_name);
				print_msg("Rows affected: ".$n."<br/>");
			}
		}
		else if ($form_type == "edit_ingredient") {
			$preparedStatement = $db->prepare("SELECT * FROM ingredients WHERE id=:ingredient_id");
			$preparedStatement->execute(array(':ingredient_id' => $ingredient_id));
			if ($preparedStatement->fetch()) {
				$preparedStatement = $db->prepare("UPDATE ingredients SET name=:ingredient_name, unit=:ingredient_unit, qty=:ingredient_qty, kcal=:ingredient_kcal, carb=:ingredient_carb, sugar=:ingredient_sugar, fibre=:ingredient_fibre, protein=:ingredient_protein, fat=:ingredient_fat, sat_fat=:ingredient_sat_fat, sodium=:ingredient_sodium, cholesterol=:ingredient_cholesterol,  others=:ingredient_others WHERE id=:ingredient_id");
				$preparedStatement->execute(array(
					':ingredient_name' => $ingredient_name,
					':ingredient_unit' => $ingredient_unit,
					':ingredient_qty' => $ingredient_qty,
					':ingredient_kcal' => $ingredient_kcal,
					':ingredient_carb' => $ingredient_carb,
					':ingredient_sugar' => $ingredient_sugar,
					':ingredient_fibre' => $ingredient_fibre,
					':ingredient_protein' => $ingredient_protein,
					':ingredient_fat' => $ingredient_fat,
					':ingredient_sat_fat' => $ingredient_sat_fat,
					':ingredient_sodium' => $ingredient_sodium,
					':ingredient_cholesterol' => $ingredient_cholesterol,
					':ingredient_others' => $ingredient_others,
					':ingredient_id' => $ingredient_id
					));
				$n = $preparedStatement->rowCount();
				if ($n > 0)
					print_msg('Successfully edited ingredient '.$ingredient_name);
				print_msg("Rows affected: ".$n."<br/>");
			} else {
				print_error('Ingredient '.$ingredient_name.' doesn\'t exist');
			}
		}
		else if ($form_type == "delete_ingredient") {
			$preparedStatement = $db->prepare("SELECT * FROM ingredients WHERE id=:ingredient_id");
			$preparedStatement->execute(array(':ingredient_id' => $ingredient_id));
			if ($preparedStatement->fetch()) {
				$preparedStatement = $db->prepare("DELETE FROM ingredients WHERE id=:ingredient_id");
				$preparedStatement->execute(array(':ingredient_id' => $ingredient_id));
				$n = $preparedStatement->rowCount();
				if ($n > 0)
					print_msg('Successfully deleted ingredient '.$ingredient_name);
				print_msg("Rows affected: ".$n."<br/>");
			} else {
				print_error('Ingredient '.$ingredient_name.' doesn\'t exist');
			}
		}
	
	} catch (PDOException $e) {
		print 'Exception: '.$e->getMessage();
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

*/

/* http://www.pengoworks.com/workshop/jquery/autocomplete.htm */
?>
		<p><a href="#" id="dialog_link" class="ui-state-default ui-corner-all"><span class="ui-icon ui-icon-newwin"></span>Add Ingredient</a></p>


        <form name="delete_ingredient" action="ingredients.php" method="post">
          <input type="hidden" name="ingredient_name" value="">
          <input type="hidden" name="ingredient_id" value="-1">
          <input type="hidden" name="form_type" value="delete_ingredient">
        </form>

			<!-- ui-dialog -->
    <div id="dialog" title="Add an ingredient">
        <form name="add_ingredient" action="ingredients.php" method="post">
            <div class="row">
		<span class="left">
			<span class="label">Name:</span>
			<span class="formw"><input type="text" name="ingredient_name"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">Qty (100): </span>
			<span class="formw"><input type="text" name="ingredient_qty"/></span>
		</span>
		<span class="right">
			<span class="label">Unit name (mg):</span>
			<span class="forwm"><input type="text" name="ingredient_unit"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">kcal:</span>
			<span class="formw"><input type="text" name="ingredient_kcal"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">Carbohydrates (g):</span>
			<span class="formw"><input type="text" name="ingredient_carb"/></span>
		</span>
		<span class="right">
			<span class="label">of which sugars (g):</span>
			<span class="formw"><input type="text" name="ingredient_sugar"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">Fat (g):</span>
			<span class="formw"><input type="text" name="ingredient_fat"/></span>
		</span>
		<span class="right">
			<span class="label">of which saturates (g):</span>
			<span class="formw"><input type="text" name="ingredient_sat_fat"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">Protein (g):</span>
			<span class="formw"><input type="text" name="ingredient_protein"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">Fibre (g):</span>
			<span class="formw"><input type="text" name="ingredient_fibre"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">Sodium (mg):</span>
			<span class="formw"><input type="text" name="ingredient_sodium"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">Cholesterol (mg):</span>
			<span class="formw"><input type="text" name="ingredient_cholesterol"/></span>
		</span>
            </div>
            <div class="row">
		<span class="labelfull">Others (foo=bar, moh=meh):</span>
                <span class="formfull"><textarea style="width: 90%;" name="ingredient_others"></textarea></span>
            </div>
          <input type="hidden" name="ingredient_id" value="-1">
          <input type="hidden" name="form_type" value="add_ingredient">
          <!--<input type="submit"/>-->
        </form>
    </div>









    <div id="dialog_edit" title="Edit an ingredient">
        <form name="edit_ingredient" action="ingredients.php" method="post">
            <div class="row">
		<span class="left">
			<span class="label">Name:</span>
			<span class="formw"><input type="text" name="ingredient_name"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">Qty (100): </span>
			<span class="formw"><input type="text" name="ingredient_qty"/></span>
		</span>
		<span class="right">
			<span class="label">Unit name (mg):</span>
			<span class="forwm"><input type="text" name="ingredient_unit"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">kcal:</span>
			<span class="formw"><input type="text" name="ingredient_kcal"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">Carbohydrates (g):</span>
			<span class="formw"><input type="text" name="ingredient_carb"/></span>
		</span>
		<span class="right">
			<span class="label">of which sugars (g):</span>
			<span class="formw"><input type="text" name="ingredient_sugar"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">Fat (g):</span>
			<span class="formw"><input type="text" name="ingredient_fat"/></span>
		</span>
		<span class="right">
			<span class="label">of which saturates (g):</span>
			<span class="formw"><input type="text" name="ingredient_sat_fat"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">Protein (g):</span>
			<span class="formw"><input type="text" name="ingredient_protein"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">Fibre (g):</span>
			<span class="formw"><input type="text" name="ingredient_fibre"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">Sodium (mg):</span>
			<span class="formw"><input type="text" name="ingredient_sodium"/></span>
		</span>
            </div>
            <div class="row">
		<span class="left">
			<span class="label">Cholesterol (mg):</span>
			<span class="formw"><input type="text" name="ingredient_cholesterol"/></span>
		</span>
            </div>
            <div class="row">
		<span class="labelfull">Others (foo=bar, moh=meh):</span>
                <span class="formfull"><textarea style="width: 90%;" name="ingredient_others"></textarea></span>
            </div>
          <input type="hidden" name="form_type" value="edit_ingredient">
          <input type="hidden" name="ingredient_id" value="-1">
          <!--<input type="submit"/>-->
        </form>
    </div>












			<div id="demo"> 
<table cellpadding="0" cellspacing="0" border="0" class="display" id="ingredients_data"> 
	<thead> 
		<tr> 
			<th>Ingredient</th> 
			<th>Quantity</th> 
			<!--<th>Unit</th> -->
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
	<tbody>
    <?php
		$db = new PDO("mysql:host=localhost;dbname=recipemaster", "root", "");

		$i = 0;
		$result = $db->query('SELECT * FROM ingredients');
		foreach ($result as $row) {
			$i = !$i;
			if ($i)
				echo '<tr class="even">';
			else
				echo '<tr class="odd">';


			echo '<td>'.$row['name'].' <a href="#" onclick="
				document.edit_ingredient.ingredient_name.value=decodeURIComponent(\''.rawurlencode($row['name']).'\');
				document.edit_ingredient.ingredient_qty.value=decodeURIComponent(\''.rawurlencode($row['qty']).'\');
				document.edit_ingredient.ingredient_unit.value=decodeURIComponent(\''.rawurlencode($row['unit']).'\');
				document.edit_ingredient.ingredient_kcal.value=decodeURIComponent(\''.rawurlencode($row['kcal']).'\');
				document.edit_ingredient.ingredient_carb.value=decodeURIComponent(\''.rawurlencode($row['carb']).'\');
				document.edit_ingredient.ingredient_sugar.value=decodeURIComponent(\''.rawurlencode($row['sugar']).'\');
				document.edit_ingredient.ingredient_fat.value=decodeURIComponent(\''.rawurlencode($row['fat']).'\');
				document.edit_ingredient.ingredient_sat_fat.value=decodeURIComponent(\''.rawurlencode($row['sat_fat']).'\');
				document.edit_ingredient.ingredient_protein.value=decodeURIComponent(\''.rawurlencode($row['protein']).'\');
				document.edit_ingredient.ingredient_fibre.value=decodeURIComponent(\''.rawurlencode($row['fibre']).'\');
				document.edit_ingredient.ingredient_sodium.value=decodeURIComponent(\''.rawurlencode($row['sodium']).'\');
				document.edit_ingredient.ingredient_cholesterol.value=decodeURIComponent(\''.rawurlencode($row['cholesterol']).'\');
				document.edit_ingredient.ingredient_others.value=decodeURIComponent(\''.rawurlencode($row['others']).'\');
				document.edit_ingredient.ingredient_id.value=decodeURIComponent(\''.rawurlencode($row['id']).'\');
				$(\'#dialog_edit\').dialog(\'open\');
			">(edit)</a>
			<a href="#" onclick="
				document.delete_ingredient.ingredient_name.value=decodeURIComponent(\''.rawurlencode($row['name']).'\');
				document.delete_ingredient.ingredient_id.value=decodeURIComponent(\''.rawurlencode($row['id']).'\');
				document.delete_ingredient.submit();
			">(delete)</a></td>';
			echo '<td class="center">'.$row['qty'].' '.$row['unit'].'</td>';
			/*echo '<td class="center">'.$row['unit'].'</td>'; */
			echo '<td class="center">'.$row['kcal'].'</td>';
			echo '<td class="center">'.$row['carb'].'</td>';
			echo '<td class="center">'.$row['sugar'].'</td>';
			echo '<td class="center">'.$row['fibre'].'</td>';
			echo '<td class="center">'.$row['protein'].'</td>';
			echo '<td class="center">'.$row['fat'].'</td>';
			echo '<td class="center">'.$row['sat_fat'].'</td>';
			echo '<td class="center">'.$row['sodium'].'</td>';
			echo '<td class="center">'.$row['cholesterol'].'</td>';
			echo '</tr>';
		}
	?>

	</tbody> 
	<tfoot> 
		<tr> 
			<th>Ingredient</th> 
			<th>Quantity</th> 
			<!--<th>Unit</th> -->
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



	</body>

</html>
