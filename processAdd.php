<?php

// define app - all includes require this
define('APP', 'Book');

// get model
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'BookModel.php';

// create class and connect to db
$bookDB = new BookModel();

/*
	example $_POST = array(4) {
		["collectionName"]=> string(12) "collection 1"
		["collectionSpecialPrice"]=> string(1) "2"
		["bookName"]=> array(2) {
			[0]=> string(13) "book numero 1"
			[1]=> string(13) "book numero 2"
		}
		["bookPrice"]=> array(2) {
			[0]=> string(0) ""
			[1]=> string(0) ""
		}
	}
*/

// process data
// NOTE; inputted data should be sanitized and validated
$data = array(
	'name' =>  $_POST['collectionName'],
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
	echo json_encode(array('error' => 'No books added in collection'));	
	exit;
}

// save to db
$result = array();
if ( $bookDB->addCollection($data) == FALSE ) {
	// an error happened
	$result = array('error' => 'Failed to save new collection! <br/><br/>'.$bookDB->getError());

} else {
	// all ok
	$result = array('message' => 'New collection and book(s) saved!');
}


// return result to ajax as JSON
header('Content-Type: application/json');
echo json_encode( $result );