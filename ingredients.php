<?php
include('functions.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--
TODO: add a show ingredient page, possibly with some photos, etc
TODO: add per-ingredient custom units (i.e. 1 glass) (XXX: probably not)
TODO: add optional ingredients as dynamic list of extras, like ingredients in a recipe
 -->
 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=us-ascii"/>

    <title>Ingredients : RecipeMaster</title>
    <style type="text/css" title="currentStyle">

			@import "css/demo_table.css";
    </style>
    <link type="text/css" href="css/style.css" rel="stylesheet"/>
    <link type="text/css" href="css/ui-lightness/jquery-ui-1.8.7.custom.css" rel="stylesheet"/>
    <script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
    <script type="text/javascript" src="js/jquery.tools.min.js"></script>
    
    
    
    
    
    <script type="text/javascript" src="js/jquery-ui-1.8.7.custom.min.js"></script>
    <script type="text/javascript" language="javascript" src="js/jquery.dataTables.js"></script>

    <link rel="stylesheet" type="text/css" href="css/jquery.confirm.css" />
    <script type="text/javascript" language="javascript" src="js/jquery.confirm.js"></script>

    <script type="text/javascript">
    //<![CDATA[
	var oTable;
	var fields = [ 'name', 'qty', 'unit', 'typical_qty', 'typical_unit', 'kcal', 'carb',
		      'sugar', 'fibre', 'fat', 'sat_fat', 'protein', 'sodium',
		      'cholesterol', 'others', 'id' ];

	function addtooltip(input) {
	    var img = $('<img alt="?" height="16" width="16" src="icons/help.png" class="boring">');
	    img.mouseover(function() {
		$(input).data('tooltip').show();
	    });
	    img.mouseout(function() {
		$(input).data('tooltip').hide();
	    });
	    $(input).parent().append(img);
	}


	function deleteingredient(id) {
	    $.confirm({
		    'title'		: 'Delete Confirmation',
		    'message'	: 'You are about to delete this item. <br />It cannot be restored at a later time! Continue?',
		    'buttons'	: {
			    'Yes' : {
				    'class'	: 'blue',
				    'action': function(){
					document.delete_ingredient.ingredient_id.value = id;
					$(document.delete_ingredient).submit();
				    }
			    },
			    'No' : {
				    'class'	: 'gray',
				    'action': function(){}	// Nothing to do in this case. You can as well omit the action property.
			    }
		    }
	    });
	}

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

	$(function() {

	    $('form').submit(function() {
		clearMsgDivs();

		$("#dialog-submit").button("disable");
		$.ajax({
		    type: "POST",
		    timeout: 30000, /* in ms */
		    url: "ajax_form_ingredients.php",
		    dataType: "json",
		    data: $(this).serialize(),
		    success: function(data) {
			if (data.error == 0) {
			    printMsgs(data, 'ok');
			    if (data.type == 'add_ingredient') {
				$('#dialog').dialog('close');
				location.href = "show_ingredient.php?ingredient_id="+data.id;
			    }
			} else {
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
		    opacity: 0.9
	    });

	    // Add tooltips
	    $('.has_tooltip').each(function() {
		addtooltip(this);
	    });

	    oTable = $('#ingredients_data').dataTable({
		//"bJQueryUI": true,
		"sPaginationType": "full_numbers",
		"bServerSide": true,
		"bProcessing": true,
		"sAjaxSource": 'ajax_ingredients.php',
		"aoColumnDefs": [
			{ "aTargets": [ 0 ], "sWidth": '200px' }
		    ]
	    });

	    // Dialog                       
	    $('#dialog').dialog({
		autoOpen: false,
		modal: true,
		width: 625,
		buttons: [
		    {
			id: "dialog-submit",
			text: "Add Ingredient",
			click: function() {
			    $ret = $(document.add_ingredient).submit();
			    if ($ret == true)
				$(this).dialog("close");
			}
		    }
		]
	    });

	    // Dialog Link
	    $('#dialog_link').click(function() {

		document.add_ingredient.ingredient_id.value = '-1';
		document.add_ingredient.form_type.value = 'add_ingredient';
		
		$('#dialog').dialog('option', {
		    title: 'Add an ingredient',
		    autoOpen: false,
		    width: 625,
		    buttons: [
			{
			    id: "dialog-submit",
			    text: "Add Ingredient",
			    click: function() {
				$ret = $(document.add_ingredient).submit();
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
    //<![CDATA[
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
	    <h1>Ingredients<a class="boring" href="#" id="dialog_link" name="dialog_link"><img class="boring" src="icons/add.png" width="16" height="16" alt="Add Ingredient"/></a></h1>
	</div>
	
	<div class="global-messages"></div>

	<form name="delete_ingredient" action="ingredients.php" method="post" id="delete_ingredient">
	    <input type="hidden" name="ingredient_name" value=""/>
	    <input type="hidden" name="ingredient_id" value="-1"/>
	    <input type="hidden" name="form_type" value="delete_ingredient"/>
	    <input type="hidden" name="where_ok" value="global-messages"/>
	    <input type="hidden" name="where_error" value="global-messages"/>
	</form>

	<!-- ui-dialog -->
	<div id="dialog" title="Add an ingredient">
	    <div class="dialog-messages"></div>
	    <form name="add_ingredient" action="ingredients.php" method="post" id="add_ingredient">
		<div class="row">
		    <label for="ingredient_name">Ingredient Name:</label>
		</div>

		<div class="row">
		    <input type="text" name="ingredient_name" id="ingredient_name" style="width: 600px;"/>
		</div>

		<input type="hidden" name="ingredient_id" value="-1"/>
		<input type="hidden" name="form_type" value="add_ingredient"/>
		<input type="hidden" name="where_ok" value="global-messages"/>
		<input type="hidden" name="where_error" value="dialog-messages"/>
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
