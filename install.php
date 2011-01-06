<?php
include('functions.php');

function populate_db()
{
    try {
        $db = db_connect();
        $sql = file_get_contents( 'recipemaster.sql' );
        $st = $db->prepare( $sql );
        $st->execute();
        echo 'Done!';
    } catch(Exception $e) {
        echo 'Exception occurred: '.$e->getMessage();
    }
}

populate_db();
?>