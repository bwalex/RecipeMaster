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

//ob_start('tidyhtml');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--
TODO: add edit recipe button
TODO: split ingredients into two columns
inspiration: http://www.flickr.com/photos/87116893@N00/5292842186/ http://www.flickr.com/photos/87116893@N00/5292842186/sizes/o/in/photostream/
TODO: add bullet points
TODO: add print stuff
-->

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="generator" content="HTML Tidy for Windows (vers 11 August 2008), see www.w3.org" />
    <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />

    <title>Show Recipe : RecipeMaster</title>
    <style type="text/css" title="currentStyle">
/*<![CDATA[*/
			@import "css/demo_table.css";
    /*]]>*/
    </style>
    <script type="text/javascript" src="highslide/highslide-with-gallery.min.js">
    </script>
    <script type="text/javascript" src="highslide/highslide.config.js" charset="utf-8">
    </script>
    <link rel="stylesheet" type="text/css" href="highslide/highslide.css"/>
    <!--[if lt IE 7]>
	<link rel="stylesheet" type="text/css" href="highslide/highslide-ie6.css" />
    <![endif]-->
    <link type="text/css" href="css/style.css" rel="stylesheet"/>
    <link type="text/css" href="css/ui-lightness/jquery-ui-1.8.7.custom.css" rel="stylesheet"/>
    <script type="text/javascript" src="js/jquery-1.4.4.min.js">
</script>
    <script type="text/javascript" src="http://cdn.jquerytools.org/1.2.5/tiny/jquery.tools.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.7.custom.min.js">
</script>
    <script type="text/javascript" language="javascript" src="js/jquery.dataTables.js">
</script>
    <script type="text/javascript" language="javascript" src="js/jquery.jeditable.mini.js">
    </script>
    <script type="text/javascript" src="ckeditor/ckeditor.js">
