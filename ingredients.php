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
	var oTable;
	var fields = [ 'name', 'qty', 'unit', 'typical_qty', 'typical_unit', 'kcal', 'carb',
		      'sugar', 'fibre', 'fat', 'sat_fat', 'protein', 'sodium',
		      'cholesterol', 'others', 'id' ];

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

		/* Populate form */
		for (var i in fields) {
		    document.add_ingredient['ingredient_' + fields[i]].value = ingredient[fields[i]];
		}
		document.add_ingredient.form_type.value = 'edit_ingredient';

		$('#dialog').dialog('option',
		{
		    title: 'Edit ingredient',
		    autoOpen: false,
		    modal: true,
		    width: 600,
		    buttons: [
			{
			    id: "dialog-submit",
			    text: "Submit changes",
			    click: function() {
				$ret = $(document.add_ingredient).submit();
				if ($ret == true)
				    $(this).dialog("close");
			    }
			}
		    ]
		});

		clearmsgdiv(document.getElementById('dialog-messages'));
		$('#dialog').dialog('open');
	    },
	    'json');
	}

	function deleteingredient(id) {
	    document.delete_ingredient.ingredient_id.value = id;
	    $(document.delete_ingredient).submit();
	}

	function printmsgdiv(container, msg, className) {
	    var widget = document.createElement('div');
	    widget.className = className;
	    
	    var p = document.createElement('p');
	    var text = document.createTextNode(msg);
	    p.appendChild(text);
	    widget.appendChild(p);
    
	    container.appendChild(widget);
	}

	function clearmsgdiv(elem) {
	    while (elem.hasChildNodes()) {
		elem.removeChild(elem.firstChild);
	    }
	}

	$(function() {

	    $('form').submit(function() {
		clearmsgdiv(document.getElementById('global-messages'));
		clearmsgdiv(document.getElementById('dialog-messages'));

		$("#dialog-submit").button("disable");
		$.ajax({
		    type: "POST",
		    timeout: 30000, /* in ms */
		    url: "ajax_form_ingredients.php",
		    dataType: "json",
		    data: $(this).serialize(),
		    success: function(data) {
			if (data.error == 0) {
			    for (var i in data.msg) {
				printmsgdiv(document.getElementById('global-messages'), data.msg[i], 'form-ok');
			    }

			    $('#dialog').dialog('close');
			} else {
			    for (var i in data.errmsg) {
				printmsgdiv(document.getElementById(data.where), data.errmsg[i], 'form-error');
			    }
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
		    opacity: 0.7
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
		width: 600,
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
		/* Populate (or rather clear) form */
		for (var i in fields) {
		    document.add_ingredient['ingredient_' + fields[i]].value = '';
		}
		document.add_ingredient.ingredient_id.value = '-1';
		document.add_ingredient.form_type.value = 'add_ingredient';
		
		$('#dialog').dialog('option', {
		    title: 'Add an ingredient',
		    autoOpen: false,
		    width: 600,
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

		clearmsgdiv(document.getElementById('dialog-messages'));
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
	
	<div id="global-messages"></div>

	<form name="delete_ingredient" action="ingredients.php" method="post" id="delete_ingredient">
	    <input type="hidden" name="ingredient_name" value="">
	    <input type="hidden" name="ingredient_id" value="-1">
	    <input type="hidden" name="form_type" value="delete_ingredient">
	</form>

	<!-- ui-dialog -->
	<div id="dialog" title="Add an ingredient">
	    <div id="dialog-messages"></div>
	    <form name="add_ingredient" action="ingredients.php" method="post" id="add_ingredient">
		<div class="row">
		    <span class="left">
			<span class="label">
			    <label for="ingredient_name">Name:</label>
			</span>
			<span class="formw">
			    <input type="text" name="ingredient_name" id="ingredient_name">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">
			    <label for="ingredient_qty">Qty:</label>
			</span>
			<span class="formw">
			    <input size="5" type="text" name="ingredient_qty" id="ingredient_qty" class="has_tooltip" title="The quantity of this ingredient that the nutritional information is for">
			    <select name="ingredient_unit" id="ingredient_unit">
				<option>&nbsp;</option><option>g</option><option>ml</option><option>mg</option><option>kg</option><option>l</option>
			    </select>
			</span>
		    </span>
		</div>
		
		<div class="row">
		    <span class="left">
			<span class="label">
			    <label for="ingredient_typical_qty">Typical unit weight:</label>
			</span>
			<span class="formw">
			    <input size="5" type="text" name="ingredient_typical_qty" id="ingredient_typical_qty">
			    <select name="ingredient_typical_unit" id="ingredient_typical_unit" class="has_tooltip" title="The typical weight of one unit of this ingredient (i.e. the typical weight of 1 tomato)">
				<option>g</option><option>mg</option><option>kg</option>
			    </select>
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">
			    <label for="ingredient_kcal">kcal:</label>
			</span>
			<span class="formw">
			    <input type="text" name="ingredient_kcal" id="ingredient_kcal">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">
			    <label for="ingredient_carb">Carbohydrates (g):</label>
			</span>
			<span class="formw">
			    <input type="text" name="ingredient_carb" id="ingredient_carb">
			</span>
		    </span>
		    <span class="right">
			<span class="label">
			    <label for="ingredient_sugar">of which sugars (g):</label>
			</span>
			<span class="formw">
			    <input type="text" name="ingredient_sugar" id="ingredient_sugar">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">
			    <label for="ingredient_fat">Fat (g):</label>
			</span>
			<span class="formw">
			    <input type="text" name="ingredient_fat" id="ingredient_fat">
			</span>
		    </span>
		    <span class="right">
			<span class="label">
			    <label for="ingredient_sat_fat">of which saturates (g):</label>
			</span>
			<span class="formw">
			    <input type="text" name="ingredient_sat_fat" id="ingredient_sat_fat">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">
			    <label for="ingredient_protein">Protein (g):</label>
			</span>
			<span class="formw">
			    <input type="text" name="ingredient_protein" id="ingredient_protein">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">
			    <label for="ingredient_fibre">Fibre (g):</label>
			</span>
			<span class="formw">
			    <input type="text" name="ingredient_fibre" id="ingredient_fibre">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">
			    <label for="ingredient_sodium">Sodium (mg):</label>
			</span>
			<span class="formw">
			    <input type="text" name="ingredient_sodium" id="ingredient_sodium">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="left">
			<span class="label">
			    <label for="ingredient_cholesterol">Cholesterol (mg):</label>
			</span>
			<span class="formw">
			    <input type="text" name="ingredient_cholesterol" id="ingredient_cholesterol">
			</span>
		    </span>
		</div>

		<div class="row">
		    <span class="labelfull">
			<label for="ingredient_others">Others (foo=bar, moh=meh):</label>
		    </span>
		    <span class="formfull">
			<textarea cols="100" rows="3" style="width: 90%;" name="ingredient_others" id="ingredient_others"></textarea>
		    </span>
		</div>

		<input type="hidden" name="ingredient_id" value="-1">
		<input type="hidden" name="form_type" value="add_ingredient">
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
