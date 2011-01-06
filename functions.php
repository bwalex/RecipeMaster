<?php

include('config.php');

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


class Photo {
	var $id;
	var $parent_id;
	var $type;
	var $caption;
	var $photo_path;
	var $photo_data;
	var $table;
	var $new;
	var $mime;
	var $extension;

	public function toArray() {
		$export['type'] = $this->type;
		$export['id'] = $this->id;
		$export['parent_id'] = $this->parent_id;
		$export['photo'] = $this->get();
		$export['thumb'] = $this->getThumbnail();
		$export['caption'] = $this->caption;
		$export['mime'] = $this->mime;
		return $export;
	}

	public function getJSON() {
	    return json_encode($this->toArray());
	}

	var $mime_types = array(
		'image/gif' => 'gif',
		'image/jpeg' => 'jpg',
		'image/png' => 'png',
	/*
		'application/x-shockwave-flash' => 'swf',
		'image/psd' => 'psd',
		'image/bmp' => 'bmp',
		'image/tiff' => 'tiff',
		'image/tiff' => 'tiff',
		'image/jp2' => 'jp2',
		'image/iff' => 'iff',
		'image/vnd.wap.wbmp' => 'bmp',
	*/
		'image/xbm' => 'xbm'
	/*
		'image/vnd.microsoft.icon' => 'ico'
	*/
	);

	function getAllowedMIMETypesString() {
		$types = '';
		foreach($this->mime_types as $mime => $ext) {
		    $types .= $mime . ', ';
		}
		$types = substr_replace( $types, "", -2 );
		return $types;
	}

	function getAllowedExtensionsString() {
		$exts = '';
		foreach($this->mime_types as $ext) {
			$exts .= $ext . ', ';
		}
		$exts = substr_replace( $exts, "", -2 );
		return $exts;
	}

	function Photo($type, $id = -1, $parent_id = -1, $caption = '', $data = NULL) {
		$photo_table = '';

		switch ($type) {
			case 'ingredient':
				$photo_table = 'ingredient_photos';
				break;
			case 'recipe':
				$photo_table = 'recipe_photos';
				break;
			default:
				throw new Exception('Unknown photo type');
		}
		$this->table = $photo_table;

		$db = db_connect();

		if ($id > -1) {
			$preparedStatement = $db->prepare("SELECT * FROM ".$photo_table." WHERE id=:id");
			$preparedStatement->execute(array(':id' => $id));
			$result = $preparedStatement->fetch();
			if (!$result) {
				throw new Exception('Object doesn\'t exist!');
			}
			$this->id = $result['id'];
			$this->parent_id = $result['parent_id'];
			$this->caption = $result['photo_caption'];
			$this->mime = $result['photo_mime'];
			$this->extension = $this->mime_types[$this->mime];
			$this->photo_data = NULL;
			$this->new = 0;
		} else {
			if ($data == NULL || $parent_id == NULL)
				throw new Exception('New photo needs data/file and a parent_id!');
			$this->id = -1;
			$this->parent_id = $parent_id;
			$this->caption = $caption;
			$this->photo_path = '';
			$this->extension = '';
			$this->mime = '';
			$this->photo_data = $data;
			$this->new = 1;
		}
	}

	function get() {
		global $globalConfig;
		if ($this->new == 1)
			return NULL;

		return $globalConfig['photo']['Path'].$this->id.'.'.$this->extension;
	}

	function updateCaption($caption) {
		if ($this->new == 1)
			return NULL;
		
		$db = db_connect();

		$preparedStatement = $db->prepare("UPDATE ".$this->table." SET photo_caption=:caption WHERE id=:id");

		$preparedStatement->execute(array(
			':caption' => $caption,
			':id' => $this->id
		));

		$n = $preparedStatement->rowCount();
		if ($n > 0)
			$this->caption = $caption;

		return $n;
	}

	function getThumbnail() {
		global $globalConfig;
		if ($this->new == 1)
			return NULL;

		return $globalConfig['photo']['ThumbPath'].$this->id.'.jpg';
	}

