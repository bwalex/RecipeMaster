<?php

/* DB Settings */
$globalConfig['db']['PDOString'] = "mysql:host=localhost;dbname=recipemaster";
$globalConfig['db']['PDOUser'] = "root";
$globalConfig['db']['PDOPassword'] = "";

/* Photo Class Settings */
$globalConfig['photo']['Path'] = "photos/"; 
$globalConfig['photo']['ThumbPath'] = "thumbs/";
$globalConfig['photo']['ThumbMaxWidth'] = 200;
$globalConfig['photo']['ThumbMaxHeight'] = 200;

/* Photo Class UI Settings */
$globalConfig['photo']['Viewer'] = "highslide"; //Valid options: highslide, fancybox, colorbox, prettyPhoto
$globalConfig['photo']['Colorbox']['Style'] = 1;       // Valid options: 1-5 (except 2, which is ugly!!)

/* Text Editor Settings */
$globalConfig['text']['richEditor'] = "tinymce"; //Valid options: tinymce, ckeditor

?>
