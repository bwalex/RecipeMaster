var seq = 0;



function editableConnector(settings) {
    this.id = settings.id;
    this.editType = settings.editType;
    this.type = settings.type;
    this.success = settings.success;
    this.error = settings.error;
    this.privateData = {};

    this.data = function(key, val) {
	if (val) {
	    this.privateData[key] = val;
	    return null;
	} else {
	    return this.privateData[key];
	}
    }

    this.send = function() {
	if (typeof(settings.data) == 'function') {
	    this.sendData = settings.data(this.privateData);
	} else if (typeof(settings.data) == 'object') {
	    this.sendData = settings.data;
	} else if (typeof(settings.data) == 'string') {
	    this.sendData = settings.data;
	} else {
	    this.sendData = {};
	}

	if (typeof(this.sendData) == 'string') {
	    if (this.sendData.length > 0)
		this.sendData = this.sendData + '&';
	    this.sendData = this.sendData + $.param({
		type: this.type,
		id : this.id,
		edit_type: this.editType
	    });
	} else {
	    this.sendData.type = this.type;
	    this.sendData.id = this.id;
	    this.sendData.edit_type = this.editType;
	}
	console.log(this);
	$.ajax({
	    "dataType": 'json',
	    "type": "GET",
	    "url": "ajax_editable.php",
	    "data": this.sendData,
	    "context": this,
	    "success": function(data) {
		console.log(this);
		if (data.error != 0) {
		    var r = false;
		    if (typeof(this.error) == 'function')
			r = this.error(data);
		    else if (typeof(this.error) == 'string')
			r = window[this.error]();
		    if (r == false)
			alert(data.errmsg);
		} else {
		    if (typeof(this.success) == 'function')
			this.success(data);
		    else if (typeof(this.success) == 'string')
			window[this.success]();
		}
	    }
	});
    }

    if (settings.sendNow == true)
	this.send();
}






var qtipSettings = {
    show: {
	when: false, // Don't specify a show event
	ready: true // Show the tooltip when ready
     },
     hide: false, // Don't specify a hide event
    position: {
	corner: {
	    tooltip: 'bottomMiddle',
	    target: 'topMiddle'
	}
    },
    style: {
	'font-weight': 'bold',
	'font-size': '14px',
	background: '#ffAAAA',
	color: 'black',
	border: {
	   width: 7,
	   radius: 5,
	   color: '#ffAAAA'
	},
	tip: 'bottomMiddle' // Notice the corner value is identical to the previously mentioned positioning corners
    }
};



function addToolTip(jqi, msg, className, settings) {
    jqi.attr('title', msg);
    if (className != null)
	jqi.addClass(className);
    if (settings == null)
	jqi.qtip(qtipSettings);
    else
	jqi.qtip(settings);
}

function delToolTip(jqi, className) {
    var e;
    if (jqi != null) {
	e = jqi;
    } else {
	e = $('.'+className);
	e.removeClass(className);
    }
    e.each(function() {
	if ($(this).data('qtip'))
	    $(this).qtip('destroy');
    });

}











function createPhoto(photo, thumb, caption) {
    var a = $('<a href="'+ photo +'" title="'+ caption +'" class="highslide">');
    if (thumb != null) {
        a.append('<img src="'+ thumb +'" alt="photo of dish">');
    } else {
        a.text(caption);
    }

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

    return a;
}

function enableLightbox(jqi) {
    console.log(jqi);
    if (RMConfig.photoViewer == 'fancybox') {
	jqi.fancybox();
    } else if (RMConfig.photoViewer == 'colorbox') {
	jqi.colorbox({maxHeight:"100%", maxWidth:"100%"});
    } else if (RMConfig.photoViewer == 'highslide') {
	jqi.each(function() {
	    this.onclick = function() {
		return hs.expand(this, config1);
	    };
	});
    } else if (RMConfig.photoViewer == 'prettyPhoto') {
	jqi.prettyPhoto({theme:'facebook'});
    }
}

function createPhotoRow(parentType, parentId, photoId, photo, thumb, caption) {
    var row = $('<div class="row"></div>');

    var span = $('<span></span>').appendTo(row);
    span.append(createPhoto(photo, thumb, caption));

    // with jeditable:
    var editCaption = $('<span>'+caption+'</span>').appendTo(row);
    editCaption.data('photoId', photoId);
    editCaption.data('parentType', parentType);
    editCaption.data('parentId', parentId);
    $(editCaption).editable(
	function(value, settings) {
	    var sendData = {
                "parent_id"  : $(this).data('parentId'),
		"parent_type": $(this).data('parentType'),
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

function addUpload(container, parentType, parentId) {
    var outerdiv = $('<div></div>');
    $('<iframe id="'+'upload_target_'+seq+'" name="'+'upload_target_'+seq+'" style="width: 0px; height: 0px; border-width: 0px;">').appendTo(outerdiv);
    
    var div = $('<div id="process-div-'+seq +'" style="height: 0px; visibility: hidden;">').appendTo(outerdiv);
    div.append('<img src="icons/load_bar2.gif" alt="Uploading...">');

    var div = $('<div id="input-div-'+seq +'">').appendTo(outerdiv);
    var form = $('<form name="photo_form" target="upload_target_'+seq+'" action="ajax_form_photos.php" method="post" enctype="multipart/form-data">').appendTo(div);
    var input = $('<input type="file" name="'+parentType+'_photo">').appendTo(form);
    input.data('seq', seq);
    input.change(function() {	
	var id = $(this).data('seq');
	$('#process-div-'+id).css({'visibility' : 'visible', 'height' : 'auto'});
	$('#input-div-'+id).css({'visibility' : 'hidden', 'height' : '0px'});
        this.parentNode.submit();
    });
    
    form.append('<input type="hidden" name="sequence_id" value="'+seq+'">');
    form.append('<input type="hidden" name="parent_type" value="'+parentType+'">');
    form.append('<input type="hidden" name="parent_id" value="'+parentId+'">');
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
    return false;
}

function uploadDone(data) {
    if (data.error) {
	$('#process-div-'+data.seq).parent().replaceWith('<div class="form-error photo-form-error"><p>Upload failed: ' + data.errmsg[0] + '</p></div>');
    } else {
	$('#process-div-'+data.seq).parent().replaceWith(createPhotoRow(data.parentType, data.parentId, data.id, data.photo, data.thumb, data.caption));
    }
}