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

function deleteallingredients(form, id) {
    document.getElementById(form).ingredient_count.value = 0;
    var elem = document.getElementById(id);
    while (elem.hasChildNodes()) {
        elem.removeChild(elem.firstChild);
    }
}

function addingredient(form, id, before_node, qty, unit, name, method) {
    var row = document.createElement("div");
    row.className = 'row';

    var input = document.createElement("input");
    input.type = "text";
    input.size = "5";
    input.name = "ing_qty[]";
    input.value = qty;

    row.appendChild(input);

    select = createselect("ing_unit[]", ["", "g", "ml", "mg", "kg", "l"]);
    select.value = unit;
    row.appendChild(select);

    var input = document.createElement("input");
    input.type = "text";
    input.size = "35";
    input.name = "ing_name[]";
    input.value = name;

    row.appendChild(input);

    $(input).autocomplete({
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

    var input2 = document.createElement("input");
    input2.type = "text";
    input2.size = "20";
    input2.name = "ing_method[]";
    input2.value = method;
    $(input2).one('focus', function() {
        if (this.value == 'method (e.g. diced)')
            this.value="";
    });

    row.appendChild(input2);

    a = document.createElement("a");
    a.href = '#';
    a.className = 'boring';
    a.onclick = function() {
        parentrow = this.parentNode;
        parentrow.parentNode.removeChild(parentrow);
    }

    img = document.createElement("img");
    img.alt = 'remove ingredient field';
    img.height = '16';
    img.width = '16';
    img.src = 'icons/cross.png';
    img.className = 'boring';
    a.appendChild(img);

    row.appendChild(a);

    a = document.createElement("a");
    a.href = '#';
    a.className = 'boring';
    a.onclick = function() {
        elem = this.parentNode.nextSibling;
        /*
         * NOTE: elem = null is ok here since that's the
         * case where we append an extra row at the end.
         */
        addingredient(form, id, elem, '100', 'g', '', 'method (e.g. diced)');
    }

    img = document.createElement("img");
    img.alt = 'add ingredient field';
    img.height = '16';
    img.width = '16';
    img.src = 'icons/add.png';
    img.className = 'boring';
    a.appendChild(img);

    row.appendChild(a);

    var elem = document.getElementById(id);
    if (before_node == null) {
        elem.appendChild(row);
    } else {
        elem.insertBefore(row, before_node);
    }

    input.focus();
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

        deleteallingredients('add_recipe', 'ingredient_add_inputs');
        deleteallphotos('add_recipe', 'photo_add_inputs');

        document.add_recipe.recipe_name.value = recipe.name;
        document.add_recipe.recipe_instructions.value = recipe.instructions;
        CKEDITOR.instances.add_instructions_editor.setData(recipe.instructions,
        function() {
            this.checkDirty(); // true
        });
        for (var i in recipe.ingredients) {
            addingredient('add_recipe', 'ingredient_add_inputs', null, recipe.ingredients[i].qty, recipe.ingredients[i].unit, recipe.ingredients[i].Ingredient.name, recipe.ingredients[i].method);
        }
        for (var i in recipe.photos) {
            addphoto('add_recipe', 'photo_add_inputs', null, recipe.photos[i].id, recipe.photos[i].photo, recipe.photos[i].thumb, recipe.photos[i].caption);
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
			$ret = $(document.add_recipe).submit();
			if ($ret == true)
			    $(this).dialog("close");
		    }
		}
	    ]
	});
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

function deleteallphotos(form, id) {
    var elem = document.getElementById(id);
    while (elem.hasChildNodes()) {
        elem.removeChild(elem.firstChild);
    }
}