</script>
    <script type="text/javascript" src="ckeditor/adapters/jquery.js"></script>
    <script type="text/javascript" src="ckeditor/plugins/save/plugin.js"></script>
    <script type="text/javascript">
    //<![CDATA[
	hs.showCredits = false;
	var oTable = null;
	var addingRow = 0;
	var recipeId = <?php echo $_REQUEST['recipe_id'] ?>;



	function saveChanges() {
	    /* Clear errors */
	    $(oTable.fnGetNodes()).filter('.error-row').each(function() {
		$('td', this).slice(0, 2).each(function() {
		    if ($(this).data('tooltip')) {
			$(this).data('tooltip').hide();
			$(this).data('tooltip', null);
		    }
		});
	    });
	    $(oTable.fnGetNodes()).filter('.error-row').removeClass('error-row');

	    var sendData = {
		"recipe_id": recipeId,
		"adding_row": addingRow,
		"save_changes": 1,
		"column": -1,
		"data": oTable.fnGetData()
	    };
	    addingRow = 0;
	    //console.log(sendData);
	    $.ajax({
		"dataType": 'json',
		"type": "GET",
		"url": "ajax_experiment.php",
		"data": sendData,
		"success": function(data) {
		    //console.log(data);
		    if (data.error == 0) {
			/* Update the table, row by row */
			console.log(data.deletedRows);
			for (var i in data.deletedRows) {
			    oTable.fnDeleteRow(data.deletedRows[i]);
			}
			for (var i in data.aaData) {
			    oTable.fnUpdate(data.aaData[i], i);
			}
		    } else {
			$(oTable.fnGetNodes(data.errorRow)).addClass('error-row');
			errorCol = data.errorCol;
			$('td', oTable.fnGetNodes(data.errorRow)).slice(errorCol, errorCol + 1).attr('title', data.errmsg);
			$('td', oTable.fnGetNodes(data.errorRow)).slice(errorCol, errorCol + 1).tooltip({
			    position: "top center",
			    events: {
				def: ',',
				input: ',',
				widget: ',',
				tooltip: ','

			    },
			    effect: "fade",
			    tipClass: "tooltip-arrow-black",
			    opacity: 1
			});
			$('td', oTable.fnGetNodes(data.errorRow)).slice(errorCol, errorCol + 1).data('tooltip').show();
			//console.log(data.errorRow);
			//console.log(oTable.fnGetNodes(data.errorRow));
		    }

		}
	    });
	}




















	function fnClickAddRow() {
	    addingRow = 0; /* needed since draw callback will refresh data */
	    oTable.fnAddData( ['', '', '', '', '','','','','','','',''] );
	}

	$(function() {
	    
	    
	    $('#recipe_preparation_header').click(function() {
		var html = $('#recipe_preparation').html();
		$('#recipe_preparation').empty();
		var textarea = $('<textarea>'+ html + '</textarea>').appendTo('#recipe_preparation');
		$(textarea).ckeditor(function() {},
		    {
			"saveFunction" : function(data) {
					    alert(data);
					}
		    }
		);
	    });
	    
	    
	    
	    // make other stuff editable
	    $('#recipe_name').editable(
		function(value, settings) {
    		    /* Clear errors */

		    if ($('#recipe_name').data('tooltip')) {
			$('#recipe_name').data('tooltip').hide();
			$('#recipe_name').data('tooltip', null);
		    }

		    $('.error-field').removeClass('error-field');
		    
		    var sendData = {
			"recipe_id": recipeId,
			"edit_type": "edit_name",
			"edit_val" : value
		    };

		    $.ajax( {
			"dataType": 'json',
			"type": "GET",
			"url": "ajax_editable.php",
			"data": sendData,
			"success": function(data) {
			    //console.log(data);
    
			    if (data.error == 0) {

			    } else {
				$('#recipe_name').addClass('error-field');

				$('#recipe_name').attr('title', data.errmsg);
				$('#recipe_name').tooltip({
					position: "top left",
					offset: [10, 150],
					events: {
					    def: ',',
					    input: ',',
					    widget: ',',
					    tooltip: ','
					    
					},
					effect: "fade",
					tipClass: "tooltip-arrow-black",
					opacity: 1
				});
				$('#recipe_name').data('tooltip').show();
				//console.log(data.errorRow);
				//console.log(oTable.fnGetNodes(data.errorRow));
			    }
			    
			}
		    } );
		    return(value);
		},
		{
		    tooltip   : 'Click to edit...'
		}
	    );

	    
	    
	    
	    //hide the all of the element with class msg_body
	    $("#acc_content").hide();
	    //toggle the componenet with class msg_body
	    $("#acc_head").click(function() {
		$(this).next("#acc_content").slideToggle(100);
	    });

	    $.editable.addInputType('autocomplete', {
		element : $.editable.types.text.element,
		plugin : function(settings, original) {
		    $('input', this).autocomplete( {
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
			minLength: settings.autocomplete.minLength }
		    );
		}
	    });


	    //oTable = $('#ingredients_data').dataTable();
	    oTable = $('#ingredients_data').dataTable({
		//"bJQueryUI": true,
		"sPaginationType": "full_numbers",
		"bServerSide": false,
		"iDisplayLength": 50,
		"bProcessing": true,
		"sDom": 'rti',
		"sAjaxSource": 'ajax_experiment.php',
		"fnServerData": function ( sSource, aoData, fnCallback ) {
		    aoData.push( { "name": "recipe_id", "value": recipeId } );
		    $.ajax( {
			"dataType": 'json',
			"type": "GET",
			"url": sSource,
			"data": aoData,
			"success": fnCallback
		    } );
		},

		"fnDrawCallback": function() {
		    /* This can be called while still initializing */
		    if (oTable == null)
			oTable = this;
		    //console.log(this);
		    
		    /* Apply the jEditable handlers to the table */
		    
		    $(oTable.fnGetNodes()).each(function() {
			$('td', this).slice(0,3).editable(
			    function(value, settings) {
				/* Clear previous nutri info */
				$('#nutri_info').empty();

				/* Clear errors */
				$(oTable.fnGetNodes()).filter('.error-row').each(function() {
				    $('td', this).slice(0, 2).each(function() {
					if ($(this).data('tooltip')) {
					    $(this).data('tooltip').hide();
					    $(this).data('tooltip', null);
					}
				    });
				});
				$(oTable.fnGetNodes()).filter('.error-row').removeClass('error-row');
				

				/* Update edited value in table */
				oTable.fnUpdate(value, oTable.fnGetPosition( this )[0], oTable.fnGetPosition( this )[2]);
				var sendData = {
				    "recipe_id": recipeId,
				    "adding_row": addingRow,
				    "save_changes": 0,
				    "row_id": this.parentNode.getAttribute('id'),
				    "column": oTable.fnGetPosition( this )[2],
				    "data": oTable.fnGetData()
				};
				addingRow = 0;
				//console.log(sendData);
				$.ajax( {
				    "dataType": 'json',
				    "type": "GET",
				    "url": "ajax_experiment.php",
				    "data": sendData,
				    "success": function(data) {
					//console.log(data);

					if (data.error == 0) {
					    /* Update the table, row by row */
					    console.log(data.deletedRows);
					    for (var i in data.deletedRows) {
						oTable.fnDeleteRow(data.deletedRows[i]);
					    }
					    for (var i in data.aaData) {
						oTable.fnUpdate(data.aaData[i], i);
					    }

					    /* populate nutri_info div */
					    var a = $('<a href="nutrilabel.php?'+ $.param(data.nutriInfo[0]) +'" class="highslide">Revised Nutritional Information Label</a>').appendTo('#nutri_info');
					    a.click(function() {
						return hs.expand(this);
					    });
					} else {
					    $(oTable.fnGetNodes(data.errorRow)).addClass('error-row');
					    errorCol = data.errorCol;
					    $('td', oTable.fnGetNodes(data.errorRow)).slice(errorCol, errorCol+1).attr('title', data.errmsg);
					    $('td', oTable.fnGetNodes(data.errorRow)).slice(errorCol, errorCol+1).tooltip({
						    position: "top center",
						    events: {
							def: ',',
							input: ',',
							widget: ',',
							tooltip: ','
							
						    },
						    effect: "fade",
						    tipClass: "tooltip-arrow-black",
						    opacity: 1
					    });
					    $('td', oTable.fnGetNodes(data.errorRow)).slice(errorCol, errorCol+1).data('tooltip').show();
					    //console.log(data.errorRow);
					    //console.log(oTable.fnGetNodes(data.errorRow));
					}
					
				    }
				} );
				return(value);
			    },
			    {
				//onblur : ignore  -- Click outside editable area is ignored. Pressing ESC cancels changes. Clicking submit button submits changes.
				height    : "14px",
				type      : "autocomplete",
				tooltip   : 'Click to edit...',
				autocomplete : {
				    minLength : 1
				}
			    }
			);
		    })
		}
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
    /*]]>*/
    </style>
</head>

<body>
    <?php print_header(); ?>
    <div class="spacer container_16"></div>

    <div id="content" class="container_16">
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

	if ($_GET['recipe_id']) {
		try {
			$recipe = new Recipe($_GET['recipe_id']);
			$info = $recipe->getNutriInfo();
			echo '<div class="container_16">';
			echo '<div class="grid_16">';
			echo '<h1 id="recipe_name" style="margin-bottom: 8px">'.$recipe->name.'</h1>';
			echo '</div>';
			echo '</div>';


			echo '<div class="container_16 clearfix">';
			echo '<div class="grid_11">Serves ';
			echo $recipe->serves;
			echo '</div>';
			echo '<div class="grid_11">';

			echo '<h2>Ingredients</h2>';
			
			//echo '<h2 id="acc_head"><a style="text-decoration: none; color: #000000;" href="#">Ingredients</a></h2>';
			//echo '<div id ="acc_content">';
			
			//echo '<div style="width: 520px;">';
			$i = 0;
			foreach ($recipe->ingredients as $ingredient) {
				if ($i % 2 == 0) {
					echo '<div class="row clearfix">';
					$class = "leftfixed";
				} else {
					$class = "rightfixed";
				}
				if ($ingredient['method'])
				    echo '<div class="'.$class.'">&bull;&nbsp;'.$ingredient['qty'].$ingredient['unit'].' '.$ingredient['Ingredient']->name.' ('.$ingredient['method'].')</div>';
				else
				    echo '<div class="'.$class.'">&bull;&nbsp;'.$ingredient['qty'].$ingredient['unit'].' '.$ingredient['Ingredient']->name.'</div>';

				if ($i % 2 != 0)
					echo '</div>';
				$i++;
			}
			if ($i % 2 != 0)
			    echo '</div>';
			
			echo '<h2 id="recipe_preparation_header" style="clear: left; padding-top: 15px;">Preparation</h2>';
			echo '<div id="recipe_preparation">';
			echo $recipe->instructions;
			echo '</div>';
			echo '</div>';

			echo '<div class="grid_5">';
			echo '<img alt="Nutritional Information Label" src="nutrilabel.php?carb='.$info['carb'].'&amp;protein='.$info['protein'].'&amp;fat='.$info['fat'].'&amp;sat_fat='.$info['sat_fat'].'&amp;kcal='.$info['kcal'].'&amp;cholesterol='.$info['cholesterol'].'&amp;sodium='.$info['sodium'].'&amp;fibre='.$info['fibre'].'&amp;sugar='.$info['sugar'].'"/>';
			
			echo '</div>';
			echo '</div>';













		    if (count($recipe->photos) > 0) {
			echo '<div class="container_16 clearfix">
				 <div class="highslide-gallery" style="clear: both;">
				    <h2>Photos</h2>
    
				    <ul>';
			


			foreach ($recipe->photos as $photo) {
			    echo '<li>';
			    echo '<a href="'.$photo->get().'" class="highslide" title="'.$photo->caption.'" onclick="return hs.expand(this, config1 )">';
			    echo '<img src="'.$photo->getThumbnail().'" alt="photo of dish"/>';
			    echo '</a>';
			    echo '</li>';
			}
    



			echo '
				    </ul>
				    <div style="clear:both"></div>
				</div>
			    </div>';
		    }


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


		
	<div class="container_16 clearfix">
	    <a name="detailednutri" id="detailednutri"></a>
	    <h3 id="acc_head"><a style="text-decoration: none; color: #000000;" href="#detailednutri">Detailed Nutrition Facts and Sandbox</a></h3>
	    <div id="acc_content">
		<a class="boring" href="#" onclick="fnClickAddRow()"><img class="boring" src="icons/add.png" alt="add a row"/>Add a row</a><br/>
		<a class="boring" href="#" onclick="saveChanges()"><img class="boring" src="icons/table_save.png" alt="add a row"/>Save changes to ingredients</a>

		<div id="nutri_info"></div>
		<div id="demo">
		    <table cellpadding="0" cellspacing="0" border="0" class="display" id="ingredients_data">
			<thead>
			    <tr>
				<th>Ingredient</th>
    
				<th>Quantity</th><!--<th>Unit</th> -->

				<th>Method</th><!--<th>Unit</th> -->
    
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

				<th>Method</th><!--<th>Unit</th> -->
    
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
		    <tr><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td><td>dummy</td></tr>

			</tbody>
		    </table>
		</div>
	    </div>
	</div>
    </div>

    <div class="spacer container_16"></div>
    <?php print_footer(); ?>
</body>
</html>
