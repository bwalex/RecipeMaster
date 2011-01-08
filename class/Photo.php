<?php

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
		$export['caption'] = htmlspecialchars($this->caption);
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
				throw new Exception('New photo needs data/file and a parent_id! (' . $data . ', ' . $parent_id . ')');
			if ($parent_id < 0) {
				throw new Exception('Invalid photo parent id!');
			}
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

		return $globalConfig['photo']['Path'].$this->table.'/'.$this->id.'.'.$this->extension;
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

		return $globalConfig['photo']['ThumbPath'].$this->table.'/'.$this->id.'.jpg';
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
		$path = $globalConfig['photo']['Path'].$this->table.'/'.$this->id.'.'.$this->extension;

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
			imagejpeg($image_p, $globalConfig['photo']['ThumbPath'].$this->table.'/'.$this->id.'.jpg');
			$this->new = 0;
		} else {
			throw new Exception('copying image failed');
		}

		return $n;
	}

	function delete() {
		global $globalConfig;

		if ($this->new == 1)
			return 0;

		$db = db_connect();

		$preparedStatement = $db->prepare("DELETE FROM ".$this->table." WHERE id=:id");
		$preparedStatement->execute(array(':id' => $this->id));
		$n = $preparedStatement->rowCount();

		unlink($globalConfig['photo']['Path'].$this->table.'/'.$this->id.'.'.$this->extension);
		unlink($globalConfig['photo']['ThumbPath'].$this->table.'/'.$this->id.'.jpg');
		$this->id = -1;
		$this->new = 1;
		return $n;
	}
}

?>
