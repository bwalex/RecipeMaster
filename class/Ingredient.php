<?php

class Ingredient {
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
	var $photos;
	var $info;
	var $nutrients;




	public function toArray() {
		$nutri_info = $this->getOwnNutriInfo();
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


	function Ingredient($id, $name = '', $new = 0, $unit = '', $qty = 0, $typical_unit = 'g', $typical_qty = 0, $kcal = 0, $carb = 0, $sugar = 0, $fibre = 0, $protein = 0, $fat = 0, $sat_fat = 0, $sodium = 0, $cholesterol = 0, $others = '', $info = '') {
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
			$this->info = $result['info'];
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



			$nuts = array();
			$i = 0;

			$preparedStatement = $db->prepare("SELECT * FROM ing_nutrients WHERE ingredient_id=:ingredient_id");
			$preparedStatement->execute(array(':ingredient_id' => $this->id));
			$result = $preparedStatement->fetchAll();
			foreach ($result as $row) {
				$elem = array();

				$elem['qty'] = $row['qty'];
				$elem['unit'] = $row['unit'];
				$elem['Nutrient'] = new Nutrient($row['nutrient_id']);
				if (!$elem['Nutrient']) {
					throw new Exception('Problem with nutrient id='.$row['nutrient_id']);
				}

				$nuts[$i++] = $elem;
			}
			$this->nutrients = $nuts;



			$this->photos = get_photos("ingredient", $this->id);
		} else {
			$this->setName($name);
			$this->id = $id;
			$this->info = $info;
			$this->qty = $qty;
			$this->unit = $unit;
			$this->typical_qty = $typical_qty;
			$this->typical_unit = $typical_unit;
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
			$this->nutrients = array();
		}
	}

	function addNutrient($qty, $unit, $id, $name = '', $validate = 1) {
		$elem = array();

		$elem['Nutrient'] = new Nutrient($id, $name);
		if ($unit == '')
			$unit = $elem['Nutrient']->unit;
		$elem['qty'] = $qty;
		$elem['unit'] = $unit;

		if ($validate) {
			/* Validate units and qty */
			if ((!is_numeric($qty)) || ($qty <= 0)) {
				throw new Exception('"qty" needs to be a positive number');
			}
			if ($unit != $elem['Nutrient']->unit) {
				throw new Exception('"unit" needs to match the unit of the nutrient');
			}
			//$elem['Nutrient']->...validate..
		}

		array_push($this->nutrients, $elem);
		return $elem;
	}

	function clearNutrients() {
		$this->nutrients = array();
	}

	function setName($name) {
		if (empty($name))
			throw new Exception('Empty ingredient names are not valid');
		else
			$this->name = $name;
	}

	function setQtyUnit($qty, $unit, $typical_qty, $typical_unit) {
		if (!is_numeric($qty))
			throw new Exception('Quantity must be a number!');
		if ($qty <= 0)
			throw new Exception('Quantity must be positive!');
		if (!is_numeric($typical_qty))
			throw new Exception('Typical weight must be a number!');
		if ($typical_qty < 0)
			throw new Exception('Typical weight cannot be negative!');

		if (($unit != '') && ($unit != 'mg') && ($unit != 'g') && ($unit != 'kg') && ($unit != 'ml') && ($unit != 'l'))
			throw new Exception('Unit must be one of the provided choices!');
		if (($typical_unit != 'mg') && ($typical_unit != 'g') && ($typical_unit != 'kg'))
			throw new Exception('Typical Unit Weight must be one of the provided choices (mg, g, kg)!');
		if (($unit == '') && ($typical_qty == 0)) {
			throw new Exception('If no unit is specified, a typical weight must be specified!');
		}
		$this->qty = $qty;
		$this->unit = $unit;
		$this->typical_qty = $typical_qty;
		$this->typical_unit = $typical_unit;
	}

	function setNutri($key, $val) {
		if (!isset($key))
			throw new Exception('Key must be set!');
		if (!isset($val))
			throw new Exception('Value for '.$key.' must be set!');
		if (!is_numeric($val))
			throw new Exception('Value for '.$key.' must be a number!');
		if ($val < 0)
			throw new Exception('Value for '.$key.' cannot be negative!');

		$this->$key = $val;
	}

