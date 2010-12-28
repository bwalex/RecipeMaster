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

<!DOCTYPE html>

<!--
TODO: copy recipe
TODO: split ingredients into two columns
TODO: http://www.flickr.com/photos/87116893@N00/5292842186/ http://www.flickr.com/photos/87116893@N00/5292842186/sizes/o/in/photostream/
TODO: add photo stuff
-->

<html>

	<head>

		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Show Recipe : RecipeMaster</title>
        
		<style type="text/css" title="currentStyle"> 
			@import "css/demo_table.css";
		</style> 

		<script type="text/javascript" src="highslide/highslide-with-gallery.js"></script>
		<script type="text/javascript" src="highslide/highslide.config.js" charset="utf-8"></script>
		<link rel="stylesheet" type="text/css" href="highslide/highslide.css" />
		<!--[if lt IE 7]>
		<link rel="stylesheet" type="text/css" href="highslide/highslide-ie6.css" />
		<![endif]-->

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
			//ACCORDION BUTTON ACTION	
			$(document).ready(function()
			{

			});
			$(function(){
				  //hide the all of the element with class msg_body
				  $("#acc_content").hide();
				  //toggle the componenet with class msg_body
				  $("#acc_head").click(function()
				  {
				    $(this).next("#acc_content").slideToggle(100);
				  });
  
				$('#ingredients_data').dataTable();
				
				// Accordion
				//$("#accordion").accordion({ header: "h3" });

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

if ($_GET['recipe_id']) {
	try {
		$recipe = new Recipe($_GET['recipe_id']);
		$info = $recipe->getNutriInfo();
		echo '<h1>'.$recipe->name.'</h1>';
		echo '<span style="float:right;">';
		echo '<img alt="Nutritional Information Label" src="nutrilabel.php?carbs='.$info['carb'].'&protein='.$info['protein'].'&fat='.$info['fat'].'&sat_fat='.$info['sat_fat'].'&calories='.$info['kcal'].'&cholesterol='.$info['cholesterol'].'&sodium='.$info['sodium'].'&fibre='.$info['fibre'].'&sugars='.$info['sugar'].'"/>';
		echo '</span>';
		echo '<h2>Ingredients</h2>';
		
		//echo '<h2 id="acc_head"><a style="text-decoration: none; color: #000000;" href="#">Ingredients</a></h2>';
		//echo '<div id ="acc_content">';
		
		echo '<div style="width: 520px;">';
		$i = 0;
		foreach ($recipe->ingredients as $ingredient) {
			if ($i % 2 == 0) {
				echo '<div class="row">';
				$class = "leftfixed";
			} else {
				$class = "rightfixed";
			}
			echo '<span class="'.$class.'">'.$ingredient['qty'].$ingredient['unit'].' '.$ingredient['Ingredient']->name.' ('.$ingredient['method'].')</span>';
			
			if ($i % 2 != 0)
				echo '</div>';
			$i++;
		}
		echo '</div>';
		
		echo '<h2 style="clear: left; padding-top: 15px;">Preparation</h2>';
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



<div class="highslide-gallery" style="clear: both;">
<h2>Photos</h2>
	<ul>
	<li>
	<a href="highslide/sample-images/thumbstrip01.jpg" class="highslide" 
			title="Caption from the anchor's title attribute" 
			onclick="return hs.expand(this, config1 )">
		<img src="highslide/sample-images/thumbstrip01.thumb.jpg"  alt=""/>
	</a>
	</li>
	<li>
	<a href="highslide/sample-images/thumbstrip02.jpg" class="highslide" 
			title="Caption from the anchor's title attribute" 
			onclick="return hs.expand(this, config1 )">
		<img src="highslide/sample-images/thumbstrip02.thumb.jpg"  alt=""/>
	</a>
	</li>
	<li>
	<a href="highslide/sample-images/thumbstrip12.jpg" class="highslide" 
			title="Caption from the anchor's title attribute" 
			onclick="return hs.expand(this, config1 )">
		<img src="highslide/sample-images/thumbstrip12.thumb.jpg"  alt=""/>
	</a>
	</li>
	<li>
	<a href="highslide/sample-images/thumbstrip07.jpg" class="highslide" 
			title="Caption from the anchor's title attribute" 
			onclick="return hs.expand(this, config1 )">
		<img src="highslide/sample-images/thumbstrip07.thumb.jpg"  alt=""/>
	</a>
	</li>
	<li>
	<a href="highslide/sample-images/thumbstrip22.jpg" class="highslide" 
			title="Caption from the anchor's title attribute" 
			onclick="return hs.expand(this, config1 )">
		<img src="highslide/sample-images/thumbstrip22.thumb.jpg"  alt=""/>
	</a>
	</li>
	<li>
	<a href="highslide/sample-images/thumbstrip24.jpg" class="highslide" 
			title="Caption from the anchor's title attribute" 
			onclick="return hs.expand(this, config1 )">
		<img src="highslide/sample-images/thumbstrip24.thumb.jpg"  alt=""/>
	</a>
	</li>
	<li>
	<a href="highslide/sample-images/thumbstrip03.jpg" class="highslide" 
			title="Caption from the anchor's title attribute" 
			onclick="return hs.expand(this, config1 )">
		<img src="highslide/sample-images/thumbstrip03.thumb.jpg"  alt=""/>
	</a>
	</li>
	<li>
	<a href="highslide/sample-images/thumbstrip05.jpg" class="highslide" 
			title="Caption from the anchor's title attribute" 
			onclick="return hs.expand(this, config1 )">
		<img src="highslide/sample-images/thumbstrip05.thumb.jpg"  alt=""/>
	</a>
	</li>
	<li>
	<a href="highslide/sample-images/thumbstrip06.jpg" class="highslide" 
			title="Caption from the anchor's title attribute" 
			onclick="return hs.expand(this, config1 )">
		<img src="highslide/sample-images/thumbstrip06.thumb.jpg"  alt=""/>
	</a>
	</li>
	<li>
	<a href="highslide/sample-images/thumbstrip08.jpg" class="highslide" 
			title="Caption from the anchor's title attribute" 
			onclick="return hs.expand(this, config1 )">
		<img src="highslide/sample-images/thumbstrip08.thumb.jpg"  alt=""/>
	</a>
	</li>
	</ul>
	<div style="clear:both"></div></div>



	<A name="detailednutri">.</A>
	<h4 id="acc_head"><a style="text-decoration: none; color: #000000;" href="#detailednutri">Detailed nutritional analysis</a></h4>
	<div id ="acc_content">
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
	<tbody>
    <?php
if ($_GET['recipe_id']) {
	try {
		$recipe = new Recipe($_GET['recipe_id']);
		$i = 0;
		foreach ($recipe->ingredients as $ingredient) {
			$i = !$i;
			if ($i)
				echo '<tr class="even">';
			else
				echo '<tr class="odd">';
			
			$nutri_info = $ingredient['Ingredient']->getNutriInfo($ingredient['qty'], $ingredient['unit']);

			echo '<td>'.$ingredient['Ingredient']->name.'</td>';
			echo '<td class="center">'.$ingredient['qty'].' '.$ingredient['unit'].'</td>';
			/*echo '<td class="center">'.$ing->unit.'</td>'; */
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
	} catch (Exception $e) {
		print_error('Exception: '.$e->getMessage());
	}
}
	?>


	</tbody> 
</table> 
	</div>
</div>

</div>

<div id="footer">
<span style="margin-top: 10px; float: left;">
    <a href="http://validator.w3.org/check?uri=referer"><img
        src="http://www.w3.org/Icons/valid-xhtml10"
        alt="Valid XHTML 1.0 Transitional" height="31" width="88" /></a>
  </span>
	<h4>&copy; 2010, Alex Hornung</h4>
</div>
	</body>

</html>
