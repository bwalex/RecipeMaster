<?php
include('functions.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">


<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="generator" content="HTML Tidy for Windows (vers 11 August 2008), see www.w3.org" />
    <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />

    <title>Show Ingredient : RecipeMaster</title>
    <style type="text/css" title="currentStyle">
/*<![CDATA[*/
			@import "css/demo_table.css";
    /*]]>*/
    </style>

    <link type="text/css" href="css/style.css" rel="stylesheet"/>
    <link type="text/css" href="css/print.css" rel="stylesheet" media="print"/>
    <link type="text/css" href="css/ui-lightness/jquery-ui-1.8.7.custom.css" rel="stylesheet"/>
    <script type="text/javascript" src="js/jquery-1.4.4.min.js">
</script>
    
    <script type="text/javascript" src="js/jquery-ui-1.8.7.custom.min.js">
</script>
    <script type="text/javascript" src="js/jquery.tools.min.js"></script>

<?php
    printExtraHeaders();
?>

    <script type="text/javascript" language="javascript" src="js/jquery.dataTables.js">
</script>
    <script type="text/javascript" language="javascript" src="js/jquery.jeditable.mini.js">
    </script>

    <script type="text/javascript" src="js/functions.js"></script>
    <script type="text/javascript">
    //<![CDATA[
	var addingRow = 0;
	var ingredientId = <?php echo $_REQUEST['ingredient_id'] ?>;
	var isEditing = 0;
	var isPhotoEditing = 0;
	var origHTML = '';
	var RMConfig = {
	    photoViewer : "<?php echo $globalConfig['photo']['Viewer'] ?>",
	    richEditor : "<?php echo $globalConfig['text']['richEditor'] ?>",
	}


	function populatePage() {
	    $('#loading-screen').data('overlay').load();
	    $.post("ajax_formdata.php", {
		ingredient: ingredientId
	    },
	    function(ingredient) {
		// format and output result
		if (ingredient.exception) {
		    alert(ingredient.exception);
		    return;
		}

		$('#ingredient_photos').empty();
		$('#ingredient_info').empty();
		$('#ingredient_qtyunit').empty();
		$('#ingredient_nutrients').empty();
		$('#ingredient_typical_qtyunit').empty();
		$('#kcal').empty();
		$('#carb').empty();
		$('#sugar').empty();
		$('#fat').empty();
		$('#sat_fat').empty();
		$('#protein').empty();
		$('#fibre').empty();
		$('#sodium').empty();
		$('#cholesterol').empty();

		$('#ingredient_name').text(ingredient.name);
		$('#ingredient_qtyunit').text('Quantity: ' + ingredient.qty + ingredient.unit);
		$('#ingredient_typical_qtyunit').text('Typical unit weight: ' + ingredient.typical_qty + ingredient.typical_unit);
		$('#kcal').text(ingredient.kcal);
		$('#carb').text(ingredient.carb);
		$('#sugar').text(ingredient.sugar);
		$('#fat').text(ingredient.fat);
		$('#sat_fat').text(ingredient.sat_fat);
		$('#protein').text(ingredient.protein);
		$('#fibre').text(ingredient.fibre);
		$('#sodium').text(ingredient.sodium);
		$('#cholesterol').text(ingredient.cholesterol);

		$('#ingredient_photos').append(createPhotoGallery(ingredient.photos, '1'));

		$('#loading-screen').data('overlay').close();
	    },
	    'json');
	}


	$(function() {
	    $('#loading-screen').overlay({
		    top: 260,
		    target: '#loading-screen',
		    mask: {
			    color: '#FFF',
			    loadSpeed: 100,
			    opacity: 0.9
		    },
		    closeOnClick: false,
		    closeOnEsc: false,
		    speed: 100,
		    load: true
	    });
	    populatePage();
	});
    //]]>
    </script>
    <style type="text/css">
    /*<![CDATA[*/
                    /*demo page css*/
                        .demoHeaders { margin-top: 2em; }
                        #dialog_link {padding: .4em 1em .4em 20px;text-decoration: none;position: relative;}
                        #dialog_link span.ui-icon {margin: 0 5px 0 0;position: absolute;left: .2em;top: 50%;margin-top: -8px;}
                        ul#icons {margin: 0; padding: 0;}
                        ul#icons li {margin: 2px; position: relative; padding: 4px 0; cursor: pointer; float: left;  list-style: none;}
                        ul#icons span.ui-icon {float: left; margin: 0 4px;}
    /*]]>*/
    </style>
</head>

<body>
    <?php print_header(); ?>
    <div class="spacer container_16"></div>

    <div id="content" class="container_16">


	<form name="delete_photo" action="ajax_form_photos.php" method="post" id="delete_photo">
	    <input type="hidden" name="ingredient_id" value="-1"/>
	    <input type="hidden" name="photo_id" value="-1"/>
	    <input type="hidden" name="form_type" value="delete_photo"/>
	    <input type="hidden" name="where_ok" value="dialog-messages"/>
	    <input type="hidden" name="where_error" value="dialog-messages"/>
	</form>


	<div class="loading-modal" id="loading-screen">
		<img src="icons/load_circle_huge.gif" alt="Loading..."/>
	</div>


	<div class="container_16">
	    <div class="grid_15">
		<h1 id="ingredient_name" style="margin-bottom: 8px">DUMMY_INGREDIENT_NAME</h1>
	    </div>
	    <div class="grid_1" style="padding-top: 20px">
		<span class="noprint">
		    <a class="boring" href="#" onclick="window.print();" title="Print this page">
			<img class="boring" src="icons/printer.png" alt="Print"/>
		    </a>
		</span>
	    </div>
	</div>

	<div class="container_16 clearfix">
	    <div class="grid_11">
		<h2 style="margin-bottom: 0px;">
		    <span class="editsection">
			<a class="boring editsection" href="#" id="ingredient_qtys_header" onclick="switchIngredientsToEdit();" title="Edit">
			    <img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)"/>
			</a>
		    </span>
		    <span>Quantities</span>
		</h2>
		<div class="row clearfix">
		    <div id="ingredient_qtyunit" class="leftfixed">
			Quantity: 100g
		    </div>

		    <div id="ingredient_typical_qtyunit" class="rightfixed">
			Typical unit weight: 550g
		    </div>
		</div>
	    </div>

	    <div class="grid_11">
		<h2 style="margin-bottom: 0px;">
		    <span class="editsection">
			<a class="boring editsection" href="#" id="ingredient_nutritional_header" onclick="switchIngredientsToEdit();" title="Edit">
			    <img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)"/>
			</a>
		    </span>
		    <span>Nutritional Information</span>
		</h2>

		<div class="row clearfix">
		    <div class="leftfixed">
			<table>
			    <tr>
				<td>Calories:</td>
				<td><span id="kcal">DUMMY</span> kcal</td>
			    </tr>
			    <tr>
				<td>Carbohydrates:</td>
				<td><span id="carb">DUMMY</span>g</td>
			    </tr>
			    <tr>
				<td>Fat:</td>
				<td><span id="fat">DUMMY</span>g</td>
			    </tr>
			    <tr>
				<td>Protein:</td>
				<td><span id="protein">DUMMY</span>g</td>
			    </tr>
			    <tr>
				<td>Fibre:</td>
				<td><span id="fibre">DUMMY</span>g</td>
			    </tr>
			    <tr>
				<td>Sodium:</td>
				<td><span id="sodium">DUMMY</span>mg</td>
			    </tr>
			    <tr>
				<td>Cholesterol:</td>
				<td><span id="cholesterol">DUMMY</span>mg</td>
			    </tr>

			</table>
		    </div>
		    <div class="rightfixed">
			<table>
			    <tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			    </tr>
			    <tr>
				<td>of which sugars:</td>
				<td><span id="sugar">DUMMY</span>g</td>
			    </tr>
			    <tr>
				<td>of which saturated:</td>
				<td><span id="sat_fat">DUMMY</span>g</td>
			    </tr>
			</table>
		    </div>
		</div>

		<h2 style="margin-bottom: 0px;">
		    <span class="editsection">
			<a class="boring editsection" href="#" id="ingredient_additional_nutritional_header" title="Edit">
			    <img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)"/>
			</a>
		    </span>
		    <span>Additional Nutritional Information</span>
		</h2>

		<div id="ingredient_nutrients" class="row clearfix">
		    <div class="leftfixed">
			<table>
			    <tr>
				<td>Vitamin A:</td>
				<td>10mg</td>
			    </tr>
			    <tr>
				<td>Vitamin B12:</td>
				<td>0.01mg</td>
			    </tr>

			</table>
		    </div>
		    <div class="rightfixed">
			<table>
			    <tr>
				<td>Vitamin E:</td>
				<td>0.1mg</td>
			    </tr>
			</table>
		    </div>
		</div>
		
		<h2>
		    <span class="editsection">
			<a class="boring editsection" href="#" id="ingredient_info_header" title="Edit">
			    <img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)"/>
			</a>
		    </span>
		    <span>Information</span>
		</h2>

		<div id="ingredient_info">
		    DUMMY_INGREDIENT_INFO
		</div>
	    </div>

	    <div class="grid_5">
		<div id="ingredient_nutrilabel">
		    DUMMY_INGREDIENT_NUTRI_LABEL
		</div>
	    </div>
	</div>

	<div class="container_16 clearfix">
	    <h2>
	    	<span class="editsection">
		    <a class="boring editsection" href="#" id="ingredient_photo_header" onclick="switchPhotosToEdit();" title="Edit">
		        <img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)"/>
		    </a>
		</span>
		<span>Photos</span>
	    </h2>
	    <div id="ingredient_photos" class="highslide-gallery" style="clear: both;">
		DUMMY_INGREDIENT_PHOTOS
	    </div>
	</div>



    </div>

    <div class="spacer container_16"></div>
    <?php print_footer(); ?>
</body>
</html>
