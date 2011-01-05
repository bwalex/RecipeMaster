<?php


$globalConfig['photoViewer'] = "highslide"; //Valid options: highslide, fancybox, colorbox, prettyPhoto
$globalConfig['colorboxStyle'] = 1;       // Valid options: 1-5 (except 2, which is ugly!!)

$globalConfig['db']['PDOString'] = "mysql:host=localhost;dbname=recipemaster";
$globalConfig['db']['PDOUser'] = "root";
$globalConfig['db']['PDOPassword'] = "";


/* XXX: add image file size limit */

$globalConfig['photo']['Path'] = "photos/"; 
$globalConfig['photo']['ThumbPath'] = "thumbs/";
$globalConfig['photo']['ThumbMaxWidth'] = 200;
$globalConfig['photo']['ThumbMaxHeight'] = 200;
$globalConfig['photo']['Viewer'] = "highslide"; //Valid options: highslide, fancybox, colorbox, prettyPhoto
$globalConfig['photo']['Colorbox']['Style'] = 1;       // Valid options: 1-5 (except 2, which is ugly!!)

?>
