<?php
include('functions.php');
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
    
    
    
    
    
    
    









    <link type="text/css" href="css/style.css" rel="stylesheet"/>
    <link type="text/css" href="css/print.css" rel="stylesheet" media="print"/>
    <link type="text/css" href="css/ui-lightness/jquery-ui-1.8.7.custom.css" rel="stylesheet"/>
    <script type="text/javascript" src="js/jquery-1.4.4.min.js">
</script>
    
    <script type="text/javascript" src="js/jquery-ui-1.8.7.custom.min.js">
</script>
    <script type="text/javascript" src="js/jquery.tools.min.js"></script>

<?php
    if($globalConfig['photo']['Viewer'] == "highslide") {
	echo '
	    <!-- Highslide -->
	    <script type="text/javascript" src="highslide/highslide-with-gallery.min.js">
	    </script>
	    <script type="text/javascript" src="highslide/highslide.config.js" charset="utf-8">
	    </script>
	    <link rel="stylesheet" type="text/css" href="highslide/highslide.css"/>
	    <!--[if lt IE 7]>
		<link rel="stylesheet" type="text/css" href="highslide/highslide-ie6.css" />
	    <![endif]-->
	    ';
    } else if($globalConfig['photo']['Viewer'] == "fancybox") {
	echo '
	    <!-- Fancybox -->
	    <script type="text/javascript" src="fancybox/jquery.fancybox-1.3.4.js"></script>
	    <link rel="stylesheet" href="fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />
	    ';
    } if($globalConfig['photo']['Viewer'] == "colorbox") {
	echo '
	    <!-- Colorbox -->
	    <script type="text/javascript" src="colorbox/colorbox/jquery.colorbox-min.js"></script>
	    <link rel="stylesheet" href="colorbox/example'.$globalConfig['photo']['Colorbox']['Style'].'/colorbox.css" type="text/css" media="screen" />
	';
    } else if($globalConfig['photo']['Viewer'] == "prettyPhoto") {
	echo '
	    <!-- prettyPhoto -->
	    <script type="text/javascript" src="prettyphoto/js/jquery.prettyPhoto.js"></script>
	    <link rel="stylesheet" href="prettyphoto/css/prettyPhoto.css" type="text/css" media="screen" />
	';
    }
?>

    <script type="text/javascript" language="javascript" src="js/jquery.dataTables.js">
</script>
    <script type="text/javascript" language="javascript" src="js/jquery.jeditable.mini.js">
    </script>
    <!--
    <script type="text/javascript" src="ckeditor/ckeditor.js">
