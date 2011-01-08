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
	var isQtyEditing = 0;
	var isPhotoEditing = 0;
	var isNutritionalEditing = 0;
	var isNutrientEditing = 0;
	var origHTML = '';
	var RMConfig = {
	    photoViewer : "<?php echo $globalConfig['photo']['Viewer'] ?>",
	    richEditor : "<?php echo $globalConfig['text']['richEditor'] ?>",
	}















	function switchQtyToNormal() {
	    if (isQtyEditing == 0)
		return;

	    $('#ingredient_qtys').find('.error-field').removeClass('error-field');
	    $('.error-qtys-tooltip').each(function() {
		$(this).data('tooltip').hide();
		$(this).data('tooltip', null);
		$(this).removeClass('error-qtys-tooltip');
	    });

	    $('#ingredient_qtys').empty();

	    var a = $('<a class="boring editsection" id="ingredient_qtys_header" href="javascript:void(0);" title="Edit"></a>').replaceAll('#ingredient_qtys_header');
	    a.click(function() {
	        switchQtyToEdit();
		return false;
	    });
	    a.append('<img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)">');
	    $('#ingredient_qtys_header2').remove();
	    isQtyEditing = 0;
	    populatePage();
	}

	function switchQtyToEdit() {
	    if (isQtyEditing)
		return;
	    isQtyEditing = 1;

	    $.post("ajax_formdata.php", {
		ingredient: ingredientId
	    },
	    function(ingredient) {
		// format and output result
		if (ingredient.exception) {
		    alert(ingredient.exception);
		    return;
		}

		$('#ingredient_qtys').empty();
		
		var form = $('<form id="ingredient_qty_form" action="ajax_editable.php" method="post"></form>').appendTo('#ingredient_qtys');
		form.append('<input type="hidden" name="ingredient_id" value="'+ingredientId+'"/>');
		form.append('<input type="hidden" name="edit_type" value="edit_qtys"/>');
		
		var div = $('<div id="ingredient_qtyunit" class="leftfixed"></div>').appendTo(form);
		div.append('<label for="field_qty">Quantity: </label>');
		div.append('<input type="text" size="5" name="field_qty" id="field_qty" value="'+ingredient.qty+'">');
		div.append('<select name="field_unit" id="field_unit"><option></option><option>g</option><option>ml</option><option>mg</option><option>kg</option><option>l</option></select>');
		$('#field_unit').val(ingredient.unit);
		
		var div = $('<div id="ingredient_typical_qtyunit" class="rightfixed"></div>').appendTo(form);
		div.append('<label for="field_typical_qty">Typical unit weight: </label>');
		div.append('<input type="text" size="5" name="field_typical_qty" id="field_typical_qty" value="'+ingredient.typical_qty+'">');
		div.append('<select name="field_typical_unit" id="field_typical_unit"><option>g</option><option>mg</option><option>kg</option></select>');
		$('#field_typical_unit').val(ingredient.typical_unit);

		var a = $('<a class="boring editsection" id="ingredient_qtys_header" href="javascript:void(0);" title="Finish editing"></a>').replaceAll('#ingredient_qtys_header');
		a.click(function() {
		    // Save
		    $('#ingredient_qtys').find('.error-field').removeClass('error-field');
		    $('.error-qtys-tooltip').each(function() {
			$(this).data('tooltip').hide();
			$(this).data('tooltip', null);
			$(this).removeClass('error-qtys-tooltip');
		    });

		    $.ajax({
			"dataType": 'json',
			"type": "GET",
			"url": "ajax_editable.php",
			"data": $('#ingredient_qty_form').serialize(),
			"success": function(data) {
			    //console.log(data);
    
			    if (data.error == 0) {
				switchQtyToNormal();
			    } else {
				if ((data.errorRow == '') || (data.errorRow == -1)) {
				    alert(data.errmsg);
				    return;
				}
				
				$('#field_'+data.errorRow).parent().addClass('error-field');

				input = $('#field_'+data.errorRow).filter('input');

				input.attr('title', data.errmsg);
				input.addClass('error-qtys-tooltip');
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
			    }
			    
			}
		    });
		    return false;  
		});
		a.append('<img class="boring" src="icons/accept.png" width="16" height="16" alt="Finish Editing">');

		var a = $('<a class="boring editsection" id="ingredient_qtys_header2" href="javascript:void(0);" title="Finish editing without saving"></a>').insertBefore('#ingredient_qtys_header');
		a.click(function() {
		    // Cancel
		    switchQtyToNormal();
		    return false;
		});
		a.append('<img class="boring" src="icons/cancel.png" width="16" height="16" alt="Finish Editing without save">');
		

	    });
	}













