<?php

// simple security - deny access if no defined constant
if ( !defined("APP") ) die;

// get settings
require_once "config.php";

/**
* Handles all communication with the database
*/
class BookModel {

	private $_db = null;
	private $_db_error = "";


	// database tables - do not change
	const DB_TABLE_BOOKCOLLECTION = 'BookCollection';
	const DB_TABLE_BOOK = 'Book';

	// ################################################################################################################
	public function __construct() {

		// get settings
		global $settings;

		// connect to db
		$this->_db = new mysqli($settings['db_host'], $settings['db_user'], $settings['db_password'], $settings['db_name']);

		// Check connection
		if ( $this->_db->connect_errno )
		{
			// save error
		 	$this->_db_error = "Failed to connect to MySQL: " . $this->_db->connect_error;
		}
	}

	public function __destruct() {
		// close connection when class is destroyed
		$this->_db->close();
		
		// reset 
		$this->_db = null;
		$this->_db_error = "";
	}

	/**
	* Any DB error?
	*
	* @return boolean
	*/
	public function hasError() {
		return ( empty($this->_db_error) == FALSE ? TRUE : FALSE );
	}

	/**
	* Returns error
	* @return string
	*/
	public function getError() {
		return $this->_db_error;
	}

	/**
	* Get all collections and books
	*
	* @return array
	*/
	public function getAll(){
		return $this->get();
	}

	/**
	* Get all collections and books
	* or a single collection if first
	* parameter is sent
	*
	* @param int collectionId optional
	* @return array
	*/
	public function get($collectionId=""){

		// reset error
		$this->_db_error = "";

		// query all
		$sql = 'SELECT * FROM '.BookModel::DB_TABLE_BOOKCOLLECTION;

		if ( empty($collectionId) == FALSE ) {
			$sql .= ' WHERE id = '.$this->_db->escape_string( $collectionId );
		}

		$sql .=' ORDER BY name;';
		
		$query = $this->_db->query($sql);		

		// any error?
		if ( $query === FALSE ) {
			// save error and return empty result
			$this->_db_error = $this->_db->error;
			return array();
		}

		// process result
		$result = array();
		while($row = $query->fetch_object() ){ 
			array_push($result, $row);
		}

		// get books for each collection
		$new_result = array();
		foreach($result as $row) {
			// save as an object and get books for collection
			$o = new stdClass();
			$o->id = $row->id;
			$o->name = $row->name;
			$o->collection_price = $row->collection_price;
			$o->books = $this->_getAllBooks($row->id);
			
			// push to array
			array_push($new_result, $o);

		}

		// and return..
		return $new_result;
	}

	/**
	* Get all books in a collection
	*
	* @param int collectionId
	* @ return array
	*/
	private function _getAllBooks($collectionId) {

		// query all
		$sql = 'SELECT * FROM '.BookModel::DB_TABLE_BOOK.' WHERE collection_id = '.$collectionId.' ORDER BY name;';
		
		$query = $this->_db->query($sql);		

		// any error?
		if ( $query === FALSE ) {
			// save error and return empty result
			$this->_db_error = $this->_db->error;
			return array();
		}


		// process result
		$result = array();
		while($row = $query->fetch_object() ){ 
			array_push($result, $row);
		}


		// and return..
		return $result;	
	}

	/**
	* Add a new collection
	*
	* @param array collection
	* @param boolean noNewCommit optional if no new transaction should be started
	* @return boolean
	*/
	public function addCollection($collection, $noNewCommit=FALSE) {

		if ( $noNewCommit == FALSE ) {
			// reset error
			$this->_db_error = "";

			// autocommit off - all or nothing
			$this->_db->autocommit(FALSE);
		}

		// first - add collection and get id

		// escape data if needed
		$c_name = $this->_db->escape_string( $collection['name'] );
		$c_price = ( empty($collection['price']) ? 'NULL' : '"'.$this->_db->escape_string( $collection['price'] ).'"' );

		// create query
		$sql = 'INSERT INTO '.BookModel::DB_TABLE_BOOKCOLLECTION.' (name, collection_price) VALUES("'.$c_name.'", '.$c_price.');';

		// run
		$query = $this->_db->query($sql);		

		// any error?
		if ( $query === FALSE ) {
			// save error, rollback, reset autocommit and return false
			$this->_db_error = $this->_db->error;

			if ( $noNewCommit == FALSE ) {
				$this->_db->rollback();
				$this->_db->autocommit(TRUE);
			}

			return FALSE;
		}

		// get last id
		$collection_id = $this->_db->insert_id;

		// insert books
		foreach($collection['books'] as $book) {

			// escape data if needed
			$b_name = $this->_db->escape_string( $book['name'] );
			$b_price = $this->_db->escape_string( $book['price'] );

			// create query
			$sql = 'INSERT INTO '.BookModel::DB_TABLE_BOOK.' (name, collection_id, price) VALUES("'.$b_name.'", '.$collection_id.', "'.$b_price.'");';

			// run
			$query = $this->_db->query($sql);		

			// any error?
			if ( $query === FALSE ) {
				// save error, rollback, reset autocommit and return false
				$this->_db_error = $this->_db->error;

				if ( $noNewCommit == FALSE ) {
					$this->_db->rollback();
					$this->_db->autocommit(TRUE);
				}

				return FALSE;
			}

		}

		if ( $noNewCommit === FALSE ) {
			// all ok, commit
			$this->_db->commit();

			// reset autocommit
			$this->_db->autocommit(TRUE);
		}

		// return all good
		return TRUE;
	}

	/**
	* Update a collection
	*
	* @param array collection
	* @return boolean
	*/
	public function updateCollection($collection) {

		// reset error
		$this->_db_error = "";
		
		// autocommit off - all or nothing
		$this->_db->autocommit(FALSE);

		// delete the old collection ( will also cascade delete all books for collection )
		$old_collection_id = $this->_db->escape_string( $collection['old_collection_id'] );
		$sql = 'DELETE FROM '.BookModel::DB_TABLE_BOOKCOLLECTION.' WHERE id = '.$old_collection_id.';';

		// run
		$query = $this->_db->query($sql);		

		// any error?
		if ( $query === FALSE ) {
			// save error, rollback, reset autocommit and return false
			$this->_db_error = $this->_db->error;

			$this->_db->rollback();
			$this->_db->autocommit(TRUE);

			return FALSE;
		}

		// old id is not needed now
		unset($collection['old_collection_id']);

		// ok then, insert all new
		$result = $this->addCollection($collection, TRUE);

		if ( $result === FALSE ) {
			// something FAILED, message is saved

			$this->_db->rollback();
			$this->_db->autocommit(TRUE);

			return FALSE;
		}

		// else all ok, save final to db
		$this->_db->commit();

		// reset autocommit
		$this->_db->autocommit(TRUE);

		// return all good..
		return TRUE;
	}


}
