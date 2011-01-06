<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
    $editor = $_REQUEST['editor'];

    if ($editor == 'tinymce') {
        echo '
        <script type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce_popup.js"></script>
        <script type="text/javascript">

            var FileBrowserDialogue = {
                init : function () {
                    // Here goes your code for setting your custom things onLoad.
                },
                mySubmit : function () {
                    var URL = $("img.active").data("photo");
                    var win = tinyMCEPopup.getWindowArg("window");
            
                    // insert information now
                    win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;
            
                    // are we an image browser
                    if (typeof(win.ImageDialog) != "undefined") {
                        // we are, so update image dimensions...
                        if (win.ImageDialog.getImageData)
                            win.ImageDialog.getImageData();
            
                        // ... and preview if necessary
                        if (win.ImageDialog.showPreviewImage)
                            win.ImageDialog.showPreviewImage(URL);
                    }
            
                    // close popup window
                    tinyMCEPopup.close();
                }
            }
            tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);
        </script>
        ';
    } else if ($editor == 'ckeditor') {
        echo '
        <style type="text/css">
            body {
                background: none repeat scroll 0 0 #F0F0EE;
                font-family: Verdana,Arial,Helvetica,sans-serif;
                font-size: 11px;
                margin: 8px 8px 0;
                padding: 0;
            }
        </style>
        <script type="text/javascript">

            var FileBrowserDialogue = {
                init : function () {
                    // Here goes your code for setting your custom things onLoad.
                },
                mySubmit : function () {
                    var URL = $("img.active").data("photo");
                    window.opener.CKEDITOR.tools.callFunction('.$_REQUEST["CKEditorFuncNum"].', URL);
                    window.close();
                }
            }
        </script>
        ';
    }
?>
    <link type="text/css" href="css/fed.css" rel="stylesheet"/>
    <script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
    <script type="text/javascript" src="js/jquery.tools.min.js"></script>

    <script type="text/javascript">
        var recipeId = <?php echo $_REQUEST['recipe_id'] ?>;
        


        function makeScrollable() {
            $(".scrollable").scrollable();
     
            $(".items img").click(function() {
             
                    // see if same thumb is being clicked
                    if ($(this).hasClass("active")) { return; }

                    var url = 'resize.php?width=600&height=340&photo=' + $(this).data("photo");
             
                    // get handle to element that wraps the image and make it semi-transparent
                    var wrap = $("#image_wrap").fadeTo("medium", 0.5);
                    var img = new Image();
             
                    // call this function after it's loaded
                    img.onload = function() {
                            // make wrapper fully visible
                            wrap.fadeTo("fast", 1);
             
                            // change the image
                            wrap.find("img").attr("src", url);  
                    };
             
                    // begin loading the image
                    img.src = url;
             
                    // activate item
                    $(".items img").removeClass("active");
                    $(this).addClass("active");
                    $('#photo-caption').replaceWith('<p id="photo-caption">' + $(this).data('photoCaption') + '</p>');
             
            // when page loads simulate a "click" on the first image
            }).filter(":first").click();
        }
    
        $(function() {
            $.post("ajax_formdata.php", {
                    recipe: recipeId
                },
                function(recipe) {
                    if (recipe.exception) {
                        $('#content').empty();
                        $('#content').append('<h2>' + recipe.exception + '</h2>');
                        return;
                    }
                    var count = recipe.photos.length;
                    if (count == 0) {
                        $('#content').empty();
                        $('#content').append('<h2>Recipe &quot;' + recipe.name + '&quot; has no photos yet. Please upload some and try again.</h2>');
                        return;
                    }
                    var segments = Math.ceil(count/5);
                    var i = 0;

                    // Hide navigation buttons if there's only one segment
                    if (segments == 1)
                        $('.browse').addClass('disabled');
    
                    for (var s = 0; s < segments; s = s+1) {
                        var div = $('<div></div>').appendTo('.items');
                        for (var j = 0; (i < count) && (j < 5); i = i+1, j = j+1) {
                            var img = $('<img src="resize.php?width=100&height=75&photo='+recipe.photos[i].thumb+'" alt="photo of dish"/>').appendTo(div);
                            img.data('photo', recipe.photos[i].photo);
                            img.data('photoCaption', recipe.photos[i].caption);
                        }
                    }
                    makeScrollable();
                }
            );
        });
    </script>

    <title>Recipe Image Browser</title>
</head>

<body>
<div id="content">
<div id="image_browser" class="clearfix">
    <!-- wrapper element for the large image --> 
    <div id="image_wrap"> 
     
            <!-- Initially the image is a simple 1x1 pixel transparent GIF --> 
            <img src="http://static.flowplayer.org/tools/img/blank.gif"/>
            <p id="photo-caption"></p>
    </div>
    
    
    <!-- "previous page" action --> 
    <a class="prev browse left"></a> 
     
    <!-- root element for scrollable --> 
    <div class="scrollable">   
       
       <!-- root element for the items --> 
       <div class="items"> 
       
       </div> 
       
    </div> 
     
    <!-- "next page" action --> 
    <a class="next browse right"></a>
</div>
<div style="text-align: center; margin-top: 20px;">
<a href="#" onClick="FileBrowserDialogue.mySubmit();"><span id="select-button">Select image</span></a>
</div>
</div>
</body>
</html>
