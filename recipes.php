<?php
include('functions.php');
?>

<!--
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
-->
<!DOCTYPE HTML>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="generator" content="HTML Tidy for Windows (vers 11 August 2008), see www.w3.org" />
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" >

    <title>Recipes : RecipeMaster</title>
    <style type="text/css" title="currentStyle">
	    @import "css/demo_table.css";
    </style>
    
    
    <link type="text/css" href="css/style.css" rel="stylesheet"/>
    <link type="text/css" href="css/ui-lightness/jquery-ui-1.8.7.custom.css" rel="stylesheet"/>
    <script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.7.custom.min.js"></script>
    <script type="text/javascript" src="js/jquery.tools.min.js"></script>

<?php
    printExtraHeaders();
?>

    <script type="text/javascript" language="javascript" src="js/jquery.dataTables.js"></script>
    <script type="text/javascript" language="javascript" src="js/jquery.jeditable.mini.js"></script>

    <link rel="stylesheet" type="text/css" href="css/jquery.confirm.css" />
    <script type="text/javascript" language="javascript" src="js/jquery.confirm.js"></script>

    <script type="text/javascript" src="js/recipes.js"></script>
    <script type="text/javascript">
    //<![CDATA[
	var oTable;
	var api;
	var recipeId = -1;
	var RMConfig = {
	    photoViewer : "<?php echo $globalConfig['photo']['Viewer'] ?>"
	}
	hs.showCredits = false;
	hs.zIndexCounter = 2000;

	function printMsgs(data, type) {
	    if (type == 'ok') {
		msgSet = data.msg;
		className = 'form-ok';
		where = data.whereOk;
	    } else if (type == 'error') {
		msgSet = data.errmsg;
		className = 'form-error';
		where = data.whereError;
	    } else {
		return;
	    }

	    for (var i in msgSet) {
		$('.' + where).append('<div class="' + className + '"><p>' + msgSet[i] + '</p></div>');
	    }
	}

	function clearMsgDivs() {
	    $('.dialog-messages, .global-messages').empty();
	}

	$(document).ready(function() {
	    //CKEDITOR.config.toolbar = [['Source', '-', 'Preview', '-', 'Templates'], ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'SpellChecker', 'Scayt'], ['Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat'], '/', ['Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript'], ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote', 'CreateDiv'], ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'], ['BidiLtr', 'BidiRtl'], ['Link', 'Unlink', 'Anchor'], ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak'], '/', ['Styles', 'Format', 'Font', 'FontSize'], ['TextColor', 'BGColor'], ['Maximize', 'ShowBlocks', '-', 'About']];
	});

	$(function() {
	    $('form').submit(function() {
		clearMsgDivs();

		$("#dialog-submit").button("disable");
		$.ajax({
		    type: "POST",
		    timeout: 30000,
		    /* in ms */
		    url: this.action/* "ajax_form_recipes.php" */,
		    dataType: "json",
		    data: $(this).serialize(),
		    success: function(data) {
			if (data.error == 0) {
			    printMsgs(data, 'ok');

			    if ((data.type == 'add_recipe') || (data.type == 'copy_recipe')) {
				recipeId = data.id;
				$('#dialog').dialog('close');
				location.href = "show_recipe.php?recipe_id="+recipeId;
			    }
			} else {
			    recipeId = -1;
			    printMsgs(data, 'error');
			}

			/* Refresh table */
			oTable.fnDraw();
		    },
		    error: function(req, textstatus) {
			alert('Request failed: ' + textstatus);
		    }
		});
		$("#dialog-submit").button("enable");
		return false;
	    });

	    oTable = $('#recipe_data').dataTable({
		//"bJQueryUI": true,
		"sPaginationType": "full_numbers",
		//"bServerSide": true, /* XXX: temporary workaround, since ordering is broken on serverside */
		"bProcessing": true,
		"sAjaxSource": 'ajax_recipes.php',
		"aoColumnDefs": [{
		    "aTargets": [0],
		    "sWidth": '200px'
		},
		{
		    "aTargets": [10],
		    "sWidth": '100px'
		}]
	    });

	    // Dialog

	    $('#dialog').dialog({
		autoOpen: false,
		width: 625,
	    });

	    // Dialog Link
	    $('#dialog_link').click(function() {
		recipeId = -1;
		$('#ingredient_add_inputs').empty();
		$('#photo_add_inputs').empty();
		document.add_recipe.recipe_id.value = '-1';
		document.add_recipe.recipe_name.value = '';

		$('#dialog').dialog('option', {
		    title: 'Add a recipe',
		    modal: true,
		    autoOpen: false,
		    buttons: [
			{
			    id: "dialog-submit",
			    text: "Add Recipe",
			    click: function() {
				recipeId = -1;
				$ret = $(document.add_recipe).submit();
				if ($ret == true)
				    $(this).dialog("close");
			    }
			}
		    ]
		});
		clearMsgDivs();
		$('#dialog').dialog('open');
		return false;
	    });

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
    //]]>
    </style>
</head>

<body>
    <?php print_header(); ?>
    <div class="spacer container_16"></div>

    <div id="content" class="container_16">
	<div class="container_16">
	    <h1>Recipes<a href="#" class="boring" id="dialog_link" name="dialog_link"><img class="boring" src="icons/add.png" width="16" height="16" alt="Add Recipe"/></a></h1>
	</div>

	<div class="global-messages"></div>

	<form name="delete_recipe" action="ajax_form_recipes.php" method="post" id="delete_recipe">
	    <input type="hidden" name="recipe_name" value=""/>
	    <input type="hidden" name="recipe_id" value="-1"/>
	    <input type="hidden" name="form_type" value="delete_recipe"/>
	    <input type="hidden" name="where_ok" value="global-messages"/>
	    <input type="hidden" name="where_error" value="global-messages"/>
	</form>
	<form name="copy_recipe" action="ajax_form_recipes.php" method="post" id="copy_recipe">
	    <input type="hidden" name="recipe_name" value=""/>
	    <input type="hidden" name="recipe_id" value="-1"/>
	    <input type="hidden" name="form_type" value="copy_recipe"/>
	    <input type="hidden" name="where_ok" value="global-messages"/>
	    <input type="hidden" name="where_error" value="global-messages"/>
	</form>

	<div id="dialog" title="Add a recipe">
	    <div class="dialog-messages"></div>

	    <form name="add_recipe" id="add_recipe" action="ajax_form_recipes.php" method="post" enctype="multipart/form-data">
		<div class="row">
		    <label for="add_recipe_name">Recipe Name:</label>
		</div>

		<div class="row">
		    <input type="text" name="recipe_name" id="add_recipe_name" style="width: 600px;"/>
		</div>

		<input type="hidden" name="form_type" value="add_recipe"/>
		<input type="hidden" name="ingredient_count" value="0"/>
		<input type="hidden" name="recipe_id" value="-1"/>
		<input type="hidden" name="where_ok" value="global-messages"/>
		<input type="hidden" name="where_error" value="dialog-messages"/>
	    </form>
	</div>


	<div id="demo">
	    <table class="tdatatable display" id="recipe_data">
		<thead>
		    <tr>
			<th>Recipe</th>

			<th>kcal</th>

			<th>Carbs (g)</th>

			<th>Sugars (g)</th>

			<th>Fibre (g)</th>

			<th>Protein (g)</th>

			<th>Total Fat (g)</th>

			<th>Sat. Fat (g)</th>

			<th>Sodium (mg)</th>

			<th>Cholesterol (mg)</th>

			<th>Last modified</th><!--<th>Unit</th> -->
		    </tr>
		</thead>

		<tfoot>
		    <tr>
			<th>Recipe</th>

			<th>kcal</th>

			<th>Carbs (g)</th>

			<th>Sugars (g)</th>

			<th>Fibre (g)</th>

			<th>Protein (g)</th>

			<th>Total Fat (g)</th>

			<th>Sat. Fat (g)</th>

			<th>Sodium (mg)</th>

			<th>Cholesterol (mg)</th>

			<th>Last modified</th><!--<th>Unit</th> -->
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
