function deleteallingredients(form, id) {
    document.getElementById(form).ingredient_count.value = 0;
    var elem = document.getElementById(id);
    while (elem.hasChildNodes()) {
        elem.removeChild(elem.firstChild);
    }
}

function addingredient(form, id, before_node, qty, name, method) {
    ning = Number(document.getElementById(form).ingredient_count.value) + 1;
    var row = document.createElement("div");
    row.className = 'row';

    var input = document.createElement("input");
    input.type = "text";
    input.size = "10";
    input.name = "ing_qty[]";
    input.value = qty;

    row.appendChild(input);

    var input = document.createElement("input");
    input.type = "text";
    input.size = "50";
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
        addingredient(form, id, elem, '100g', '', 'diced');
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
    document.getElementById(form).ingredient_count.value = ning;
    input.focus();
}

function editrecipe(id) {
    $.post("ajax_formdata.php", {
        recipe: id
    },
    function(recipe) {
        // format and output result
        if (recipe.exception) {
            alert(recipe.exception);
            return;
        }

        deleteallingredients('edit_recipe', 'ingredient_edit_inputs');
        deleteallphotos('edit_recipe', 'photo_edit_inputs');

        document.edit_recipe.recipe_name.value = recipe.name;
        document.edit_recipe.recipe_instructions.value = decodeURIComponent(recipe.instructions);
        CKEDITOR.instances.edit_instructions_editor.setData(decodeURIComponent(recipe.instructions),
        function() {
            this.checkDirty(); // true
        });
        for (var i in recipe.ingredients) {
            addingredient('edit_recipe', 'ingredient_edit_inputs', null, recipe.ingredients[i].qty + recipe.ingredients[i].unit, recipe.ingredients[i].name, recipe.ingredients[i].method);
        }
        for (var i in recipe.photos) {
            addphoto('edit_recipe', 'photo_edit_inputs', null, recipe.photos[i].id, recipe.photos[i].photo, recipe.photos[i].thumb, recipe.photos[i].caption);
        }
        document.edit_recipe.recipe_id.value = recipe.id;

        $('#dialog_edit').dialog('open');
    },
    'json');
}

function deleterecipe(id) {
    document.delete_recipe.recipe_id.value = id;
    document.delete_recipe.submit();
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
        $(a).fancybox();
    
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
    a.onclick = function() {
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

    a = document.createElement("a");
    a.href = '#';
    a.className = 'boring';
    a.onclick = function() {
        elem = this.parentNode.nextSibling;
        /*
         * NOTE: elem = null is ok here since that's the
         * case where we append an extra row at the end.
         */
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

    var elem = document.getElementById(id);
    if (before_node == null) {
        elem.appendChild(row);
    } else {
        elem.insertBefore(row, before_node);
    }
    document.getElementById(form).ingredient_count.value = ning;
    input.focus();
}