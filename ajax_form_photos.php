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
	echo '	window.top.window.uploadDone(obj);';
	echo '</script>';

}

if ($_REQUEST['recipe_id']) {
	try {
		$recipe_id = $_REQUEST['recipe_id'];
		$form_type = $_REQUEST['form_type'];
		$photo_id = $_REQUEST['photo_id'];
                $output['where'] = 'dialog-messages';
		$output['seq'] = $_REQUEST['sequence_id'];
		$output['type'] = $form_type;


		$recipe = new Recipe($recipe_id);
		if ($form_type == 'delete_photo') {
			$photo = new Photo("recipe", $photo_id, $recipe->id);
			$n = $photo->delete();
			$output['rowsaffected'] = $n;
			if ($n > 0)
				print_msg('Successfully deleted photo');
			print_msg("Rows affected: ".$n);
			print_json($output);
		}
		else if ($form_type == 'edit_photo') {
			$photo_caption = $_REQUEST['photo_caption'];
			$photo = new Photo("recipe", $photo_id, $recipe->id);
			$n = $photo->updateCaption($photo_caption);
			$output['rowsaffected'] = $n;
			print_json($output);
		}
		else if ($form_type == 'add_photo') {
			$photo_caption = $_REQUEST['photo_caption'];
			$photo = new Photo("recipe", -1, $recipe->id, $photo_caption, $_FILES['recipe_photo']['tmp_name']);
			$n = $photo->store();
			$output['rowsaffected'] = $n;

			if ($n == 0) {
				$output['error'] = 1;
				print_error("Error processing image '".$_FILES['recipe_photo']['name']."', supported image types are: ".$photo->getAllowedExtensionsString());
			} else {
				print_msg('Successfully added photo '.$_FILES['recipe_photo']['name']);
				$output['id'] = $photo->id;
				$output['photo'] = $photo->get();
				$output['thumb'] = $photo->getThumbnail();
				$output['caption'] = $photo->caption;
			}
			print_msg("Rows affected: ".$n);
			print_javascript($output);
			/* XXX: need to feedback via javascript from iframe */
		}
	} catch (Exception $e) {
		print_error('Exception: '.$e->getMessage());
                $output['error'] = 1;
		if ($form_type == 'add_photo') {
			/* XXX: need to feedback via javascript from iframe */
			print_javascript($output);
		} else {
			print_json($output);
		}
	}
}



?>