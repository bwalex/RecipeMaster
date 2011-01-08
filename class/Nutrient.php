<?php

class Nutrient extends Model {
	var $id;
	var $name;
	var $info;
	var $rdi;
	var $unit;

	function Nutrient($id, $name = '', $info = '', $rdi = '', $unit = '') {
		$db = db_connect();
		$result = 0;
		$this->mtime = 0;

		if (!$new) {
			if ($name != '') {
				/* Get by name */
				$preparedStatement = $db->prepare('SELECT * FROM nutrients WHERE name LIKE :name');
				$preparedStatement->execute(array(':name' => $name));
				$result = $preparedStatement->fetch();  
			} else {
				/* Get by ID */
				$preparedStatement = $db->prepare("SELECT * FROM nutrients WHERE id=:id");
				$preparedStatement->execute(array(':id' => $id));
				$result = $preparedStatement->fetch();
			}

			if (!$result) {
				throw new Exception('Nutrient doesn\'t exist!');
			}
			
			$this->name = $result['name'];
			$this->id = $result['id'];
			$this->info = $result['info'];
			$this->unit = $result['unit'];
			$this->rdi = $result['rdi'];
		} else {
			$this->setName($name);
			$this->id = $id;
			$this->info = $info;
			$this->unit = $unit;
			$this->rdi = $rdi;
		}
	}

	function setName($name) {
		if (empty($name))
			throw new Exception('Empty nutrient names are not valid');
		else
			$this->name = $name;
	}

	function save($update = 0) {
		$db = db_connect();
		$preparedStatement = $db->prepare('SELECT * FROM nutrients WHERE name LIKE :name');
		$preparedStatement->execute(array(':name' => $this->name));
		$result = $preparedStatement->fetch();
		if ($result && (($update == 0) || (($update == 1) && ($result['id'] != $this->id)))) {
			throw new Exception('Tried to add new nutrient, but \''.$this->name.'\' already exists');
			return -1;
		}

		if ($update) {
			$preparedStatement = $db->prepare("SELECT * FROM nutrients WHERE id=:id");
			$preparedStatement->execute(array(':id' => $this->id));

			if (!$preparedStatement->fetch()) {
				throw new Exception('Tried to update nutrient, but nutrient doesn\'t exist');
				return -1;
			}
			$preparedStatement = $db->prepare("UPDATE nutrients SET name=:name, info=:info, unit=:unit, rdi=:rdi WHERE id=:id");
			$preparedStatement->execute(array(
				':name' => $this->name,
				':info' => $this->info,
				':iunit' => $this->unit,
				':rdi' => $this->rdi,
				':id' => $this->id
				));
			$n = $preparedStatement->rowCount();
			return $n;
		} else {
			$preparedStatement = $db->prepare("INSERT INTO nutrients (name, info, unit, rdi) ".
				"VALUES (:name, :info, :unit, :rdi);");
			$preparedStatement->execute(array(
				':name' => $this->name,
				':info' => $this->info,
				':iunit' => $this->unit,
				':rdi' => $this->rdi
				));
			$n = $preparedStatement->rowCount();
			$preparedStatement = $db->prepare('SELECT id FROM nutrients WHERE name LIKE :name');
			$preparedStatement->execute(array(':name' => $this->name));
			if ($row = $preparedStatement->fetch()) {
				$this->id = $row['id'];
			} else {
				throw new Exception('Error adding new nutrient');
			}
			return $n;
		}
	}

	function delete() {
		$db = db_connect();

		$preparedStatement = $db->prepare("DELETE FROM nutrients WHERE id=:id");
		$preparedStatement->execute(array(':id' => $this->id));
		$n = $preparedStatement->rowCount();

		return $n;
	}
}

?>
