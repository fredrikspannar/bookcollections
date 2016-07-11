<?php

// define app - all includes require this
define('APP', 'Book');

// get model
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'BookModel.php';

// create class and connect to db
$bookDB = new BookModel();

/*
	example $_POST = array(5) {
		  ["old_collection_id"]=> string(1) "1"
		  ["collectionName"]=> string(4) "test"
		  ["collectionSpecialPrice"]=> string(0) ""
		  ["bookName"]=>
		  array(2) {
		    [0]=> string(10) "first book"
		    [1]=> string(11) "second book"
		  }
		  ["bookPrice"]=>
		  array(2) {
		    [0]=> string(2) "12"
		    [1]=> string(2) "17"
		  }
		}

*/


// process data
// NOTE; inputted data should be sanitized and validated
$data = array(
	'name' =>  $_POST['collectionName'],
	'old_collection_id' =>  $_POST['old_collection_id'],
	'price' => ( empty($_POST['collectionSpecialPrice']) == FALSE ? $_POST['collectionSpecialPrice'] : ''),
	'books' => array()
);

$bookNames = $_POST['bookName'];
$bookPrices = $_POST['bookPrice'];

// add books and prices
for($n=0; $n<count($bookNames); $n++) {

	if ( empty($bookNames[$n]) == FALSE ) {
		// add item
		$data['books'][] = array(
			'name' => $bookNames[$n],
			'price' => ( empty($bookPrices[$n])==FALSE ? $bookPrices[$n] : 0 ) // can be empty if special price for collection
		);
	}
}

if ( empty($data['books']) ) {
	// no books - return error
	header('Content-Type: application/json');
	echo json_encode(array('error' => 'No books in collection'));	
	exit;
}

// save to db
$result = array();
if ( $bookDB->updateCollection($data) == FALSE ) {
	// an error happened
	$result = array('error' => 'Failed to update collection! <br/><br/>'.$bookDB->getError());

} else {
	// all ok
	$result = array('message' => 'Collection and book(s) has been updated!');
}


// return result to ajax as JSON
header('Content-Type: application/json');
echo json_encode( $result );