<?php

class Recipe {
	var $id;
	var $name;
	var $description;
	var $instructions;
	var $time_estimate;
	var $mtime;
	var $serves;
	var $photos;

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
			foreach ($ingredient_info as $key => $val) {
				$info[$key] += $val;
			}
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

?>