	function validate($photo = NULL) {
		if ($photo == NULL)
			$photo = $this->photo_data;

		if ($photo == NULL) {
			return 0;
		}

		$info = getimagesize($photo);

		if (empty($info)) {
			return 0;
		}

		$image = FALSE;
		switch($info['mime']) {
			case "image/gif":
				$image = imagecreatefromgif($photo);
				break;
			case "image/jpeg":
				$image = imagecreatefromjpeg($photo);
				break;
			case "image/png":
				$image = imagecreatefrompng($photo);
				break;
			case "image/xbm":
				$image = imagecreatefromxbm($photo);
				break;
			default:
				return 0;
		}

		if ($image == FALSE) {
			return 0;
		}

		$this->mime = $info['mime'];
		$this->extension = $this->mime_types[$this->mime];
		imagedestroy($image);
		return 1;
	}

	function store() {
		global $globalConfig;
		if ($this->photo_data == NULL)
			return 0;

		$val = $this->validate();
		if ($val == 0)
			return 0;

		if ($this->mime == '')
			return 0;

		if ($this->extension == '')
			return 0;

		$db = db_connect();

		$preparedStatement = $db->prepare("INSERT INTO ".$this->table." (parent_id, photo_caption, photo_mime) ".
			"VALUES (:parent_id, :caption, :mime);");
		$preparedStatement->execute(array(
			':parent_id' => $this->parent_id,
			':caption' => $this->caption,
			':mime' => $this->mime
			));

		$n = $preparedStatement->rowCount();
		if ($n <= 0) {
			throw new Exception('photo insert failed');
		}

		$this->id = $db->lastInsertId('id');
		$path = $globalConfig['photo']['Path'].$this->id.'.'.$this->extension;

		if(copy($this->photo_data, $path)) {
			/* Generate thumbnail */
			// Set a maximum height and width
			$width = $globalConfig['photo']['ThumbMaxWidth'];
			$height = $globalConfig['photo']['ThumbMaxHeight'];
			$info = getimagesize($path);
			$width_orig = $info[0];
			$height_orig = $info[1];
			$ratio_orig = $width_orig/$height_orig;
			if ($width/$height > $ratio_orig) {
				$width = $height*$ratio_orig;
			} else {
				$height = $width/$ratio_orig;
			}
			$image_p = imagecreatetruecolor($width, $height);
			switch($this->mime) {
				case "image/gif":
					$image = imagecreatefromgif($path);
					break;
				case "image/jpeg":
					$image = imagecreatefromjpeg($path);
					break;
				case "image/png":
					$image = imagecreatefrompng($path);
					break;
				case "image/xbm":
					$image = imagecreatefromxbm($path);
					break;
				default:
					throw new Exception('Unknown mime type in Photo->store(), this should have never happened!');
			}
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			imagejpeg($image_p, $globalConfig['photo']['ThumbPath'].$this->id.'.jpg');
			$this->new = 0;
		} else {
			throw new Exception('copying image failed');
		}

		return $n;
	}

