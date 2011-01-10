<?php

include('config.php');
include('class/Photo.php');
include('class/Nutrient.php');
include('class/Ingredient.php');
include('class/Recipe.php');

function db_connect()
{
	global $globalConfig;
	$db = new PDO($globalConfig['db']['PDOString'],
		      $globalConfig['db']['PDOUser'],
		      $globalConfig['db']['PDOPassword']);

	/* Set it so that db errors throw exceptions */
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	return $db;
}

abstract class Model {
   
   public function toArray() {
        return $this->processArray(get_object_vars($this));
    }
    
    private function processArray($array) {
        foreach($array as $key => $value) {
            if (is_object($value)) {
                $array[$key] = $value->toArray();
            }
            if (is_array($value)) {
                $array[$key] = $this->processArray($value);
            }
        }
        // If the property isn't an object or array, leave it untouched
        return $array;
    }
    
    public function __toString() {
        return json_encode($this->toArray());
    }
    public function getJSON() {
        return json_encode($this->toArray());
    }
}



function delete_photos($type, $parent_id, $keep = array()) {
	$db = db_connect();

	$photo_table = $type."_photos";

	$query = "SELECT id FROM ".$photo_table." WHERE parent_id=:parent_id ";
	$tokens = array(':parent_id' => $parent_id);

	if (!empty($keep)) {
		$i = 0;
		foreach ($keep as $entry) {
			$query .= " AND NOT id=:id_".$i;
			$tokens[":id_".$i] = $entry;
			$i++;
		}
	}

	$preparedStatement = $db->prepare($query);	
	$preparedStatement->execute($tokens);
	$result = $preparedStatement->fetchAll();
	$n = 0;

	foreach($result as $row) {
		$id = $row['id'];
		$photo = new Photo($type, $id);
		$n += $photo->delete();
	}
	return $n;	
}

function get_photos($type, $parent_id) {
	$photos = array();
	$photo_table = $type."_photos";

	$db = db_connect();
	$preparedStatement = $db->prepare("SELECT id FROM ".$photo_table." WHERE parent_id=:parent_id");
	$preparedStatement->execute(array(':parent_id' => $parent_id));
	$result = $preparedStatement->fetchAll();

	foreach($result as $row) {
		$id = $row['id'];
		$photo = new Photo($type, $id);
		array_push($photos, $photo);
	}

	return $photos;
}


function get_all_nutrients($restrict_query = '', $tokens = NULL) {
	$nutrients = array();
	
	$db = db_connect();
	if ($restrict_query == '') {
		$result = $db->query('SELECT * FROM nutrients');
	} else {
		$preparedStatement = $db->prepare('SELECT id FROM nutrients '.$restrict_query);
		//echo $preparedStatement->queryString;
		$preparedStatement->execute($tokens);
		$result = $preparedStatement->fetchAll();
	}
	$i = 0;
	foreach ($result as $row) {
		$nutrients[$i++] = new Nutrient($row['id']);
	}

	return $nutrients;
}


function get_nutrients_count($restrict_query = '', $tokens = NULL) {
	$nutrients = array();
	
	$db = db_connect();
	if ($restrict_query == '') {
		$result = $db->query('SELECT COUNT(id) FROM nutrients');
		return $result->fetchColumn();
	} else {
		$preparedStatement = $db->prepare('SELECT COUNT(id) FROM nutrients '.$restrict_query);
		$preparedStatement->execute($tokens);
		return $preparedStatement->fetchColumn();
	}
}


function get_all_ingredients($restrict_query = '', $tokens = NULL) {
	$ingredients = array();
	
	$db = db_connect();
	if ($restrict_query == '') {
		$result = $db->query('SELECT * FROM ingredients');
	} else {
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$preparedStatement = $db->prepare('SELECT id FROM ingredients '.$restrict_query);
		//echo $preparedStatement->queryString;
		$preparedStatement->execute($tokens);
		$result = $preparedStatement->fetchAll();
	}
	$i = 0;
	foreach ($result as $row) {
		$ingredients[$i++] = new Ingredient($row['id']);
	}

	return $ingredients;
}


