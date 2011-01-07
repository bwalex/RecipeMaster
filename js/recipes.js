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
    var row = $('<div class="row sortable-outline"></div>');

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

function copyrecipe(id) {
    document.copy_recipe.recipe_id.value = id;
    $(document.copy_recipe).submit();
    return false;
}

function deleterecipe(id) {
    $.confirm({
	    'title'		: 'Delete Confirmation',
	    'message'	: 'You are about to delete this item. <br />It cannot be restored at a later time! Continue?',
	    'buttons'	: {
		    'Yes' : {
			    'class'	: 'blue',
			    'action': function(){
				document.delete_recipe.recipe_id.value = id;
				$(document.delete_recipe).submit();
			    }
		    },
		    'No' : {
			    'class'	: 'gray',
			    'action': function(){}	// Nothing to do in this case. You can as well omit the action property.
		    }
	    }
    });
    return false;
}

function deletePhoto(id) {
    document.delete_photo.recipe_id.value = recipeId;
    document.delete_photo.photo_id.value = id;
    $(document.delete_photo).submit();
    return false;
}
