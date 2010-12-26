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

	function Ingredient($id, $name, $unit, $qty, $kcal, $carb, $sugar, $fibre, $protein, $fat, $sat_fat, $sodium, $cholesterol, $others) {
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

	function Ingredient($id, $name = '') {
		$db = new PDO("mysql:host=localhost;dbname=recipemaster", "root", "");
		var $result;

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
	var $ingredients = array();
	
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

	function Recipe($id, $name, $description, $instructions, $time_estimate, $ingredients = NULL) {
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

	function Recipe($id, $name = '') {
		$db = new PDO("mysql:host=localhost;dbname=recipemaster", "root", "");
		var $result;

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
			throw new Exception('Ingredient doesn\'t exist!');
		}

		$this->id = $result['id'];	
		$this->name = $result['name'];
		$this->description = $result['description'];
		$this->instructions = $result['instructions'];
		$this->time_estimate = $result['time_estimate'];

		var $ings = array();
		var $i = 0;

		$preparedStatement = $db->prepare("SELECT * FROM rec_ing WHERE recipe_id=:recipe_id");
		$preparedStatement->execute(array(':recipe_id' => $this->id));
		$result = $preparedStatement->fetchAll();
		foreach ($result as $row) {
			var $elem = array();

			$elem['qty'] = $row['ingredient_qty'];
			$elem['unit'] = $row['ingredient_unit'];
			$elem['method'] = $row['method'];
			$elem['Ingredient'] = new Ingredient($row['ingredient_id']);
			if (!$elem['Ingredient']) {
				throw new Exception('Problem with ingredient id='.$row['ingredient_id']);
			}

			ings[$i++] = $elem;
		}
	}

	function addIngredient($qty, $unit, $method, $id, $name = '') {
		var $elem = array();
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

	function getNutriInfo() {
		var $nutritional_info = array();

		foreach ($this->ingredients as $elem) {
			var $info = array();
			$multiplier = $elem['qty']/$elem['Ingredient']->qty;

			if ($elem['unit'] == $elem['Ingredient']->unit) {
				$multiplier *= 1;
			} else if (($elem['unit'] == 'g') && ($elem['Ingredient']->unit == 'ml')) {
				$multiplier *= 1;
			} else if (($elem['unit'] == 'ml') && ($elem['Ingredient']->unit == 'g')) {
				$multiplier *= 1;
			} else if (($elem['unit'] == 'g') && ($elem['Ingredient']->unit == 'mg')) {
				$multiplier *= 1000;
			} else if (($elem['unit'] == 'mg') && ($elem['Ingredient']->unit == 'g')) {
				$multiplier /= 1000;
			} else if (($elem['unit'] == 'kg') && ($elem['Ingredient']->unit == 'g')) {
				$multiplier *= 1000;
			} else if (($elem['unit'] == 'g') && ($elem['Ingredient']->unit == 'kg')) {
				$multiplier /= 1000;
			} else if (($elem['unit'] == 'l') && ($elem['Ingredient']->unit == 'ml')) {
				$multiplier *= 1000;
			} else if (($elem['unit'] == 'ml') && ($elem['Ingredient']->unit == 'l')) {
				$multiplier /= 1000;
			} else {
				throw new Exception "unknown unit mismatch, '".$elem['unit']."' vs '".$elem['Ingredient']->unit."'";
			}

			$info['kcal'] += $elem['Ingredient']->kcal * $multiplier;
			$info['carb'] += $elem['Ingredient']->carb * $multiplier;
			$info['sugar'] += $elem['Ingredient']->sugar * $multiplier;
			$info['fat'] += $elem['Ingredient']->fat * $multiplier;
			$info['sat_fat'] += $elem['Ingredient']->sat_fat * $multiplier;
			$info['protein'] += $elem['Ingredient']->protein * $multiplier;
			$info['fibre'] += $elem['Ingredient']->fibre * $multiplier;
			$info['sodium'] += $elem['Ingredient']->sodium * $multiplier;
			$info['cholesterol'] += $elem['Ingredient']->cholesterol * $multiplier;
			array_push($nutritional_info, $info);
		}

		return $nutritional_info;
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
				$ing['qty'], $ing['unit'], $ing['method'], $ing['Ingredient'];
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
				$ing['qty'], $ing['unit'], $ing['method'], $ing['Ingredient'];
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
	var $recipes = array();
	
	$db = new PDO("mysql:host=localhost;dbname=recipemaster", "root", "");
	$result = $db->query('SELECT id FROM recipes');
	$i = 0;
	foreach ($result as $row) {
		$recipes[$i++] = new Recipe($row['id']);
	}

	return $recipes;
}

?>