function get_ingredients_count($restrict_query = '', $tokens = NULL) {
	$ingredients = array();
	
	$db = db_connect();
	if ($restrict_query == '') {
		$result = $db->query('SELECT COUNT(id) FROM ingredients');
		return $result->fetchColumn();
	} else {
		$preparedStatement = $db->prepare('SELECT COUNT(id) FROM ingredients '.$restrict_query);
		$preparedStatement->execute($tokens);
		return $preparedStatement->fetchColumn();
	}
}



function get_all_recipes($restrict_query = '', $tokens = NULL) {
	$ingredients = array();
	
	$db = db_connect();
	if ($restrict_query == '') {
		$result = $db->query('SELECT id FROM recipes');
	} else {
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$preparedStatement = $db->prepare('SELECT id FROM recipes '.$restrict_query);
		//echo $preparedStatement->queryString;
		$preparedStatement->execute($tokens);
		$result = $preparedStatement->fetchAll();
	}
	$i = 0;
	foreach ($result as $row) {
		$recipes[$i++] = new Recipe($row['id']);
	}

	return $recipes;
}

function get_recipes_count($restrict_query = '', $tokens = NULL) {
	$ingredients = array();
	
	$db = db_connect();
	if ($restrict_query == '') {
		$result = $db->query('SELECT COUNT(id) FROM recipes');
		return $result->fetchColumn();
	} else {
		$preparedStatement = $db->prepare('SELECT COUNT(id) FROM recipes '.$restrict_query);
		$preparedStatement->execute($tokens);
		return $preparedStatement->fetchColumn();
	}
}


function print_header() {
	echo '
	<div id="header" class="container_16">
	    <div id="logo">
		<img src="images/recipe_master.png" alt="RecipeMaster"/>
	    </div>
	</div>
	<div id="navbar" class="container_16 clearfix">
		<div class="grid_2">
		    <a href="ingredients.php">Ingredients</a>
		</div>
		<div class="grid_2">
		    <a href="recipes.php">Recipes</a>
		</div>
	</div>
    ';
}

function print_footer() {
	echo '
	<div id="footer" class="container_16 clearfix">
	    <div style="text-align: left;" class="grid_2">
		<a style="display: block;" class="boring" href="http://validator.w3.org/check?uri=referer"><img class="boring" src="icons/valid-html5.png" alt="Valid HTML 5" height="31" width="88"/></a>
	    </div>
	    <div class="grid_14">
		<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/88x31.png" /></a><br/>&copy; <span>Alex Hornung</span> <!-- span: property="cc:attributionName" -->
		<!--<h4>&copy; 2010, 2011 Alex Hornung</h4>-->
	    </div>
	    <div id="lastfooter" class="container_16">
	    built using <a href="http://960.gs/">960 gs</a>,
	    <a href="http://www.famfamfam.com/lab/icons/silk/">FAMFAMFAM silk icons</a>,
	    <a href="http://www.datatables.net/">DataTables</a>,
	    <a href="http://highslide.com/">Highslide</a>,
	    <a href="http://www.jquery.com/">jQuery</a>,
	    <a href="http://flowplayer.org/tools/index.html">jQuery Tools</a>,
	    <a href="http://jqueryui.com/">jQuery UI</a>
	    </div>
	</div>
    ';
}

function let_to_num($v){ //This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
    $l = substr($v, -1);
    $ret = substr($v, 0, -1);
    switch(strtoupper($l)){
    case 'P':
        $ret *= 1024;
    case 'T':
        $ret *= 1024;
    case 'G':
        $ret *= 1024;
    case 'M':
        $ret *= 1024;
    case 'K':
        $ret *= 1024;
        break;
    }
    return $ret;
}

function printExtraHeaders()
{
	global $globalConfig;

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

	if ($globalConfig['text']['richEditor'] == "tinymce") {
		echo '
		<!-- tinyMCE -->
		<script type="text/javascript" src="tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
		<script type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
		';
	} else if ($globalConfig['text']['richEditor'] == "ckeditor") {
		echo '
		<!-- CKEditor -->
		<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
		<script type="text/javascript" src="ckeditor/adapters/jquery.js"></script>
		';
	}
}

?>
