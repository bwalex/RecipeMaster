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
TODO: font? http://en.wikipedia.org/wiki/Droid_(font)
TODO: chart
TODO: login
TODO: load jquery via google APIs CDN
TODO: use jqueryUI-only-necessary
TODO: add serves N field
TODO: add time estimate field
TODO: add copy button

-- misc: apply to all files --
TODO: Ketchup validation
TODO: tesupload or so
TODO: config file for db and thumb size, paths, etc
TODO: replace jquery ui dialog with jquery tools overlay thingie?
TODO: ajaxify show ingredients
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
    
    <script type="text/javascript" src="highslide/highslide-with-gallery.min.js">
    </script>
    <script type="text/javascript" src="highslide/highslide.config.js" charset="utf-8">
    </script>
    <link rel="stylesheet" type="text/css" href="highslide/highslide.css">
    <!--[if lt IE 7]>
	<link rel="stylesheet" type="text/css" href="highslide/highslide-ie6.css" />
    <![endif]-->

<!--
    <script type="text/javascript" src="fancybox/jquery.fancybox-1.3.4.pack.js"></script>
    <link rel="stylesheet" href="fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />
-->
    
    <script type="text/javascript" language="javascript" src="js/jquery.dataTables.js">
</script>
    <script type="text/javascript" src="ckeditor/ckeditor.js">