        function getMultiplier($unit, $own_unit, $qty, $own_qty, $dont_except = 0) {
                if ($own_qty == 0)
                    throw new Exception('oops eeps, own_qty=0');
                $multiplier = $qty/$own_qty;
		if ($unit == 'l') {
			$unit = 'ml';
			$multiplier *= 1000;
		}

                if ($ok) {
                        /* Do nothing */
                } else if ($unit == $own_unit) {
			$multiplier *= 1;
		} else if (($unit == 'g') && ($own_unit == 'ml')) {
			$multiplier *= 1;
		} else if (($unit == 'ml') && ($own_unit == 'g')) {
			$multiplier *= 1;
		} else if (($unit == 'g') && ($own_unit == 'mg')) {
			$multiplier *= 1000;
		} else if (($unit == 'mg') && ($own_unit == 'g')) {
			$multiplier /= 1000;
		} else if (($unit == 'kg') && ($own_unit == 'g')) {
			$multiplier *= 1000;
		} else if (($unit == 'g') && ($own_unit == 'kg')) {
			$multiplier /= 1000;
		} else if (($unit == 'l') && ($own_unit == 'ml')) {
			$multiplier *= 1000;
		} else if (($unit == 'ml') && ($own_unit == 'l')) {
			$multiplier /= 1000;
		} else {
			//return NULL;
			if ($dont_except)
				return 0;
			else
				throw new Exception("unknown unit mismatch, '".$unit."' vs '".$this->unit."'");
		}
                
                return $multiplier;
        }

        function getOwnNutriInfo() {
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
                
                $multiplier = 1;
		$info['kcal'] += round($this->kcal * $multiplier, $fractional_precision);
		$info['carb'] += round($this->carb * $multiplier, $fractional_precision);
		$info['sugar'] += round($this->sugar * $multiplier, $fractional_precision);
		$info['fat'] += round($this->fat * $multiplier, $fractional_precision);
		$info['sat_fat'] += round($this->sat_fat * $multiplier, $fractional_precision);
		$info['protein'] += round($this->protein * $multiplier, $fractional_precision);
		$info['fibre'] += round($this->fibre * $multiplier, $fractional_precision);
		$info['sodium'] += round($this->sodium * $multiplier, $fractional_precision);
		$info['cholesterol'] += round($this->cholesterol * $multiplier, $fractional_precision);

		foreach($this->nutrients as $nutrient) {
			$info[$nutrient['Nutrient']->name] = round($nutrient['qty'] * $multiplier, $fractional_precision);
		}
                return $info;
        }

        function getNutriInfo($qty, $unit, $serves = 1, $dont_except = 0, $fractional_precision = 1, $panic = 1) {
                $ok = 0;
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
                $multiplier = 1;
                $adjustment = 1;

                if ($this->qty == 0) {
                    //throw new Exception('This ingredient has a 0 quantity and cannot be used');
                    return $info;
                }

                if ($this->unit == '') {
                    if ($this->typical_qty == 0) {
                        throw new Exception('The ingredient\'s unit cannot be zero as there is no typical weight specified');
                    }
                    $multiplier = $qty/$this->qty;
                    $adjustment = 0;
                    
                } else if ($unit == '' && $this->unit != '') {
                    $qty = $qty * $this->typical_qty;
                    $unit = $this->typical_unit;
                    //echo $this->name ." : $qty : $unit";
                    $adjustment = 1;
                    $multiplier = $this->getMultiplier($unit, $this->unit, $qty, $this->qty, $dont_except);
                    //echo "Multi < $multiplier >";
                    
                } else if ($unit != '' && $this->unit != '') {
                    $adjustment = 1;
                    $multiplier = $this->getMultiplier($unit, $this->unit, $qty, $this->qty, $dont_except);
                    
                } else if ($unit != '' && $this->unit == '') {
                    $qty_bar = $this->qty * $this->typical_qty;
                    $unit_bar = $this->typical_unit;
                    $adjustment = 1;
                    $multiplier = $this->getMultiplier($unit, $unit_bar, $qty, $qty_bar, $dont_except);
                    
                }

                $multiplier /= $serves;

		$info['kcal'] += round($this->kcal * $multiplier, $fractional_precision);
		$info['carb'] += round($this->carb * $multiplier, $fractional_precision);
		$info['sugar'] += round($this->sugar * $multiplier, $fractional_precision);
		$info['fat'] += round($this->fat * $multiplier, $fractional_precision);
		$info['sat_fat'] += round($this->sat_fat * $multiplier, $fractional_precision);
		$info['protein'] += round($this->protein * $multiplier, $fractional_precision);
		$info['fibre'] += round($this->fibre * $multiplier, $fractional_precision);
		$info['sodium'] += round($this->sodium * $multiplier, $fractional_precision);
		$info['cholesterol'] += round($this->cholesterol * $multiplier, $fractional_precision);

		foreach($this->nutrients as $nutrient) {
			$info[$nutrient['Nutrient']->name] = round($nutrient['qty'] * $multiplier, $fractional_precision);
		}

		return $info;
        }