	function delete() {
		if ($this->new == 1)
			return 0;

		$db = db_connect();

		$preparedStatement = $db->prepare("DELETE FROM ".$this->table." WHERE id=:id");
		$preparedStatement->execute(array(':id' => $this->id));
		$n = $preparedStatement->rowCount();

		unlink('photos/'.$this->id.'.'.$this->extension);
		unlink('thumbs/'.$this->id.'.jpg');
		$this->id = -1;
		$this->new = 1;
		return $n;
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

class Ingredient extends Model {
	var $name;
	var $id;
	var $unit;
	var $qty;
	var $typical_unit;
	var $typical_qty;
	var $kcal;
	var $carb;
	var $sugar;
	var $fibre;
	var $protein;
	var $fat;
	var $sat_fat;
	var $sodium;
	var $cholesterol;
	var $others;
	var $mtime;

	function Ingredient($id, $name = '', $new = 0, $unit = '', $qty = 0, $typical_unit = '', $typical_qty = 0, $kcal = 0, $carb = 0, $sugar = 0, $fibre = 0, $protein = 0, $fat = 0, $sat_fat = 0, $sodium = 0, $cholesterol = 0, $others = '') {
		$db = db_connect();
		$result = 0;
		$this->mtime = 0;

		if (!$new) {
			if ($name != '') {
				/* Get by name */
				$preparedStatement = $db->prepare('SELECT * FROM ingredients WHERE name LIKE :name');
				$preparedStatement->execute(array(':name' => $name));
				$result = $preparedStatement->fetch();  
			} else {
				/* Get by ID */
				$preparedStatement = $db->prepare("SELECT * FROM ingredients WHERE id=:ingredient_id");
				$preparedStatement->execute(array(':ingredient_id' => $id));
				$result = $preparedStatement->fetch();
			}

			if (!$result) {
				throw new Exception('Ingredient doesn\'t exist!');
			}
			
			$this->name = $result['name'];
			$this->id = $result['id'];
			$this->unit = $result['unit'];
			$this->qty = $result['qty'];
			$this->typical_unit = $result['typical_unit'];
			$this->typical_qty = $result['typical_qty'];
			$this->kcal = $result['kcal'];
			$this->carb = $result['carb'];
			$this->sugar = $result['sugar'];
			$this->fibre = $result['fibre'];
			$this->protein = $result['protein'];
			$this->fat = $result['fat'];
			$this->sat_fat = $result['sat_fat'];
			$this->sodium = $result['sodium'];
			$this->cholesterol = $result['cholesterol'];
			$this->others = $result['others'];
			$this->mtime = strtotime($result['mtime']);
		} else {
			$this->name = $name;
			$this->id = $id;
			$this->unit = $unit;
			$this->qty = $qty;
			$this->typical_unit = $typical_unit;
			$this->typical_qty = $typical_qty;
			$this->kcal = $kcal;
			$this->carb = $carb;
			$this->sugar = $sugar;
			$this->fibre = $fibre;
			$this->protein = $protein;
			$this->fat = $fat;
			$this->sat_fat = $sat_fat;
			$this->sodium = $sodium;
			$this->cholesterol = $cholesterol;
			$this->others = $others;
		}
	}

	function getNutriInfo($qty, $unit, $serves = 1, $dont_except = 0, $fractional_precision = 1) {
		$info = array();
		$info['kcal'] = 0;
		$info['carb'] = 0;
		$info['sugar'] = 0;
		$info['fat'] = 0;
		$info['sat_fat'] = 0;
		$info['protein'] = 0;
		$info['fibre'] = 0;
		$info['sodium'] = 0;
		$info['cholesterol'] = 0;

		
		if (($unit == '') && ($this->typical_qty != 0)) {
			$multiplier = $qty * $this->typical_qty/$this->qty;
			$unit = $this->typical_unit;
		} else {
			if ($this->qty == 0)
				return NULL;
			$multiplier = $qty/$this->qty;
		}
		$multiplier /= $serves;
		if ($unit == 'l') {
			$unit = 'ml';
			$multiplier *= 1000;
		}
		if ($unit == $this->unit) {
			$multiplier *= 1;
		} else if (($unit == 'g') && ($this->unit == 'ml')) {
			$multiplier *= 1;
		} else if (($unit == 'ml') && ($this->unit == 'g')) {
			$multiplier *= 1;
		} else if (($unit == 'g') && ($this->unit == 'mg')) {
			$multiplier *= 1000;
		} else if (($unit == 'mg') && ($this->unit == 'g')) {
			$multiplier /= 1000;
		} else if (($unit == 'kg') && ($this->unit == 'g')) {
			$multiplier *= 1000;
		} else if (($unit == 'g') && ($this->unit == 'kg')) {
			$multiplier /= 1000;
		} else if (($unit == 'l') && ($this->unit == 'ml')) {
			$multiplier *= 1000;
		} else if (($unit == 'ml') && ($this->unit == 'l')) {
			$multiplier /= 1000;
		} else {
			//return NULL;
			if ($dont_except)
				return $info;
			else
				throw new Exception("unknown unit mismatch, '".$unit."' vs '".$this->unit."'");
		}
		
		$info['kcal'] += round($this->kcal * $multiplier, $fractional_precision);
		$info['carb'] += round($this->carb * $multiplier, $fractional_precision);
		$info['sugar'] += round($this->sugar * $multiplier, $fractional_precision);
		$info['fat'] += round($this->fat * $multiplier, $fractional_precision);
		$info['sat_fat'] += round($this->sat_fat * $multiplier, $fractional_precision);
		$info['protein'] += round($this->protein * $multiplier, $fractional_precision);
		$info['fibre'] += round($this->fibre * $multiplier, $fractional_precision);
		$info['sodium'] += round($this->sodium * $multiplier, $fractional_precision);
		$info['cholesterol'] += round($this->cholesterol * $multiplier, $fractional_precision);


		return $info;
	}

	function delete() {
		$db = db_connect();
		$preparedStatement = $db->prepare("DELETE FROM ingredients WHERE id=:ingredient_id");
		$preparedStatement->execute(array(':ingredient_id' => $this->id));
		$n = $preparedStatement->rowCount();

		return $n;
	}

	function save($update = 0) {
		$db = db_connect();
		$preparedStatement = $db->prepare('SELECT * FROM ingredients WHERE name LIKE :name');
		$preparedStatement->execute(array(':name' => $this->name));
		$result = $preparedStatement->fetch();
		if ($result && $update == 0) {
			throw new Exception('Tried to add new ingredient, but ingredient \''.$this->name.'\' already exists');
			return -1;
		}

		if ($update) {
			$preparedStatement = $db->prepare("SELECT * FROM ingredients WHERE id=:ingredient_id");
			$preparedStatement->execute(array(':ingredient_id' => $this->id));

			if (!$preparedStatement->fetch()) {
				throw new Exception('Tried to update ingredient, but ingredient doesn\'t exist');
				return -1;
			}
			$preparedStatement = $db->prepare("UPDATE ingredients SET name=:ingredient_name, unit=:ingredient_unit, qty=:ingredient_qty, typical_unit=:ingredient_typical_unit, typical_qty=:ingredient_typical_qty, kcal=:ingredient_kcal, carb=:ingredient_carb, sugar=:ingredient_sugar, fibre=:ingredient_fibre, protein=:ingredient_protein, fat=:ingredient_fat, sat_fat=:ingredient_sat_fat, sodium=:ingredient_sodium, cholesterol=:ingredient_cholesterol,  others=:ingredient_others WHERE id=:ingredient_id");
			$preparedStatement->execute(array(
				':ingredient_name' => $this->name,
				':ingredient_unit' => $this->unit,
				':ingredient_qty' => $this->qty,
				':ingredient_typical_unit' => $this->typical_unit,
				':ingredient_typical_qty' => $this->typical_qty,
				':ingredient_kcal' => $this->kcal,
				':ingredient_carb' => $this->carb,
				':ingredient_sugar' => $this->sugar,
				':ingredient_fibre' => $this->fibre,
				':ingredient_protein' => $this->protein,
				':ingredient_fat' => $this->fat,
				':ingredient_sat_fat' => $this->sat_fat,
				':ingredient_sodium' => $this->sodium,
				':ingredient_cholesterol' => $this->cholesterol,
				':ingredient_others' => $this->others,
				':ingredient_id' => $this->id
				));
			$n = $preparedStatement->rowCount();
			return $n;
		} else {
			$preparedStatement = $db->prepare("INSERT INTO ingredients (name, unit, qty, typical_unit, typical_qty, kcal, carb, sugar, fibre, protein, fat, sat_fat, sodium, cholesterol, others) ".
				"VALUES (:ingredient_name, :ingredient_unit, :ingredient_qty, :ingredient_typical_unit, :ingredient_typical_qty, :ingredient_kcal, :ingredient_carb, :ingredient_sugar, :ingredient_fibre, :ingredient_protein, :ingredient_fat, :ingredient_sat_fat, :ingredient_sodium,  :ingredient_cholesterol,  :ingredient_others);");
			$preparedStatement->execute(array(
				':ingredient_name' => $this->name,
				':ingredient_unit' => $this->unit,
				':ingredient_qty' => $this->qty,
				':ingredient_typical_unit' => $this->typical_unit,
				':ingredient_typical_qty' => $this->typical_qty,
				':ingredient_kcal' => $this->kcal,
				':ingredient_carb' => $this->carb,
				':ingredient_sugar' => $this->sugar,
				':ingredient_fibre' => $this->fibre,
				':ingredient_protein' => $this->protein,
				':ingredient_fat' => $this->fat,
				':ingredient_sat_fat' => $this->sat_fat,
				':ingredient_sodium' => $this->sodium,
				':ingredient_cholesterol' => $this->cholesterol,
				':ingredient_others' => $this->others
				));
			$n = $preparedStatement->rowCount();
			$preparedStatement = $db->prepare('SELECT id, mtime FROM ingredients WHERE name LIKE :name');
			$preparedStatement->execute(array(':name' => $this->name));
			if ($row = $preparedStatement->fetch()) {
				$this->id = $row['id'];
				$this->mtime = strtotime($row['mtime']);
			} else {
				throw new Exception('Error adding new ingredient');
			}
			return $n;
		}
	}
}

function get_all_ingredients($restrict_query = '', $tokens = NULL) {
	$ingredients = array();
	
	$db = db_connect();
	if ($restrict_query == '') {
		$result = $db->query('SELECT * FROM ingredients');
	} else {
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$preparedStatement = $db->prepare('SELECT * FROM ingredients '.$restrict_query);
		//echo $preparedStatement->queryString;
		$preparedStatement->execute($tokens);
		$result = $preparedStatement->fetchAll();
	}
	$i = 0;
	foreach ($result as $row) {
		$ingredients[$i++] = new Ingredient($row['id'], $row['name'], 1, $row['unit'], $row['qty'], $row['typical_unit'], $row['typical_qty'], $row['kcal'], $row['carb'], $row['sugar'], $row['fibre'], $row['protein'], $row['fat'], $row['sat_fat'], $row['sodium'], $row['cholesterol'], $row['others']);
		//$ingredients[$i++] = new Ingredient($row['id']);
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


class Recipe {
	var $id;
	var $name;
	var $description;
	var $instructions;
	var $time_estimate;
	var $mtime;
	var $serves;

	/*
	 * array of maps such as:
	 *      Ingredient      (Ingredient)
	 *      qty             (number)
	 *      unit            (string)
	 *      method          (string)
	 * http://nutritiondata.self.com/facts/spices-and-herbs/225/2?mbid=ndhp
	 */
	var $ingredients;




	public function toArray() {
		$nutri_info = $this->getNutriInfo();
		$arr = get_object_vars($this);
		$arr['nutri_info'] = $nutri_info;
	    
		$parseMe = array();
		foreach ($nutri_info as $name => $val) {
		    $cur = array();
		    $cur['name'] = $name;
		    $cur['value'] = $val;
		    array_push($parseMe, $cur);
		}
		$arr['nutri_info_keyval'] = $parseMe;

		return $this->processArray($arr);
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
















	function Recipe($id, $name = '', $new = 0, $description = '', $instructions = '', $time_estimate = 0, $serves = 1, $ingredients = NULL) {
		$db = db_connect();
		$result = 0;
		$this->mtime = 0;

		if (!$new) {
			if ($name != '') {
				/* Get by name */
				$preparedStatement = $db->prepare('SELECT * FROM recipes WHERE name LIKE :name');
				$preparedStatement->execute(array(':name' => $name));
				$result = $preparedStatement->fetch();  
			} else {
				/* Get by ID */
				$preparedStatement = $db->prepare("SELECT * FROM recipes WHERE id=:recipe_id");
				$preparedStatement->execute(array(':recipe_id' => $id));
				$result = $preparedStatement->fetch();
			}

			if (!$result) {
				throw new Exception('Recipe doesn\'t exist!');
			}

			$this->id = $result['id'];      
			$this->name = $result['name'];
			$this->description = $result['description'];
			$this->instructions = $result['instructions'];
			$this->time_estimate = $result['time_estimate'];
			$this->serves = $result['serves'];
			$this->mtime = strtotime($result['mtime']);

			$ings = array();
			$i = 0;

			$preparedStatement = $db->prepare("SELECT * FROM rec_ing WHERE recipe_id=:recipe_id");
			$preparedStatement->execute(array(':recipe_id' => $this->id));
			$result = $preparedStatement->fetchAll();
			foreach ($result as $row) {
				$elem = array();

				$elem['qty'] = $row['ingredient_qty'];
				$elem['unit'] = $row['ingredient_unit'];
				$elem['method'] = $row['method'];
				$elem['Ingredient'] = new Ingredient($row['ingredient_id']);
				if (!$elem['Ingredient']) {
					throw new Exception('Problem with ingredient id='.$row['ingredient_id']);
				}

				$ings[$i++] = $elem;
			}
			$this->ingredients = $ings;

			$this->photos = get_photos("recipe", $this->id);
		} else {
			$this->id = $id;
			if ($name == '')
				throw new Exception('No recipe name specified!');
			$this->setName($name);
			$this->description = $description;
			$this->instructions = $instructions;
			$this->time_estimate = $time_estimate;
			$this->setServes($serves);
			if ($ingredients == NULL)
				$this->ingredients = array();
			else
				$this->ingredients = $ingredients;
		}
	}

	function addIngredient($qty, $unit, $method, $id, $name = '', $validate = 1) {
		$elem = array();
		$elem['qty'] = $qty;
		$elem['unit'] = $unit;
		$elem['method'] = $method;
		$elem['Ingredient'] = new Ingredient($id, $name);

		if ($validate) {
			/* Validate units and qty */
			if ((!is_numeric($qty)) || ($qty <= 0)) {
				throw new Exception('"qty" needs to be a positive number');
			}
			$elem['Ingredient']->getNutriInfo($elem['qty'], $elem['unit']);
		}

		array_push($this->ingredients, $elem);
		return $elem;
	}

	function clearIngredients() {
		$this->ingredients = array();
	}

	function delete() {
		$db = db_connect();

		$preparedStatement = $db->prepare("DELETE FROM rec_ing WHERE recipe_id=:recipe_id");
		$preparedStatement->execute(array(':recipe_id' => $this->id));
		$n = $preparedStatement->rowCount();

		$n += delete_photos("recipe", $this->id);

		$preparedStatement = $db->prepare("DELETE FROM recipes WHERE id=:recipe_id");
		$preparedStatement->execute(array(':recipe_id' => $this->id));
		$n += $preparedStatement->rowCount();

		return $n;
	}

	function setName($name) {
		if (empty($name))
			throw new Exception('Empty recipe names are not valid');
		else
			$this->name = $name;
	}

	function setServes($serves) {
		if ((!is_numeric($serves)) || ($serves <= 0) || (round($serves, 0) != $serves)) {
			throw new Exception('"Serves" needs to be a positive integer');
		}
		else
			$this->serves = $serves;
	}

	function getTimeEstimate() {
		$str = '';
		$hours = intval($this->time_estimate / 60);
		$minutes = intval($this->time_estimate % 60);
		if (($hours != 0) && ($minutes != 0))
			return sprintf("%dh, %dmin", $hours, $minutes);
		else if (($hours != 0) && ($minutes == 0))
			return sprintf("%dh", $hours);
		else if (($hours == 0) && ($minutes != 0))
			return sprintf("%dmin", $minutes);
		else
			return "No Estimate";
	}

	function getNutriInfo($dont_except = 0) {
		$info = array();
		$info['kcal'] = 0;
		$info['carb'] = 0;
		$info['sugar'] = 0;
		$info['fat'] = 0;
		$info['sat_fat'] = 0;
		$info['protein'] = 0;
		$info['fibre'] = 0;
		$info['sodium'] = 0;
		$info['cholesterol'] = 0;

		foreach ($this->ingredients as $elem) {
			$ingredient_info = $elem['Ingredient']->getNutriInfo($elem['qty'], $elem['unit'], $this->serves, $dont_except);

			$info['kcal'] += $ingredient_info['kcal'];
			$info['carb'] += $ingredient_info['carb'];
			$info['sugar'] += $ingredient_info['sugar'];
			$info['fat'] += $ingredient_info['fat'];
			$info['sat_fat'] += $ingredient_info['sat_fat'];
			$info['protein'] += $ingredient_info['protein'];
			$info['fibre'] += $ingredient_info['fibre'];
			$info['sodium'] += $ingredient_info['sodium'];
			$info['cholesterol'] += $ingredient_info['cholesterol'];
		}

		return $info;
	}

	function getMTime() {
		$ftime = '';
		//H:i
		if ($this->mtime != 0)
			$ftime = date('d M Y', $this->mtime);
		else
			$ftime = 'N/A';

		return $ftime;
	}

	function save($update = 0) {
		$db = db_connect();
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$preparedStatement = $db->prepare('SELECT * FROM recipes WHERE name LIKE :name');
		$preparedStatement->execute(array(':name' => $this->name));
		$result = $preparedStatement->fetch();
		if ($result && (($update == 0) || (($update == 1) && ($result['id'] != $this->id)))) {
			throw new Exception('Tried to add new recipe, but recipe \''.$this->name.'\' already exists');
			return -1;
		}

		if ($update) {
			$preparedStatement = $db->prepare("SELECT * FROM recipes WHERE id=:recipe_id");
			$preparedStatement->execute(array(':recipe_id' => $this->id));
			if (!$preparedStatement->fetch()) {
				throw new Exception('Tried to update recipe, but recipe doesn\'t exist');
				return -1;
			}
			
			$preparedStatement = $db->prepare("UPDATE recipes SET name=:recipe_name, description=:recipe_description, instructions=:recipe_instructions, time_estimate=:time_estimate, serves=:serves WHERE id=:recipe_id");
			$preparedStatement->execute(array(
				':recipe_name' => $this->name,
				':recipe_description' => $this->description,
				':recipe_instructions' => $this->instructions,
				':time_estimate' => $this->time_estimate,
				':serves' => $this->serves,
				':recipe_id' => $this->id
				));

			$n = $preparedStatement->rowCount();

			$preparedStatement = $db->prepare("DELETE FROM rec_ing WHERE recipe_id=:recipe_id");
			$preparedStatement->execute(array(':recipe_id' => $this->id));
			$n += $preparedStatement->rowCount();

			foreach ($this->ingredients as $ing) {
				$preparedStatement = $db->prepare("INSERT INTO rec_ing (recipe_id, ingredient_id, ingredient_qty, ingredient_unit, method) ".
					"VALUES (:recipe_id, :ingredient_id, :ingredient_qty, :ingredient_unit, :method);");
				$preparedStatement->execute(array(
					':recipe_id' => $this->id,
					':ingredient_id' => $ing['Ingredient']->id,
					':ingredient_qty' => $ing['qty'],
					':ingredient_unit' => $ing['unit'],
					':method' => $ing['method']
					));
				$n += $preparedStatement->rowCount();
			}
			return $n;
		} else {
			/* Add new recipe */
			$preparedStatement = $db->prepare("INSERT INTO recipes (name, description, instructions, time_estimate, serves) ".
				"VALUES (:recipe_name, :recipe_description, :recipe_instructions, :time_estimate, :serves);");
			$preparedStatement->execute(array(
				':recipe_name' => $this->name,
				':recipe_description' => $this->description,
				':recipe_instructions' => $this->instructions,
				':time_estimate' => $this->time_estimate,
				':serves' => $this->serves
				));
			
			$n = $preparedStatement->rowCount();

			$preparedStatement = $db->prepare('SELECT id, mtime FROM recipes WHERE name LIKE :name');
			$preparedStatement->execute(array(':name' => $this->name));
			if ($row = $preparedStatement->fetch()) {
				$this->id = $row['id'];
				$this->mtime = strtotime($row['mtime']);
			} else {
				throw new Exception('Error adding new recipe');
			}

			foreach ($this->ingredients as $ing) {
				$preparedStatement = $db->prepare("INSERT INTO rec_ing (recipe_id, ingredient_id, ingredient_qty, ingredient_unit, method) ".
					"VALUES (:recipe_id, :ingredient_id, :ingredient_qty, :ingredient_unit, :method);");
				$preparedStatement->execute(array(
					':recipe_id' => $this->id,
					':ingredient_id' => $ing['Ingredient']->id,
					':ingredient_qty' => $ing['qty'],
					':ingredient_unit' => $ing['unit'],
					':method' => $ing['method']
					));
				$n += $preparedStatement->rowCount();
			}
			return $n;
		}
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
		<a class="boring" href="http://validator.w3.org/check?uri=referer"><img class="boring" src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Transitional" height="31" width="88"/></a>
	    </div>
	    <div class="grid_14">
		<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/88x31.png" /></a><br/>&copy; <span xmlns:cc="http://creativecommons.org/ns#">Alex Hornung</span> <!-- span: property="cc:attributionName" -->
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




?>