</script>
    <script type="text/javascript" src="ckeditor/adapters/jquery.js"></script>
    <script type="text/javascript" src="ckeditor/plugins/save/plugin.js"></script>
    -->
    <script type="text/javascript" src="tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
    <script type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
    <script type="text/javascript" src="js/recipes.js">
    </script>
    <script type="text/javascript">
    //<![CDATA[
	var oTable = null;
	var addingRow = 0;
	var recipeId = <?php echo $_REQUEST['recipe_id'] ?>;
	var isEditing = 0;
	var isPhotoEditing = 0;
	var isIngredientEditing = 0;
	var origHTML = '';
	var RMConfig = {
	    photoViewer : "<?php echo $globalConfig['photo']['Viewer'] ?>",
	}


	function switchIngredientsToNormal() {
	    if (isIngredientEditing == 0)
		return;

	    $('#ingredient_add_inputs').children().filter('.error-field').removeClass('error-field');
	    $('.error-ingredients-tooltip').each(function() {
		$(this).data('tooltip').hide();
		$(this).data('tooltip', null);
		$(this).removeClass('error-ingredients-tooltip');
	    });

	    var a = $('<a class="boring editsection" id="recipe_ingredients_header" href="#" title="Edit"></a>').replaceAll('#recipe_ingredients_header');
	    a.click(function() {
	        switchIngredientsToEdit();
	    });
	    a.append('<img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)">');
	    $('#recipe_ingredients_header2').remove();
	    isIngredientEditing = 0;
	    populatePage();
	}

	function switchIngredientsToEdit()
	{
	    if (isIngredientEditing)
		return;
	    isIngredientEditing = 1;
	    $.post("ajax_formdata.php", {
		recipe: recipeId
	    },
	    function(recipe) {
		// format and output result
		if (recipe.exception) {
		    alert(recipe.exception);
		    return;
		}

		$('#recipe_ingredients').empty();
		var form = $('<form id="ingredients_edit_form" action="ajax_editable.php" method="post"></form>').appendTo('#recipe_ingredients');
		form.append('<input type="hidden" name="recipe_id" value="'+recipeId+'"/>');
		form.append('<input type="hidden" name="edit_type" value="edit_ingredients"/>');
		var div = $('<div id="ingredient_add_inputs"></div>').appendTo(form);
		if (recipe.ingredients.length > 0) {
		    //$('#recipe_photos').append('<h2>Photos</h2>');
		    for (var i in recipe.ingredients) {
			div.append(createIngredientRow(recipe.ingredients[i].qty, recipe.ingredients[i].unit, recipe.ingredients[i].Ingredient.name, recipe.ingredients[i].method));
		    }
		}
		$( "#ingredient_add_inputs" ).sortable({
			placeholder: "sortable-placeholder"
		});
		$( "#ingredient_add_inputs" ).disableSelection();

		var div = $('<div class="row"></div>').appendTo('#recipe_ingredients');
		var a = $('<a class="boring" href="#"></a>').appendTo(div);
		a.click(function() {
		    $('#ingredient_add_inputs').append(createIngredientRow('100', 'g', '', 'method (e.g. diced)'));
		});
		a.append('<img class="boring" src="icons/add.png" width="16" height="16" alt="add ingredient field">');


		var a = $('<a class="boring editsection" id="recipe_ingredients_header" href="#" title="Finish editing"></a>').replaceAll('#recipe_ingredients_header');
		a.click(function() {
		    // Save
		    $('#ingredient_add_inputs').children().filter('.error-field').removeClass('error-field');
		    $('.error-ingredients-tooltip').each(function() {
			$(this).data('tooltip').hide();
			$(this).data('tooltip', null);
			$(this).removeClass('error-ingredients-tooltip');
		    });
		    $('input[name^="ing_method"]').trigger('focus');
		    $.ajax({
			"dataType": 'json',
			"type": "GET",
			"url": "ajax_editable.php",
			"data": $('#ingredients_edit_form').serialize(),
			"success": function(data) {
			    //console.log(data);
    
			    if (data.error == 0) {
				switchIngredientsToNormal();
			    } else {
				if (data.errorRow < 0) {
				    alert(data.errmsg);
				    return;
				}
				console.log($('#ingredient_add_inputs').children());
				$('#ingredient_add_inputs').children().eq(data.errorRow).addClass('error-field');
				
				var input;
				if (data.errorCol == 1)
				    input = $('#ingredient_add_inputs').children().eq(data.errorRow).children().filter('input[name^=ing_qty]');
				else
				    input = $('#ingredient_add_inputs').children().eq(data.errorRow).children().filter('input[name^=ing_name]');

				input.attr('title', data.errmsg);
				input.addClass('error-ingredients-tooltip');
				input.tooltip({
					position: "top center",
					//offset: [10, 150],
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
				input.data('tooltip').show();
				//console.log(data.errorRow);
				//console.log(oTable.fnGetNodes(data.errorRow));
			    }
			    
			}
		    });
		    
		});
		a.append('<img class="boring" src="icons/accept.png" width="16" height="16" alt="Finish Editing">');

		var a = $('<a class="boring editsection" id="recipe_ingredients_header2" href="#" title="Finish editing without saving"></a>').insertBefore('#recipe_ingredients_header');
		a.click(function() {
		    // Cancel
		    switchIngredientsToNormal();
		});
		a.append('<img class="boring" src="icons/cancel.png" width="16" height="16" alt="Finish Editing without save">');

	    },
	    'json');  
	}






























	function switchPhotosToNormal() {
	    if (isPhotoEditing == 0)
		return;
	    var a = $('<a class="boring editsection" id="recipe_photo_header" href="#" title="Edit"></a>').replaceAll('#recipe_photo_header');
	    a.click(function() {
	        switchPhotosToEdit();
	    });
	    a.append('<img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)">');
	    isPhotoEditing = 0;
	    populatePage();
	}

	function switchPhotosToEdit()
	{
	    if (isPhotoEditing)
		return;
	    isPhotoEditing = 1;
	    $.post("ajax_formdata.php", {
		recipe: recipeId
	    },
	    function(recipe) {
		// format and output result
		if (recipe.exception) {
		    alert(recipe.exception);
		    return;
		}

		$('#recipe_photos').empty();
		var div = $('<div id="photo_add_inputs"></div>').appendTo('#recipe_photos');
		if (recipe.photos.length > 0) {
		    //$('#recipe_photos').append('<h2>Photos</h2>');
		    for (var i in recipe.photos) {
			div.append(createPhotoRow(recipe.photos[i].id, recipe.photos[i].photo, recipe.photos[i].thumb, recipe.photos[i].caption));
		    }
		}
		var div = $('<div class="row"></div>').appendTo('#recipe_photos');
		var a = $('<a class="boring" href="#"></a>').appendTo(div);
		a.click(function() {
		    addUpload(document.getElementById('photo_add_inputs'), recipeId);
		});
		a.append('<img class="boring" src="icons/add.png" width="16" height="16" alt="add photo field">');


		var a = $('<a class="boring editsection" id="recipe_photo_header" href="#" title="Finish editing"></a>').replaceAll('#recipe_photo_header');
		a.click(function() {
		    switchPhotosToNormal();
		});
		a.append('<img class="boring" src="icons/accept.png" width="16" height="16" alt="Finish Editing">');

	    },
	    'json');  
	}

	function populatePage() {
	    $('#loading-screen').data('overlay').load();
	    $.post("ajax_formdata.php", {
		recipe: recipeId
	    },
	    function(recipe) {
		// format and output result
		if (recipe.exception) {
		    alert(recipe.exception);
		    return;
		}

		$('#recipe_name').empty();
		$('#recipe_serves').empty();
		$('#recipe_ingredients').empty();
		$('#recipe_preparation').empty();
		$('#recipe_photos').empty();
		$('#recipe_nutrilabel').empty();

		$('#recipe_name').append(recipe.name);
		$('#recipe_serves').append('Serves ' + recipe.serves);
		$('#recipe_preparation').append(recipe.instructions);

		$('#recipe_nutrilabel').append('<img src="nutrilabel.php?'+ $.param(recipe.nutri_info_keyval) +'" alt="Nutritional Information Label">');

		var count = recipe.ingredients.length;
		var leftCount = Math.ceil(count/2);
		var rightCount = count - leftCount;
		
		var div = $('<div class="leftfixed"></div>').appendTo('#recipe_ingredients');
		if (leftCount > 0) {
		    var list = $('<ul style="margin-top: 0px;"></ul>').appendTo(div);
		    for (var i = 0; i < leftCount; i = i+1) {
			if (recipe.ingredients[i].method != '')
			    list.append('<li>' + recipe.ingredients[i].qty + recipe.ingredients[i].unit + ' ' + recipe.ingredients[i].Ingredient.name + ' (' + recipe.ingredients[i].method +')</li>');
			else
			    list.append('<li>' + recipe.ingredients[i].qty + recipe.ingredients[i].unit + ' ' + recipe.ingredients[i].Ingredient.name + '</li>');
		    }
		}

		var div = $('<div class="rightfixed"></div>').appendTo('#recipe_ingredients');
		if (rightCount > 0) {
		    var list = $('<ul style="margin-top: 0px;"></ul>').appendTo(div);
		    for (var i = leftCount; i < count; i = i+1) {
			if (recipe.ingredients[i].method != '')
			    list.append('<li>' + recipe.ingredients[i].qty + recipe.ingredients[i].unit + ' ' + recipe.ingredients[i].Ingredient.name + ' (' + recipe.ingredients[i].method +')</li>');
			else
			    list.append('<li>' + recipe.ingredients[i].qty + recipe.ingredients[i].unit + ' ' + recipe.ingredients[i].Ingredient.name + '</li>');
		    }
		}

		for (var i in recipe.ingredients) {
		    
		    //$('#ingredient_add_inputs').append(createIngredientRow(recipe.ingredients[i].qty, recipe.ingredients[i].unit, recipe.ingredients[i].Ingredient.name, recipe.ingredients[i].method));
		}

		if (recipe.photos.length > 0) {
		    //$('#recipe_photos').append('<h2>Photos</h2>');
		    if (RMConfig.photoViewer == 'highslide') {
			var list = $('<ul></ul>').appendTo('#recipe_photos');
			for (var i in recipe.photos) {
			    //recipe.photos[i].id, recipe.photos[i].photo, recipe.photos[i].thumb, recipe.photos[i].caption));
			    var li = $('<li></li>').appendTo(list);
			    var a = $('<a id="'+recipe.photos[i].id+'" href="'+recipe.photos[i].photo+'" title="'+recipe.photos[i].caption+'" class="highslide">').appendTo(li);
			    a.append('<img src="'+recipe.photos[i].thumb+'" alt="photo of dish">');
			    a.each(function() {
				this.onclick = function() {
				    return hs.expand(this, config1);
				};
			    });
			}
		    } else {
			for (var i in recipe.photos) {
			    var a;
			    if (RMConfig.photoViewer == 'prettyPhoto')
				a = $('<a id="'+recipe.photos[i].id+'" href="'+recipe.photos[i].photo+'" title="'+recipe.photos[i].caption+'" rel="prettyPhoto[gallery1]">').appendTo('#recipe_photos');
			    else
				a = $('<a id="'+recipe.photos[i].id+'" href="'+recipe.photos[i].photo+'" title="'+recipe.photos[i].caption+'" rel="gallery1">').appendTo('#recipe_photos');
			    
			    a.append('<img src="'+recipe.photos[i].thumb+'" alt="photo of dish">');
			    if (RMConfig.photoViewer == 'fancybox')
				a.fancybox();
			    if (RMConfig.photoViewer == 'colorbox')
				a.colorbox({maxHeight:"100%", maxWidth:"100%"});
			}
			if (RMConfig.photoViewer == 'prettyPhoto')
			    $('#recipe_photos a').prettyPhoto({theme:'facebook'});
		    }
		}
		$('#loading-screen').data('overlay').close();
	    },
	    'json');
	}

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


	function savePreparation(data) {
	    if (data != null) {
		var sendData = {
		    "recipe_id": recipeId,
		    "edit_type": "edit_preparation",
		    "edit_val" : data
		};
	    
		$.ajax({
		    "dataType": 'json',
		    "type": "GET",
		    "url": "ajax_editable.php",
		    "data": sendData,
		    "success": function(data) {
			if (data.error == 0) {
			} else {
			    alert(data.errmsg);
			}
		    }
		});
	    }

	    $('#recipe_preparation').empty();

	    if (data == null)
		data = origHTML;

	    $('#recipe_preparation').append(data);

	    var a = $('<a class="boring editsection" id="recipe_preparation_header" href="#" title="Edit"></a>').replaceAll('#recipe_preparation_header');
	    a.click(function() {
	        activateEditor();
	    });
	    a.append('<img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)">');

	    $('#recipe_preparation_header2').remove();
	    isEditing = 0;
	}

	function myFileBrowser (field_name, url, type, win) {
	  tinyMCE.activeEditor.windowManager.open({
	      file : 'fed.php' + '?editor=tinymce' + '&type=' + type + '&recipe_id=' + recipeId,
	      title : 'My File Browser',
	      width : 800,  // Your dimensions may differ - toy around with them!
	      height : 600,
	      resizable : "yes",
	      inline : "yes",  // This parameter only has an effect if you use the inlinepopups plugin!
	      close_previous : "no"
	  }, {
	      window : win,
	      input : field_name
	  });
	  return false;
	}

	function activateEditor() {
		if (isEditing)
		    return false;

		isEditing = 1;
		var html = $('#recipe_preparation').html();
		origHTML = html;
		$('#recipe_preparation').empty();
		var textarea = $('<textarea id="tinymce" style="width: 100%; height: 400px;">'+ html + '</textarea>').appendTo('#recipe_preparation');
		
		$(textarea).tinymce({
		    //script_url : 'tinymce/tiny_mce.js',
		    plugins : "safari,spellchecker,pagebreak,style,layer,table,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
		    //imagemanager, filemanager

		    theme : "advanced",
		    theme_advanced_buttons1 : "mysave,myclose,|,preview,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
		    theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,forecolor,backcolor",
		    theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		    theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
		    theme_advanced_toolbar_location : "top",
		    theme_advanced_toolbar_align : "left",
		    theme_advanced_statusbar_location : "bottom",
		    theme_advanced_resizing : true,
		    file_browser_callback: "myFileBrowser",

		    //theme_advanced_buttons3_add : "myclose",

		    setup : function(ed) {
                        ed.addButton('mysave', {
                                title : 'Apply/Save and Close',
                                //image : 'themes/default/img/icons/save.gif',
				image : 'icons/page_save.png',
                                onclick : function() {
					console.debug($(this).html());
					html = $(this).html();
                                        this.remove();
					savePreparation(html);
                                }
                        });
                        ed.addButton('myclose', {
                                title : 'Close without saving',
                                //image : 'themes/default/img/icons/save.gif',
				image : 'icons/cancel.png',
                                onclick : function() {
					this.remove();
					savePreparation(null);
                                }
                        });
		    }
    

		});
		
		var a = $('<a class="boring editsection" id="recipe_preparation_header" href="#" title="Finish editing and save changes"></a>').replaceAll('#recipe_preparation_header');
		a.click(function() {
		    html = $('#tinymce').tinymce().getContent();
		    console.log(html);
		    $('#tinymce').tinymce().remove();
		    savePreparation(html);
		});
		a.append('<img class="boring" src="icons/accept.png" width="16" height="16" alt="Finish Editing and save changes">');

		var a = $('<a class="boring editsection" id="recipe_preparation_header2" href="#" title="Finish editing without saving"></a>').insertBefore('#recipe_preparation_header');
		a.click(function() {
		    $('#tinymce').tinymce().remove();
		    savePreparation(null);
		});
		a.append('<img class="boring" src="icons/cancel.png" width="16" height="16" alt="Finish Editing without save">');

		/*
		$(textarea).ckeditor(function() {},
		    {
			// needs: http://dev.ckeditor.com/ticket/4507
			"saveFunction" : function(data, editor) {
					    //console.log(editor);
					    editor.destroy();
					    savePreparation(data);
					    
					}
		    }
		);
		*/
	}

	$(function() {

	    $('form').submit(function() {
		$.ajax({
		    type: "POST",
		    timeout: 30000,
		    /* in ms */
		    url: this.action/* "ajax_form_recipes.php" */,
		    dataType: "json",
		    data: $(this).serialize(),
		    success: function(data) {
			if (data.error == 0) {
			} else {
			    recipeId = -1;
			    // printMsgs(data, 'error');
			}
		    },
		    error: function(req, textstatus) {
			alert('Request failed: ' + textstatus);
		    }
		});
		return false;
	    });

	    
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
	    
	    $('#recipe_preparation_header').click(activateEditor);
	    
	    
	    
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
		    //onblur : 'ignore',
		    tooltip   : 'Click to edit...'
		}
	    );

	    $('#recipe_serves').editable(
		function(value, settings) {
    		    /* Clear errors */

		    if ($('#recipe_serves').data('tooltip')) {
			$('#recipe_serves').data('tooltip').hide();
			$('#recipe_serves').data('tooltip', null);
		    }

		    $('.error-field').removeClass('error-field');
		    
		    var sendData = {
			"recipe_id": recipeId,
			"edit_type": "edit_serves",
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
				populatePage();
			    } else {
				$('#recipe_serves').addClass('error-field');

				$('#recipe_serves').attr('title', data.errmsg);
				$('#recipe_serves').tooltip({
					position: "top left",
					offset: [0, 150],
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
				$('#recipe_serves').data('tooltip').show();
				//console.log(data.errorRow);
				//console.log(oTable.fnGetNodes(data.errorRow));
			    }
			    
			}
		    } );
		    return 'Serves ' + value;
		},
		{
		    //onblur : 'ignore',
		    tooltip   : 'Click to edit...',
		        data: function(value, settings) {
			    /* Convert <br> to newline. */
			    return value.replace('Serves ', '');
			}
		}
	    );
	    
	    
	    //hide the all of the element with class msg_body
	    $("#acc_content").hide();
	    //toggle the componenet with class msg_body
	    $("#acc_head").add(".detailed_nutri_link").click(function() {
		$("#acc_content").slideToggle(0);
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
				onblur : 'ignore',
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


	<form name="delete_photo" action="ajax_form_photos.php" method="post" id="delete_photo">
	    <input type="hidden" name="recipe_id" value="-1"/>
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
		<h1 id="recipe_name" style="margin-bottom: 8px">DUMMY_RECIPE_NAME</h1>
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
		<div id="recipe_serves">
		    DUMMY_SERVES_N
		</div>
	    </div>

	    <div class="grid_11">
		<h2 style="margin-bottom: 0px;">
		    <span class="editsection">
			<a class="boring editsection" href="#" id="recipe_ingredients_header" onclick="switchIngredientsToEdit();" title="Edit">
			    <img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)"/>
			</a>
		    </span>
		    <span>Ingredients</span>
		</h2>

		<div id="recipe_ingredients" class="row clearfix">
		    <div class="leftfixed">
			&bull;&nbsp;DUMMY_INGREDIENT 100g foo (foobar)
		    </div>

		    <div class="rightfixed">
			&bull;&nbsp;DUMMY_INGREDIENT 100g foo (foobar)
		    </div>
		</div>

		<!--<h2 id="recipe_preparation_header" style="clear: left; padding-top: 15px;">Preparation</h2>-->
		<h2>
		    <span class="editsection">
			<a class="boring editsection" href="#" id="recipe_preparation_header" title="Edit">
			    <img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)"/>
			</a>
		    </span>
		    <span>Preparation</span>
		</h2>

		<div id="recipe_preparation">
		    DUMMY_RECIPE_PREPARATION
		</div>
	    </div>

	    <div class="grid_5">
		<div id="recipe_nutrilabel">
		    DUMMY_RECIPE_NUTRI_LABEL
		</div>
		<p class="noprint" align="center"><a href="#detailednutri" class="recipe detailed_nutri_link">View detailed nutritional information</a></p>
	    </div>
	</div>

	<div class="container_16 clearfix">
	    <h2>
	    	<span class="editsection">
		    <a class="boring editsection" href="#" id="recipe_photo_header" onclick="switchPhotosToEdit();" title="Edit">
		        <img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)"/>
		    </a>
		</span>
		<span>Photos</span>
	    </h2>
	    <div id="recipe_photos" class="highslide-gallery" style="clear: both;">
		DUMMY_RECIPE_PHOTOS
	    </div>
	</div>





































	<div class="container_16 clearfix noprint">
	    <a name="detailednutri" id="detailednutri"></a>
	    <h2 id="acc_head">
		    <span class="editsection">
			<a class="boring" href="#" onclick="fnClickAddRow()" title="Add a row">
			    <img class="boring" src="icons/add.png" alt="add a row"/>
			</a>
			<a class="boring" href="#" onclick="saveChanges()" title="Save Changes">
			    <img class="boring" src="icons/table_save.png" alt="add a row"/>
			</a>
		    </span>
		    <span><a style="text-decoration: none; color: #000000;" href="#detailednutri">Detailed Nutrition Facts and Sandbox</a></span>
	    </h2>
	    <div id="acc_content">
		<!--
		<a class="boring" href="#" onclick="fnClickAddRow()"><img class="boring" src="icons/add.png" alt="add a row"/>Add a row</a><br/>
		<a class="boring" href="#" onclick="saveChanges()"><img class="boring" src="icons/table_save.png" alt="add a row"/>Save changes to ingredients</a>
		-->
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