function addphoto(form, id, before_node, photoid, photo, thumb, caption) {
    var row = document.createElement("div");
    row.className = 'row';

    var input = document.createElement("input");
    input.type = "hidden";
    input.name = "photo_id[]";
    input.value = photoid;
    row.appendChild(input);

    if (photo != '') {
        a = document.createElement("a");
        a.href = photo;
        a.title = caption;

        // Highslide
        a.className = 'highslide';
        a.onclick = function() {
            return hs.expand(this, config1 )
        }
        // Fancybox
        //$(a).fancybox();

    
        img = document.createElement("img");
        img.alt = 'photo of dish';
        img.src = thumb;
        a.appendChild(img);
    
        row.appendChild(a);
    } else {
        var input = document.createElement("input");
        input.type="file";
        input.name = "recipe_photo[]";
        row.appendChild(input);
    }

    var input = document.createElement("input");
    input.type = "text";
    input.size = "30";
    input.name = "photo_caption[]";
    input.value = caption;
    row.appendChild(input);

    a = document.createElement("a");
    a.href = '#';
    a.className = 'boring';
    a.photoId = photoid;
    a.onclick = function() {
	deletePhoto(this.photoId);
        parentrow = this.parentNode;
        parentrow.parentNode.removeChild(parentrow);
    }

    img = document.createElement("img");
    img.alt = 'remove photo field';
    img.height = '16';
    img.width = '16';
    img.src = 'icons/cross.png';
    img.className = 'boring';
    a.appendChild(img);

    row.appendChild(a);

/*
    a = document.createElement("a");
    a.href = '#';
    a.className = 'boring';
    a.onclick = function() {
        elem = this.parentNode.nextSibling;
        addphoto(form, id, elem, '-1', '', '', 'Description');
    }

    img = document.createElement("img");
    img.alt = 'add photo field';
    img.height = '16';
    img.width = '16';
    img.src = 'icons/add.png';
    img.className = 'boring';
    a.appendChild(img);

    row.appendChild(a);
*/

    var elem = document.getElementById(id);
    if (before_node == null) {
        elem.appendChild(row);
    } else {
        elem.insertBefore(row, before_node);
    }
    input.focus();
}


function addUpload(container, recipeId) {
    var outerdiv = document.createElement('div');
    var iframe = document.createElement('iframe');
    iframe.id = 'upload_target_'+seq;
    iframe.name = 'upload_target_'+seq;
    iframe.style.width = "0px";
    iframe.style.height = "0px";
    iframe.style.borderWidth = "0px";
    
    outerdiv.appendChild(iframe);
    var form = document.createElement('form');
    form.name = "photo_form";
    form.action = "ajax_form_photos.php";
    form.target = "upload_target_"+seq;
    form.method = "post";
    form.enctype = "multipart/form-data";

    var div = document.createElement('div');
    div.id = "process-div-"+seq;
    div.style.height = '0px';
    //var text = document.createTextNode('Uploading...');
    //div.appendChild(text);
    var img = document.createElement('img');
    img.src = 'icons/load_bar2.gif';
    div.appendChild(img);
    div.style.visibility = 'hidden';
    form.appendChild(div);
    
    var div = document.createElement('div');
    div.id = "input-div-"+seq;
    var input = document.createElement('input');
    input.name = "recipe_photo";
    input.type = "file";
    input.seq = seq;
    input.onchange = function() {
        document.getElementById('process-div-'+this.seq).style.visibility = 'visible';
        document.getElementById('process-div-'+this.seq).style.height = 'auto';
        document.getElementById('input-div-'+this.seq).style.visibility = 'hidden';
        document.getElementById('input-div-'+this.seq).style.height = '0px';
        this.parentNode.parentNode.submit();
    }
    div.appendChild(input);

    var input = document.createElement('input');
    input.name = "sequence_id";
    input.value = seq;
    input.type = "hidden";
    div.appendChild(input);

    var input = document.createElement('input');
    input.name = "recipe_id";
    input.value = recipeId;
    input.type = "hidden";
    div.appendChild(input);

    var input = document.createElement('input');
    input.name = "form_type";
    input.value = 'add_photo';
    input.type = "hidden";
    div.appendChild(input);

    var input = document.createElement('input');
    input.name = "photo_caption";
    input.value = 'Edit me!';
    input.type = "hidden";
    div.appendChild(input);


    form.appendChild(div);
    
    outerdiv.appendChild(form);
    container.appendChild(outerdiv);
    seq = seq+1;
}

function uploadDone(data) {
    if (data.error) {
        var div = document.getElementById('process-div-'+data.seq);
        div.removeChild(div.firstChild);
        text = document.createTextNode('Upload failed: ' + data.errmsg[0]);
        div.appendChild(text);
    } else {
        var div = document.getElementById('process-div-'+data.seq);
        div.removeChild(div.firstChild);
        addphoto(null, 'process-div-'+data.seq, null, data.id, data.photo, data.thumb, data.caption);
    }
}
