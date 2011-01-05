var seq = 0;

function createselect(name, options) {
    var select = document.createElement("select");
    select.name = name;

    for (var i in options) {
        option = document.createElement("option");
        option_value = document.createTextNode(options[i]);
        option.appendChild(option_value);
        select.appendChild(option);
    }

    return select;
}

function createIngredientRow(qty, unit, name, method) {
    var row = $('<div class="row"></div>');

    row.append('<input type="text" size="5" name="ing_qty[]" value="'+ qty +'">');

    select = createselect("ing_unit[]", ["", "g", "ml", "mg", "kg", "l"]);
    select.value = unit;
    row.append(select);

    var nameInput = $('<input type="text" size="30" name="ing_name[]" value="'+ name +'">').appendTo(row);
    var methodInput = $('<input type="text" size="15" name="ing_method[]" value="'+ method +'">').appendTo(row);

    methodInput.one('focus', function() {
        if (this.value == 'method (e.g. diced)')
            this.value="";
    });

    var a = $('<a href="#" class="boring"></a>').appendTo(row);
    a.click(function() {
	$(this).parent().remove();
    });
    a.append('<img alt="remove ingredient field" width="16" height="16" src="icons/cross.png" class="boring">');

    var a = $('<a href="#" class="boring"></a>').appendTo(row);
    a.click(function() {
	$('[name="ing_method[]"]').trigger('focus');
	$(this).parent().after(createIngredientRow('100', 'g', '', 'method (e.g. diced)'));
    });
    a.append('<img alt="add ingredient field" width="16" height="16" src="icons/add.png" class="boring">');
    
    nameInput.autocomplete({
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
        minLength: 2
    });

    return row;
}

function editrecipe(id) {
    recipeId = -1;
    $.post("ajax_formdata.php", {
        recipe: id
    },
    function(recipe) {
        // format and output result
        if (recipe.exception) {
            alert(recipe.exception);
            return;
        }

        $('#ingredient_add_inputs').empty();
	$('#photo_add_inputs').empty();

        document.add_recipe.recipe_name.value = recipe.name;
	document.add_recipe.recipe_serves.value = recipe.serves;
        document.add_recipe.recipe_instructions.value = recipe.instructions;
        CKEDITOR.instances.add_instructions_editor.setData(recipe.instructions,
        function() {
            this.checkDirty(); // true
        });
        for (var i in recipe.ingredients) {
	    $('#ingredient_add_inputs').append(createIngredientRow(recipe.ingredients[i].qty, recipe.ingredients[i].unit, recipe.ingredients[i].Ingredient.name, recipe.ingredients[i].method));
        }
        for (var i in recipe.photos) {
	    $('#photo_add_inputs').append(createPhotoRow(recipe.photos[i].id, recipe.photos[i].photo, recipe.photos[i].thumb, recipe.photos[i].caption));
        }
        document.add_recipe.recipe_id.value = recipe.id;
	recipeId = recipe.id;
        document.add_recipe.form_type.value = 'edit_recipe';

	$('#dialog').dialog('option',
	{
	    title: 'Edit recipe',
	    autoOpen: false,
	    modal: true,
	    width: 800,
	    buttons: [
		{
		    id: "dialog-submit",
		    text: "Submit changes",
		    click: function() {
			recipeId = -1;
			$('input[name^="ing_method"]').trigger('focus');
			$ret = $(document.add_recipe).submit();
			if ($ret == true)
			    $(this).dialog("close");
		    }
		}
	    ]
	});
	document.getElementById('form-photoset').style.visibility = 'visible';
	clearMsgDivs();
	$('#dialog').dialog('open');
    },
    'json');
}

function deleterecipe(id) {
    document.delete_recipe.recipe_id.value = id;
    $(document.delete_recipe).submit();
}

function deletePhoto(id) {
    document.delete_photo.recipe_id.value = recipeId;
    document.delete_photo.photo_id.value = id;
    $(document.delete_photo).submit();
}


