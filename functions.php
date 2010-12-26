<?php
class Ingredient {
	var $name;
	var $id;
	var $unit;
	var $qty;
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

	function Ingredient($id, $name = '', $new = 0, $unit = '', $qty = 0, $kcal = 0, $carb = 0, $sugar = 0, $fibre = 0, $protein = 0, $fat = 0, $sat_fat = 0, $sodium = 0, $cholesterol = 0, $others = '') {
		$db = new PDO("mysql:host=localhost;dbname=recipemaster", "root", "");
		$result = 0;

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
		} else {
			$this->name = $name;
			$this->id = $id;
			$this->unit = $unit;
			$this->qty = $qty;
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

	function getNutriInfo($qty, $unit) {
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

		
		$multiplier = $qty/$this->qty;
                
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
			throw new Exception("unknown unit mismatch, '".$unit."' vs '".$this->unit."'");
		}
                
		$info['kcal'] += $this->kcal * $multiplier;
		$info['carb'] += $this->carb * $multiplier;
		$info['sugar'] += $this->sugar * $multiplier;
		$info['fat'] += $this->fat * $multiplier;
		$info['sat_fat'] += $this->sat_fat * $multiplier;
		$info['protein'] += $this->protein * $multiplier;
		$info['fibre'] += $this->fibre * $multiplier;
		$info['sodium'] += $this->sodium * $multiplier;
		$info['cholesterol'] += $this->cholesterol * $multiplier;


		return $info;
	}

	function delete() {
		$db = new PDO("mysql:host=localhost;dbname=recipemaster", "root", "");
		$preparedStatement = $db->prepare("DELETE FROM ingredients WHERE id=:ingredient_id");
		$preparedStatement->execute(array(':ingredient_id' => $this->id));
		$n = $preparedStatement->rowCount();

		return $n;
	}