</script>
    <script type="text/javascript" src="js/recipes.js">
    </script>
    <script type="text/javascript">
	    hs.showCredits = false;
	    hs.zIndexCounter = 2000;
	    $(document).ready(function() {
		CKEDITOR.config.toolbar =
		[
		    ['Source','-','Preview','-','Templates'],
		    ['Cut','Copy','Paste','PasteText','PasteFromWord','-','SpellChecker', 'Scayt'],
		    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
		    '/',
		    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
		    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		    ['BidiLtr', 'BidiRtl' ],
		    ['Link','Unlink','Anchor'],
		    ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
		    '/',
		    ['Styles','Format','Font','FontSize'],
		    ['TextColor','BGColor'],
		    ['Maximize', 'ShowBlocks','-','About']
		];
	    });	    $(function() {
		$('#recipe_data').dataTable({
		    //"bJQueryUI": true,
		    "sPaginationType": "full_numbers",
		    "bServerSide": true,
		    "bProcessing": true,
		    "sAjaxSource": 'ajax_recipes.php',
		    "aoColumnDefs": [
			{ "aTargets": [ 0 ], "sWidth": '200px' }
		    ]
		});

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
		    deleteallingredients('add_recipe', 'ingredient_add_inputs');
		    deleteallphotos('add_recipe', 'photo_add_inputs');
		    document.add_recipe.recipe_id.value = '-1';
		    document.add_recipe.form_type.value = 'add_recipe';
		    document.add_recipe.recipe_name.value = '';
		    document.add_recipe.recipe_instructions.value = '';
		    CKEDITOR.instances.add_instructions_editor.setData('',
			function() {
			    this.checkDirty(); // true
		    });

		    $('#dialog').dialog('option', {
			title: 'Add a recipe',
			autoOpen: false,
			width: 800,
			buttons: {
			    "Add Recipe": function() {
				document.add_recipe.submit();
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
	    <h1>Recipes<a href="#" class="boring" id="dialog_link" name="dialog_link"><img class="boring" src="icons/add.png" width="16" height="16" alt="Add Recipe"></a></h1>
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

			if ($_POST['recipe_id']) {
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
						if (!empty($_POST['ing_name'])) {
						    foreach ($_POST['ing_name'] as $key => $ingredient_name) {
							    if (empty($ingredient_name))
								continue;
							    $ingredient_id = -1;
							    $ingredient_qty = $_POST['ing_qty'][$key];
							    $ingredient_unit = $_POST['ing_unit'][$key];
							    $method = $_POST['ing_method'][$key];
							    
							    $recipe->addIngredient($ingredient_qty, $ingredient_unit,
								$method, $ingredient_id, $ingredient_name);
						    }
						    /* XXX: maybe throw error */
						}

						$n = $recipe->save();

						if (!empty($_FILES['recipe_photo']['tmp_name'])) {
						    foreach ($_FILES['recipe_photo']['tmp_name'] as $key => $file) {
							$photo = new Photo("recipe", -1, $recipe->id, $_POST['photo_caption'][$key], $file);
							$m = $photo->store();
							if ($m == 0) {
							    $types = '';
							    foreach($photo->mime_types as $mime) {
								$types .= $mime . ', ';
							    }
							    $types = substr_replace( $types, "", -2 );
							    print_error("Error processing image '".$_FILES['recipe_photo']['name'][$key]."', supported image types are: ".$types);
							}
						    }
						}
						if ($n > 0)
							print_msg('Successfully added recipe '.$recipe_name);
						print_msg("Rows affected: ".($n + $m)."<br/>");
					} else if ($form_type == "edit_recipe") {
						if (!empty($_POST['ing_name'])) {
						    foreach ($_POST['ing_name'] as $key => $ingredient_name) {
							    if (empty($ingredient_name))
								continue;
							    $ingredient_id = -1;
							    $ingredient_qty = $_POST['ing_qty'][$key];
							    $ingredient_unit = $_POST['ing_unit'][$key];
							    $method = $_POST['ing_method'][$key];
							    
							    $recipe->addIngredient($ingredient_qty, $ingredient_unit,
								$method, $ingredient_id, $ingredient_name);
						    }
						    /* XXX: maybe throw error? */
						}
						$n = $recipe->save(1);

						$keep = array();
						if (!empty($_POST['photo_id'])) {
						    foreach ($_POST['photo_id'] as $key => $photo_id) {
							if ($photo_id >= 0) {
							    /* Edit caption and keep */
							    $photo = new Photo("recipe", $photo_id);
							    $photo->updateCaption($_POST['photo_caption'][$key]);
							    array_push($keep, $photo_id);
							}
						    }
						    delete_photos("recipe", $recipe->id, $keep);
    
						    /* No, we don't do this in the same loop as to avoid deleting the new photos whose ids we don't have at that point */
						    $file_no = 0;
						    foreach ($_POST['photo_id'] as $key => $photo_id) {
							if ($photo_id == -1) {
							    /* new photo */
							    $photo = new Photo("recipe", -1, $recipe->id, $_POST['photo_caption'][$key], $_FILES['recipe_photo']['tmp_name'][$file_no++]);
							    $m = $photo->store();
							    if ($m == 0) {
								$types = '';
								foreach($photo->mime_types as $mime) {
								    $types .= $mime . ', ';
								}
								$types = substr_replace( $types, "", -2 );
								print_error("Error processing image '".$_FILES['recipe_photo']['name'][$file_no-1]."', supported image types are: ".$types);
							    }
							}
						    }
						}

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
	    <form name="add_recipe" id="add_recipe" action="recipes.php" method="POST" enctype="multipart/form-data">
		<label for="add_recipe_name">Recipe Name:</label><br>
		<input type="text" name="recipe_name" id="add_recipe_name" size="100"> <!-- This <div> holds alert messages to be display in the sample page. -->
		<br>
		<hr>
		List of ingredients:

		<div id="ingredient_add_inputs"></div>
		<a class="boring" href="#" onclick="addingredient('add_recipe', 'ingredient_add_inputs', null, '100', 'g', '', 'method (e.g. diced)');"><img class="boring" src="icons/add.png" width="16" height="16" alt="add ingredient field"></a><br>
		<hr>

		<p><label for="add_instructions_editor">Instructions:</label><br>
		<textarea class="ckeditor" cols="80" id="add_instructions_editor" name="recipe_instructions" rows="10">dummy</textarea></p>

		<hr>
		List of photos:

		<div id="photo_add_inputs"></div>
		<a class="boring" href="#" onclick="addphoto('add_recipe', 'photo_add_inputs', null, '-1', '', '', 'Description');"><img class="boring" src="icons/add.png" width="16" height="16" alt="add ingredient field"></a><br>
		<input type="hidden" name="form_type" value="add_recipe">
		<input type="hidden" name="ingredient_count" value="0">
		<input type="hidden" name="recipe_id" value="-1">
	    </form>
	</div>

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
		<tbody>
		    <tr><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td></tr>
		</tbody>
	    </table>
	</div>
    </div>

    <div class="spacer container_16"></div>
    <?php print_footer(); ?>
</body>
</html>