function createNutrientRow(qty, unit, name) {
    var row = $('<div class="row sortable-outline clearfix"></div>');

    spanleft = $('<span class="labelfullleft"></span>').appendTo(row);
    spanleft.append('<input type="text" size="5" name="nut_qty[]" value="'+ qty +'">');
    if (unit == '')
	unit = '(unit)';
    var span = $('<span class="nut-unit">'+unit+'</span>').appendTo(spanleft);

    spanright = $('<span class="formfull"></span>').appendTo(row);
    var nameInput = $('<input style="margin-left: 15px;" type="text" size="30" name="nut_name[]" value="'+ name +'">').appendTo(spanright);

    var a = $('<a href="#" class="boring"></a>').appendTo(spanright);
    a.click(function() {
	$(this).parent().parent().remove();
    });
    a.append('<img alt="remove field" width="16" height="16" src="icons/cross.png" class="boring">');

    var a = $('<a href="#" class="boring"></a>').appendTo(spanright);
    a.click(function() {
	$(this).parent().parent().after(createNutrientRow(' ', '', ''));
    });
    a.append('<img alt="add field" width="16" height="16" src="icons/add.png" class="boring">');
    nameInput.data('unitField', span);
    nameInput.autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "ajax_autocomplete.php",
                dataType: "json",
                data: {
                    type: "nutrients",
                    maxRows: 12,
                    term: request.term
                },
                success: function(data) {
                    if (data.exception) {
                        response([]);
                        alert(data.exception);
                        return;
                    }
		    console.log(data.objects);
                    response(data.objects);
                }
            });
        },
	select: function(event, ui) {
	    var val = ui.item.value;
	    console.log(val);
	    var idx = val.lastIndexOf('(');
	    var unit = val.substr(idx+1, val.length-2-idx);
	    console.log(unit);
	    ui.item.value = val.substr(0, idx -1);
	    console.log(ui.item.value);
	    $(this).data('unitField').text(unit);
	},
        minLength: 1
    });

    return row;
}













	function switchNutrientsToNormal() {
	    if (isNutrientEditing == 0)
		return;

	    $('#nutrient_add_inputs').find('.error-field').removeClass('error-field');
	    $('.error-nutrients-tooltip').each(function() {
		$(this).data('tooltip').hide();
		$(this).data('tooltip', null);
		$(this).removeClass('error-nutrients-tooltip');
	    });

	    $('#ingredient_nutrients_form').empty();

	    var a = $('<a class="boring editsection" id="ingredient_additional_nutritional_header" href="javascript:void(0);" title="Edit"></a>').replaceAll('#ingredient_additional_nutritional_header');
	    a.click(function() {
	        switchNutrientsToEdit();
		return false;
	    });
	    a.append('<img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)">');
	    $('#ingredient_additional_nutritional_header2').remove();
	    isNutrientEditing = 0;
	    populatePage();
	}

	function switchNutrientsToEdit() {
	    if (isNutrientEditing)
		return;
	    isNutrientEditing = 1;

	    $.post("ajax_formdata.php", {
		ingredient: ingredientId
	    },
	    function(ingredient) {
		// format and output result
		if (ingredient.exception) {
		    alert(ingredient.exception);
		    return;
		}

		console.log($('#ingredient_additional_nutritional_header'));
		console.log($('#ingredient_nutrients_form'));
		$('#ingredient_nutrients_form').empty();
		$('#ingredient_nutrients_form').prepend('<input type="hidden" name="ingredient_id" value="'+ingredientId+'"/>');
		$('#ingredient_nutrients_form').prepend('<input type="hidden" name="edit_type" value="edit_nutrients"/>');
		//$('#ingredient_nutrients_form').append(createNutrientRow(' ', '', ''));
		console.log($('#ingredient_nutrients_form'));
		var div = $('<div id="nutrient_add_inputs"></div>').appendTo('#ingredient_nutrients_form');
		if (ingredient.nutrients.length > 0) {
		    for (var i in ingredient.nutrients) {
			div.append(createNutrientRow(ingredient.nutrients[i].qty, ingredient.nutrients[i].unit, ingredient.nutrients[i].Nutrient.name));
		    }
		}
		$( "#nutrient_add_inputs" ).sortable({
			placeholder: "sortable-placeholder"
		});
		$( "#nutrient_add_inputs" ).disableSelection();
		var div = $('<div class="row"></div>').appendTo('#ingredient_nutrients_form');
		var a = $('<a class="boring" href="javascript:void(0);"></a>').appendTo(div);
		a.click(function() {
		    $('#nutrient_add_inputs').append(createNutrientRow(' ', '', ''));
		    return false;
		});
		a.append('<img class="boring" src="icons/add.png" width="16" height="16" alt="add ingredient field">');


		var a = $('<a class="boring editsection" id="ingredient_additional_nutritional_header" href="javascript:void(0);" title="Finish editing"></a>').replaceAll('#ingredient_additional_nutritional_header');
		a.click(function() {
		    // Save
		    $('#nutrient_add_inputs').find('.error-field').removeClass('error-field');
		    $('.error-nutrients-tooltip').each(function() {
			$(this).data('tooltip').hide();
			$(this).data('tooltip', null);
			$(this).removeClass('error-nutrients-tooltip');
		    });
		    console.log($('#ingredient_nutrients_form'));
		    console.log($('#ingredient_nutrients_form').serialize());
		    $.ajax({
			"dataType": 'json',
			"type": "GET",
			"url": "ajax_editable.php",
			"data": $('#ingredient_nutrients_form').serialize(),
			"success": function(data) {
			    //console.log(data);
    
			    if (data.error == 0) {
				switchNutrientsToNormal();
			    } else {
				if (data.errorRow < 0) {
				    alert(data.errmsg);
				    return;
				}

				$('#nutrient_add_inputs').children().eq(data.errorRow).addClass('error-field');
				input = $('#nutrient_add_inputs').children().eq(data.errorRow).find('input[name^=nut_name]');
				console.log(input);

				input.attr('title', data.errmsg);
				input.addClass('error-nutrients-tooltip');
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
		    return false;  
		});
		a.append('<img class="boring" src="icons/accept.png" width="16" height="16" alt="Finish Editing">');

		var a = $('<a class="boring editsection" id="ingredient_additional_nutritional_header2" href="javascript:void(0);" title="Finish editing without saving"></a>').insertBefore('#ingredient_additional_nutritional_header');
		a.click(function() {
		    // Cancel
		    switchNutrientsToNormal();
		    return false;
		});
		a.append('<img class="boring" src="icons/cancel.png" width="16" height="16" alt="Finish Editing without save">');
		

	    });
	}






































	function switchNutritionalToNormal() {
	    if (isNutritionalEditing == 0)
		return;

	    $('#ingredient_nutritional_information').find('.error-field').removeClass('error-field');
	    $('.error-nutri-tooltip').each(function() {
		$(this).data('tooltip').hide();
		$(this).data('tooltip', null);
		$(this).removeClass('error-nutri-tooltip');
	    });

	    $('#ingredient_nutri_form').find('input').remove();

	    var a = $('<a class="boring editsection" id="ingredient_nutritional_header" href="javascript:void(0);" title="Edit"></a>').replaceAll('#ingredient_nutritional_header');
	    a.click(function() {
	        switchNutritionalToEdit();
		return false;
	    });
	    a.append('<img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)">');
	    $('#ingredient_nutritional_header2').remove();
	    isNutritionalEditing = 0;
	    populatePage();
	}

	function switchNutritionalToEdit() {
	    if (isNutritionalEditing)
		return;
	    isNutritionalEditing = 1;
	    var fields = [ "kcal", "carb", "fat", "protein", "fibre", "sodium", "cholesterol", "sugar", "sat_fat" ];

	    $.post("ajax_formdata.php", {
		ingredient: ingredientId
	    },
	    function(ingredient) {
		// format and output result
		if (ingredient.exception) {
		    alert(ingredient.exception);
		    return;
		}

		console.log($('#ingredient_nutritional_information'));

		$('#ingredient_nutri_form').find('input').remove();
		$('#ingredient_nutri_form').prepend('<input type="hidden" name="ingredient_id" value="'+ingredientId+'"/>');
		$('#ingredient_nutri_form').prepend('<input type="hidden" name="edit_type" value="edit_nutritional"/>');

		for (var i in fields) {
		    $('#'+fields[i]).empty();
		    $('#'+fields[i]).append('<input name="field_'+fields[i]+'" id="field_'+fields[i]+'" type="text" size="5" value="'+ingredient[fields[i]]+'"/>');
		}

		var a = $('<a class="boring editsection" id="ingredient_nutritional_header" href="javascript:void(0);" title="Finish editing"></a>').replaceAll('#ingredient_nutritional_header');
		a.click(function() {
		    // Save
		    $('#ingredient_nutritional_information').find('.error-field').removeClass('error-field');
		    $('.error-nutri-tooltip').each(function() {
			$(this).data('tooltip').hide();
			$(this).data('tooltip', null);
			$(this).removeClass('error-nutri-tooltip');
		    });
		    console.log($('#ingredient_nutri_form'));
		    console.log($('#ingredient_nutri_form').serialize());
		    $.ajax({
			"dataType": 'json',
			"type": "GET",
			"url": "ajax_editable.php",
			"data": $('#ingredient_nutri_form').serialize(),
			"success": function(data) {
			    //console.log(data);
    
			    if (data.error == 0) {
				switchNutritionalToNormal();
			    } else {
				if ((data.errorRow == '') || (data.errorRow == -1)) {
				    alert(data.errmsg);
				    return;
				}
				
				$('#'+data.errorRow).addClass('error-field');
				

				input = $('#field_'+data.errorRow).filter('input');

				input.attr('title', data.errmsg);
				input.addClass('error-nutri-tooltip');
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
		    return false;  
		});
		a.append('<img class="boring" src="icons/accept.png" width="16" height="16" alt="Finish Editing">');

		var a = $('<a class="boring editsection" id="ingredient_nutritional_header2" href="javascript:void(0);" title="Finish editing without saving"></a>').insertBefore('#ingredient_nutritional_header');
		a.click(function() {
		    // Cancel
		    switchNutritionalToNormal();
		    return false;
		});
		a.append('<img class="boring" src="icons/cancel.png" width="16" height="16" alt="Finish Editing without save">');
		

	    });
	}


	function switchPhotosToNormal() {
	    if (isPhotoEditing == 0)
		return;
	    var a = $('<a class="boring editsection" id="ingredient_photo_header" href="javascript:void(0);" title="Edit"></a>').replaceAll('#ingredient_photo_header');
	    a.click(function() {
	        switchPhotosToEdit();
		return false;
	    });
	    a.append('<img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)">');
	    isPhotoEditing = 0;
	    populatePage();
	    return false;
	}

	function switchPhotosToEdit()
	{
	    if (isPhotoEditing)
		return false;
	    isPhotoEditing = 1;
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
		var div = $('<div id="photo_add_inputs"></div>').appendTo('#ingredient_photos');
		if (ingredient.photos.length > 0) {
		    //$('#ingredient_photos').append('<h2>Photos</h2>');
		    for (var i in ingredient.photos) {
			div.append(createPhotoRow('ingredient', ingredientId, ingredient.photos[i].id, ingredient.photos[i].photo, ingredient.photos[i].thumb, ingredient.photos[i].caption));
		    }
		}
		var div = $('<div class="row"></div>').appendTo('#ingredient_photos');
		var a = $('<a class="boring" href="javascript:void(0);"></a>').appendTo(div);
		a.click(function() {
		    addUpload(document.getElementById('photo_add_inputs'), 'ingredient', ingredientId);
		    return false;
		});
		a.append('<img class="boring" src="icons/add.png" width="16" height="16" alt="add photo field">');


		var a = $('<a class="boring editsection" id="ingredient_photo_header" href="javascript:void(0);" title="Finish editing"></a>').replaceAll('#ingredient_photo_header');
		a.click(function() {
		    switchPhotosToNormal();
		    return false;
		});
		a.append('<img class="boring" src="icons/accept.png" width="16" height="16" alt="Finish Editing">');

	    },
	    'json');

	    return false;
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

		$('#ingredient_nutri_form').find('input').remove();
		$('#ingredient_photos').empty();
		$('#ingredient_info').empty();
		$('#ingredient_qtys').empty();
		$('#ingredient_nutrients_form').empty();
		$('#ingredient_nutrilabel').empty();
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

		$('<div id="ingredient_qtyunit" class="leftfixed"></div>').appendTo('#ingredient_qtys').text('Quantity: ' + ingredient.qty + ingredient.unit);
		$('<div id="ingredient_typical_qtyunit" class="rightfixed"></div>').appendTo('#ingredient_qtys').text('Typical unit weight: ' + ingredient.typical_qty + ingredient.typical_unit);

		$('#kcal').text(ingredient.kcal);
		$('#carb').text(ingredient.carb);
		$('#sugar').text(ingredient.sugar);
		$('#fat').text(ingredient.fat);
		$('#sat_fat').text(ingredient.sat_fat);
		$('#protein').text(ingredient.protein);
		$('#fibre').text(ingredient.fibre);
		$('#sodium').text(ingredient.sodium);
		$('#cholesterol').text(ingredient.cholesterol);

		var count = ingredient.nutrients.length;
		var leftCount = Math.ceil(count/2);
		var rightCount = count - leftCount;

		var div = $('<div class="leftfixed"></div>').appendTo('#ingredient_nutrients_form');
		if (leftCount > 0) {
		    var table = $('<table>').appendTo(div);
		    for (var i = 0; i < leftCount; i = i+1) {
			table.append('<tr><td>'+ingredient.nutrients[i].Nutrient.name+': </td><td>'+ingredient.nutrients[i].qty + ingredient.nutrients[i].unit+'</td></tr>');
		    }
		}


		var div = $('<div class="rightfixed"></div>').appendTo('#ingredient_nutrients_form');
		if (rightCount > 0) {
		    var table = $('<table>').appendTo(div);
		    for (var i = leftCount; i < count; i = i+1) {
			table.append('<tr><td>'+ingredient.nutrients[i].Nutrient.name+': </td><td>'+ingredient.nutrients[i].qty + ingredient.nutrients[i].unit+'</td></tr>');
		    }
		}


		$('#ingredient_info').append(ingredient.info);

		$('#ingredient_photos').append(createPhotoGallery(ingredient.photos, '1'));
		$('#ingredient_nutrilabel').append('<img src="nutrilabel.php?'+ $.param(ingredient.nutri_info_keyval) +'" alt="Nutritional Information Label">');


		$('#loading-screen').data('overlay').close();
	    },
	    'json');
	}


























	function saveInfo(data) {
	    if (data != null) {
		var sendData = {
		    "ingredient_id": ingredientId,
		    "edit_type": "edit_info",
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

	    $('#ingredient_info').empty();

	    if (data == null)
		data = origHTML;

	    $('#ingredient_info').append(data);

	    var a = $('<a class="boring editsection" id="ingredient_info_header" href="javascript:void(0);" title="Edit"></a>').replaceAll('#ingredient_info_header');
	    a.click(function() {
	        activateEditor();
		return false;
	    });
	    a.append('<img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)">');

	    $('#ingredient_info_header2').remove();
	    isEditing = 0;
	}

	function myFileBrowser (field_name, url, type, win) {
	  tinyMCE.activeEditor.windowManager.open({
	      file : 'fed.php' + '?editor=tinymce' + '&type=' + type + '&ingredient_id=' + ingredientId,
	      title : 'My File Browser',
	      width : 800,  // Your dimensions may differ - toy around with them!
	      height : 700,
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
		var html = $('#ingredient_info').html();
		origHTML = html;
		$('#ingredient_info').empty();
		var textarea = $('<textarea id="tinymce" style="width: 100%; height: 400px;">'+ html + '</textarea>').appendTo('#ingredient_info');
		
		if (RMConfig.richEditor == 'tinymce') {
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
					    saveInfo(html);
				    }
			    });
			    ed.addButton('myclose', {
				    title : 'Close without saving',
				    //image : 'themes/default/img/icons/save.gif',
				    image : 'icons/cancel.png',
				    onclick : function() {
					    this.remove();
					    saveInfo(null);
				    }
			    });
			}
		    });
		} else if (RMConfig.richEditor == 'ckeditor') {
		    $(textarea).ckeditor(function() {},
			{
			    filebrowserImageBrowseUrl : 'fed.php?editor=ckeditor&ingredient_id=' + ingredientId,
			    filebrowserImageWindowWidth : '800',
			    filebrowserImageWindowHeight : '700'
			}
		    );
		}
		
		var a = $('<a class="boring editsection" id="ingredient_info_header" href="javascript:void(0);" title="Finish editing and save changes"></a>').replaceAll('#ingredient_info_header');
		a.click(function() {
		    var html = '';
		    if (RMConfig.richEditor == 'tinymce') {
			html = $('#tinymce').tinymce().getContent();
			$('#tinymce').tinymce().remove();
		    } else if (RMConfig.richEditor == 'ckeditor') {
			editor = $('#tinymce').ckeditorGet();
			html = editor.getData();
			editor.destroy();
		    }
		    console.log(html);
		    saveInfo(html);
		    return false;
		});
		a.append('<img class="boring" src="icons/accept.png" width="16" height="16" alt="Finish Editing and save changes">');

		var a = $('<a class="boring editsection" id="ingredient_info_header2" href="javascript:void(0);" title="Finish editing without saving"></a>').insertBefore('#ingredient_info_header');
		a.click(function() {
		    if (RMConfig.richEditor == 'tinymce')
			$('#tinymce').tinymce().remove();
		    else if (RMConfig.richEditor == 'ckeditor')
			$('#tinymce').ckeditorGet().destroy();

		    saveInfo(null);
		    return false;
		});
		a.append('<img class="boring" src="icons/cancel.png" width="16" height="16" alt="Finish Editing without save">');
	    return false;
	}




























	$(function() {
	    $('form').submit(function() {
		$.ajax({
		    type: "POST",
		    timeout: 30000,
		    /* in ms */
		    url: this.action/* "ajax_form_ingredients.php" */,
		    dataType: "json",
		    data: $(this).serialize(),
		    success: function(data) {
			if (data.error == 0) {
			} else {
			    // ingredientId = -1;
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

	    $('#ingredient_info_header').click(activateEditor);







	    // make other stuff editable
	    $('#ingredient_name').editable(
		function(value, settings) {
    		    /* Clear errors */

		    if ($('#ingredient_name').data('tooltip')) {
			$('#ingredient_name').data('tooltip').hide();
			$('#ingredient_name').data('tooltip', null);
		    }

		    $('.error-field').removeClass('error-field');
		    
		    var sendData = {
			"ingredient_id": ingredientId,
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
				$('#ingredient_name').addClass('error-field');

				$('#ingredient_name').attr('title', data.errmsg);
				$('#ingredient_name').tooltip({
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
				$('#ingredient_name').data('tooltip').show();
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
			<a class="boring editsection" href="#" id="ingredient_qtys_header" onclick="switchQtyToEdit();" title="Edit">
			    <img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)"/>
			</a>
		    </span>
		    <span>Quantities</span>
		</h2>
		<div id="ingredient_qtys" class="row clearfix">
		    <div id="ingredient_qtyunit" class="leftfixed">
			Quantity: 100g
		    </div>

		    <div id="ingredient_typical_qtyunit" class="rightfixed">
			Typical unit weight: 550g
		    </div>
		</div>

		<h2 style="margin-bottom: 0px;">
		    <span class="editsection">
			<a class="boring editsection" href="#" id="ingredient_nutritional_header" onclick="switchNutritionalToEdit();" title="Edit">
			    <img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)"/>
			</a>
		    </span>
		    <span>Nutritional Information</span>
		</h2>

		<div id="ingredient_nutritional_information" class="row clearfix">
		    <form id="ingredient_nutri_form" name="ingredient_nutri_form" method="post" action="ajax_editable.php">
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
		    </form>
		</div>

		<h2 style="margin-bottom: 0px;">
		    <span class="editsection">
			<a class="boring editsection" href="#" id="ingredient_additional_nutritional_header"  onclick="switchNutrientsToEdit();" title="Edit">
			    <img class="boring" src="icons/table_edit.png" width="16" height="16" alt="(edit)"/>
			</a>
		    </span>
		    <span>Additional Nutritional Information</span>
		</h2>

		<div id="ingredient_nutrients" class="row clearfix">
		    <form id="ingredient_nutrients_form" name="ingredient_nutrients_form" method="post" action="ajax_editable.php">
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
		    </form>
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