	function save($update = 0) {
		$db = new PDO("mysql:host=localhost;dbname=recipemaster", "root", "");
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
			echo 'ID: '.$this->id;
			if (!$preparedStatement->fetch()) {
				throw new Exception('Tried to update ingredient, but ingredient doesn\'t exist');
				return -1;
			}
			$preparedStatement = $db->prepare("UPDATE ingredients SET name=:ingredient_name, unit=:ingredient_unit, qty=:ingredient_qty, kcal=:ingredient_kcal, carb=:ingredient_carb, sugar=:ingredient_sugar, fibre=:ingredient_fibre, protein=:ingredient_protein, fat=:ingredient_fat, sat_fat=:ingredient_sat_fat, sodium=:ingredient_sodium, cholesterol=:ingredient_cholesterol,  others=:ingredient_others WHERE id=:ingredient_id");
			$preparedStatement->execute(array(
				':ingredient_name' => $this->name,
				':ingredient_unit' => $this->unit,
				':ingredient_qty' => $this->qty,
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
			$preparedStatement = $db->prepare("INSERT INTO ingredients (name, unit, qty, kcal, carb, sugar, fibre, protein, fat, sat_fat, sodium, cholesterol, others) ".
				"VALUES (:ingredient_name, :ingredient_unit, :ingredient_qty, :ingredient_kcal, :ingredient_carb, :ingredient_sugar, :ingredient_fibre, :ingredient_protein, :ingredient_fat, :ingredient_sat_fat, :ingredient_sodium,  :ingredient_cholesterol,  :ingredient_others);");
			$preparedStatement->execute(array(
				':ingredient_name' => $this->name,
				':ingredient_unit' => $this->unit,
				':ingredient_qty' => $this->qty,
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
			$preparedStatement = $db->prepare('SELECT id FROM ingredients WHERE name LIKE :name');
			$preparedStatement->execute(array(':name' => $this->name));
			if ($row = $preparedStatement->fetch()) {
				$this->id = $row['id'];
			} else {
				throw new Exception('Error adding new ingredient');
			}
			return $n;
		}
	}
}

function get_all_ingredients() {
	$ingredients = array();
	
	$db = new PDO("mysql:host=localhost;dbname=recipemaster", "root", "");
	$result = $db->query('SELECT id FROM ingredients');
	$i = 0;
	foreach ($result as $row) {
		$ingredients[$i++] = new Ingredient($row['id']);
	}

	return $ingredients;
}

class Recipe {
	var $id;
	var $name;
	var $description;
	var $instructions;
	var $time_estimate;

	/*
	 * array of maps such as:
	 *	Ingredient	(Ingredient)
	 *	qty		(number)
	 *	unit		(string)
	 *	method		(string)
	 * http://nutritiondata.self.com/facts/spices-and-herbs/225/2?mbid=ndhp
	 */
	var $ingredients;

	function Recipe($id, $name = '', $new = 0, $description = '', $instructions = '', $time_estimate = 0, $ingredients = NULL) {
		$db = new PDO("mysql:host=localhost;dbname=recipemaster", "root", "");
		$result = 0;

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
		} else {
			$this->id = $id;
			$this->name = $name;
			$this->description = $description;
			$this->instructions = $instructions;
			$this->time_estimate = $time_estimate;
			if ($ingredients == NULL)
				$this->ingredients = array();
			else
				$this->ingredients = $ingredients;
		}
	}

	function addIngredient($qty, $unit, $method, $id, $name = '') {
		$elem = array();
		$elem['qty'] = $qty;
		$elem['unit'] = $unit;
		$elem['method'] = $method;
		$elem['Ingredient'] = new Ingredient($id, $name);
		array_push($this->ingredients, $elem);
	}

	function delete() {
		$db = new PDO("mysql:host=localhost;dbname=recipemaster", "root", "");
		$preparedStatement = $db->prepare("DELETE FROM recipes WHERE id=:recipe_id");
		$preparedStatement->execute(array(':recipe_id' => $this->id));
		$n = $preparedStatement->rowCount();

		$preparedStatement = $db->prepare("DELETE FROM rec_ing WHERE recipe_id=:recipe_id");
		$preparedStatement->execute(array(':recipe_id' => $this->id));
		$n += $preparedStatement->rowCount();
		return $n;
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

	function getNutriInfo() {
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
			$ingredient_info = $elem['Ingredient']->getNutriInfo($elem['qty'], $elem['unit']);

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

	function save($update = 0) {
		$db = new PDO("mysql:host=localhost;dbname=recipemaster", "root", "");
		$preparedStatement = $db->prepare('SELECT * FROM recipes WHERE name LIKE :name');
		$preparedStatement->execute(array(':name' => $this->name));
		$result = $preparedStatement->fetch();
		if ($result && $update == 0) {
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
			
			$preparedStatement = $db->prepare("UPDATE recipes SET name=:recipe_name, description=:recipe_description, instructions=:recipe_instructions, time_estimate=:time_estimate WHERE id=:recipe_id");
			/* Add new recipe */
			$preparedStatement = $db->prepare("INSERT INTO recipes (name, description, instructions, time_estimate) ".
				"VALUES (:recipe_name, :recipe_description, :recipe_instructions, :time_estimate);");
			$preparedStatement->execute(array(
				':recipe_name' => $this->name,
				':recipe_description' => $this->description,
				':recipe_instructions' => $this->instructions,
				':time_estimate' => $this->time_estimate,
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
			$preparedStatement = $db->prepare("INSERT INTO recipes (name, description, instructions, time_estimate) ".
				"VALUES (:recipe_name, :recipe_description, :recipe_instructions, :time_estimate);");
			$preparedStatement->execute(array(
				':recipe_name' => $this->name,
				':recipe_description' => $this->description,
				':recipe_instructions' => $this->instructions,
				':time_estimate' => $this->time_estimate
				));
			
			$n = $preparedStatement->rowCount();

			$preparedStatement = $db->prepare('SELECT id FROM recipes WHERE name LIKE :name');
			$preparedStatement->execute(array(':name' => $this->name));
			if ($row = $preparedStatement->fetch()) {
				$this->id = $row['id'];
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

function get_all_recipes() {
	$recipes = array();
	
	$db = new PDO("mysql:host=localhost;dbname=recipemaster", "root", "");
	$result = $db->query('SELECT id FROM recipes');
	$i = 0;
	foreach ($result as $row) {
		$recipes[$i++] = new Recipe($row['id']);
	}

	return $recipes;
}

?>