	function delete() {
		$db = db_connect();

		$preparedStatement = $db->prepare("DELETE FROM ing_nutrients WHERE ingredient_id=:ingredient_id");
		$preparedStatement->execute(array(':ingredient_id' => $this->id));
		$n = $preparedStatement->rowCount();

		$n += delete_photos("ingredient", $this->id);

		$preparedStatement = $db->prepare("DELETE FROM ingredients WHERE id=:ingredient_id");
		$preparedStatement->execute(array(':ingredient_id' => $this->id));
		$n += $preparedStatement->rowCount();

		return $n;
	}

	function save($update = 0) {
		$db = db_connect();
		$preparedStatement = $db->prepare('SELECT * FROM ingredients WHERE name LIKE :name');
		$preparedStatement->execute(array(':name' => $this->name));
		$result = $preparedStatement->fetch();
		if ($result && (($update == 0) || (($update == 1) && ($result['id'] != $this->id)))) {
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
			$preparedStatement = $db->prepare("UPDATE ingredients SET name=:ingredient_name, info=:ingredient_info, unit=:ingredient_unit, qty=:ingredient_qty, typical_unit=:ingredient_typical_unit, typical_qty=:ingredient_typical_qty, kcal=:ingredient_kcal, carb=:ingredient_carb, sugar=:ingredient_sugar, fibre=:ingredient_fibre, protein=:ingredient_protein, fat=:ingredient_fat, sat_fat=:ingredient_sat_fat, sodium=:ingredient_sodium, cholesterol=:ingredient_cholesterol,  others=:ingredient_others WHERE id=:ingredient_id");
			$preparedStatement->execute(array(
				':ingredient_name' => $this->name,
				':ingredient_info' => $this->info,
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


			$preparedStatement = $db->prepare("DELETE FROM ing_nutrients WHERE ingredient_id=:ingredient_id");
			$preparedStatement->execute(array(':ingredient_id' => $this->id));
			$n += $preparedStatement->rowCount();

			foreach ($this->nutrients as $nut) {
				$preparedStatement = $db->prepare("INSERT INTO ing_nutrients (nutrient_id, ingredient_id, qty, unit) ".
					"VALUES (:nutrient_id, :ingredient_id, :qty, :unit);");
				$preparedStatement->execute(array(
					':nutrient_id' => $nut['Nutrient']->id,
					':ingredient_id' => $this->id,
					':qty' => $nut['qty'],
					':unit' => $nut['unit']
					));
				$n += $preparedStatement->rowCount();
			}
			return $n;


		} else {
			$preparedStatement = $db->prepare("INSERT INTO ingredients (name, info, unit, qty, typical_unit, typical_qty, kcal, carb, sugar, fibre, protein, fat, sat_fat, sodium, cholesterol, others) ".
				"VALUES (:ingredient_name, :ingredient_info, :ingredient_unit, :ingredient_qty, :ingredient_typical_unit, :ingredient_typical_qty, :ingredient_kcal, :ingredient_carb, :ingredient_sugar, :ingredient_fibre, :ingredient_protein, :ingredient_fat, :ingredient_sat_fat, :ingredient_sodium,  :ingredient_cholesterol,  :ingredient_others);");
			$preparedStatement->execute(array(
				':ingredient_name' => $this->name,
				':ingredient_info' => $this->info,
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

			foreach ($this->nutrients as $nut) {
				$preparedStatement = $db->prepare("INSERT INTO ing_nutrients (nutrient_id, ingredient_id, qty, unit) ".
					"VALUES (:nutrient_id, :ingredient_id, :qty, :unit);");
				$preparedStatement->execute(array(
					':rnutrient_id' => $nut['Nutrient']->id,
					':ingredient_id' => $this->id,
					':qty' => $nut['qty'],
					':unit' => $nut['unit']
					));
				$n += $preparedStatement->rowCount();
			}
			return $n;
		}
	}
}

?>
