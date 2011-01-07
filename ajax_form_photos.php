<?php



include('functions.php');

$output = array();
$output['error'] = 0;
$output['msg'] = array();
$output['errmsg'] = array();
$output['rowsaffected'] = 0;

function print_error($msg) {
	global $output;
	array_push($output['errmsg'], $msg);
}

function print_msg($msg) {
	global $output;
	array_push($output['msg'], $msg);
}

function print_json($output) {
	header('Content-type: application/json');
	echo json_encode($output);
}

function print_javascript($output) {
	//var myObject = eval('(' + myJSONtext + ')');
	echo '<script language="javascript" type="text/javascript">';
	echo 'var obj = '.json_encode($output).';';
	//echo 'console.log(obj);';
	echo '	window.top.window.uploadDone(obj);';
	echo '</script>';

}
try {
	$form_type = $_REQUEST['form_type'];
	$photo_id = $_REQUEST['photo_id'];
	$output['seq'] = $_REQUEST['sequence_id'];
	$output['type'] = $form_type;
        $output['whereOk'] = $_REQUEST['where_ok'];
        $output['whereError'] = $_REQUEST['where_error'];
	$parent_id = $_REQUEST['parent_id'];
	$photo_type = $_REQUEST['parent_type'];

	if ($photo_type == 'recipe') {
		$recipe = new Recipe($parent_id);
	} else if ($photo_type == 'ingredient') {
		$ingredient = new Ingredient($parent_id);
	} else {
		throw new Exception('invalid parentType!');
	}

	$output['parentType'] = $photo_type;
	$output['parentId'] = $parent_id;

	if ($form_type == 'delete_photo') {
		$photo = new Photo($photo_type, $photo_id, $parent_id);
		$n = $photo->delete();
		$output['rowsaffected'] = $n;
		if ($n > 0)
			print_msg('Successfully deleted photo');
		print_msg("Rows affected: ".$n);
		print_json($output);
	}
	else if ($form_type == 'edit_photo') {
		$photo_caption = $_REQUEST['photo_caption'];
		$photo = new Photo($photo_type, $photo_id, $parent_id);
		$n = $photo->updateCaption($photo_caption);
		$output['rowsaffected'] = $n;
		if ($n > 0)
			print_msg('Successfully updated photo caption');
		print_msg("Rows affected: ".$n);
		print_json($output);
	}
	else if ($form_type == 'add_photo') {
		$photo_caption = $_REQUEST['photo_caption'];
		//$output['debug'] = $_FILES;

		if ($_FILES[$photo_type.'_photo']['error'] /*!= UPLOAD_ERR_OK*/) {
			$max_upload_size = min(let_to_num(ini_get('post_max_size')), let_to_num(ini_get('upload_max_filesize')));
			throw new Exception("Maximum upload file size is ".($max_upload_size/(1024*1024))."MB.");
			/* NOTREACHED */
		}

		$photo = new Photo($photo_type, -1, $parent_id, $photo_caption, $_FILES[$photo_type.'_photo']['tmp_name']);
		$n = $photo->store();
		$output['rowsaffected'] = $n;

		if ($n == 0) {
			$output['error'] = 1;
			print_error("Error processing image '".$_FILES[$photo_type.'_photo']['name']."', supported image types are: ".$photo->getAllowedExtensionsString());
		} else {
			print_msg('Successfully added photo '.$_FILES[$photo_type.'_photo']['name']);
			$output['id'] = $photo->id;
			$output['photo'] = $photo->get();
			$output['thumb'] = $photo->getThumbnail();
			$output['caption'] = $photo->caption;
		}
		print_msg("Rows affected: ".$n);
		print_javascript($output);
		/* need to feedback via javascript from iframe */
	}
} catch (Exception $e) {
	print_error('Exception: '.$e->getMessage());
        $output['error'] = 1;
	if ($form_type == 'add_photo') {
		/* need to feedback via javascript from iframe */
		print_javascript($output);
	} else {
		print_json($output);
	}
}



?>