function createPhotoRow(photoId, photo, thumb, caption) {
    var row = $('<div class="row"></div>');

    var span = $('<span></span>').appendTo(row);
    var a = $('<a href="'+ photo +'" title="'+ caption +'" class="highslide">').appendTo(span);
    a.append('<img src="'+ thumb +'" alt="photo of dish">');

    if (RMConfig.photoViewer == 'highslide') {
	a.each(function() {
	    this.onclick = function() {
		return hs.expand(this, config1);
	    };
	});
    } else if (RMConfig.photoViewer == 'fancybox') {
	a.fancybox();
    } else if (RMConfig.photoViewer == 'colorbox') {
	a.colorbox({maxHeight:"100%", maxWidth:"100%"});
    } else if (RMConfig.photoViewer == 'prettyPhoto') {
	a.prettyPhoto({theme:'facebook'});
    }


    // with jeditable:
    var editCaption = $('<span>'+caption+'</span>').appendTo(row);
    editCaption.data('photoId', photoId);
    $(editCaption).editable(
	function(value, settings) {   
	    var sendData = {
		"recipe_id": recipeId,
		"form_type": "edit_photo",
		"photo_id" : $(this).data('photoId'),
		"where_ok" : "dialog-messages",
		"where_error" : "dialog-messages",
		"sequence_id" : -1,
		"photo_caption": value
	    };
	    //console.log(sendData);
	    $.ajax( {
		"dataType": 'json',
		"type": "GET",
		"url": "ajax_form_photos.php",
		"data": sendData,
		"success": function(data) {
		    console.log(this);
		    if (data.error == 0) {
		    } else {
			alert(data.errmsg);
		    }
		}
	    } );
	    return value;
	},
	{
	    width: '460px',
	    tooltip   : 'Click to edit...'
	}
    );

    // without jeditable:
    //row.append('<input type="text" size="30" name="photo_caption[]" value="' + caption +'">');
    //row.append('<input type="hidden" name="photo_id[]" value="' + photoId + '">');



    var a = $('<a href="#" class="boring"></a>').appendTo(row);
    a.data('photoId', photoId);
    a.click(function() {
	deletePhoto($(this).data('photoId'));
	$(this).parent().remove();
    });
    a.append('<img alt="remove photo field" width="16" height="16" src="icons/cross.png" class="boring">');

    return row;
}


function addUpload(container, recipeId) {
    var outerdiv = $('<div></div>');
    $('<iframe id="'+'upload_target_'+seq+'" name="'+'upload_target_'+seq+'" style="width: 0px; height: 0px; border-width: 0px;">').appendTo(outerdiv);
    
    var div = $('<div id="process-div-'+seq +'" style="height: 0px; visibility: hidden;">').appendTo(outerdiv);
    div.append('<img src="icons/load_bar2.gif" alt="Uploading...">');

    var div = $('<div id="input-div-'+seq +'">').appendTo(outerdiv);
    var form = $('<form name="photo_form" target="upload_target_'+seq+'" action="ajax_form_photos.php" method="post" enctype="multipart/form-data">').appendTo(div);
    var input = $('<input type="file" name="recipe_photo">').appendTo(form);
    input.data('seq', seq);
    input.change(function() {
	var id = $(this).data('seq');
	$('#process-div-'+id).css({'visibility' : 'visible', 'height' : 'auto'});
	$('#input-div-'+id).css({'visibility' : 'hidden', 'height' : '0px'});
        this.parentNode.submit();
    });
    
    form.append('<input type="hidden" name="sequence_id" value="'+seq+'">');
    form.append('<input type="hidden" name="recipe_id" value="'+recipeId+'">');
    form.append('<input type="hidden" name="form_type" value="add_photo">');
    form.append('<input type="hidden" name="photo_caption" value="Edit me!">');
    form.append('<input type="hidden" name="where_ok" value="dialog_messages">');
    form.append('<input type="hidden" name="where_error" value="dialog_messages">');

    /* Clear failed outerdivs/uploads */
    $('.photo-form-error').each(function() {
	$(this).remove();
    });
    $(container).append(outerdiv);
    seq = seq+1;
}

function uploadDone(data) {
    if (data.error) {
	$('#process-div-'+data.seq).parent().replaceWith('<div class="form-error photo-form-error"><p>Upload failed: ' + data.errmsg[0] + '</p></div>');
    } else {
	$('#process-div-'+data.seq).parent().replaceWith(createPhotoRow(data.id, data.photo, data.thumb, data.caption));
    }
}
