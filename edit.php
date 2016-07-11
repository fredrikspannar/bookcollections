<?php

// define app - all includes require this
define('APP', 'Book');

// get model
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'BookModel.php';

// create class and connect to db
$bookDB = new BookModel();

// get collection
$collection = array();
if ( empty($_GET['id']) == FALSE ) {

	// query db
	$collection = $bookDB->get( $_GET['id'] );

	// only first if not empty
	$collection = ( empty($collection) == FALSE ? $collection[0] : $collection );
}


?>
<!DOCTYPE html>
<html lang="en">
	<head> 
		<meta charset="utf-8"/>

		<title>Book sample</title>

		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.0/css/bootstrap-theme.min.css"/>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.0/css/bootstrap.min.css"/>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.0/js/bootstrap.min.js"/></script>

		<link rel="stylesheet" href="css/book.css"/>
	</head>
	<body>

		<div class="container-fluid nav_container">
			<div class="row">
					<p class="title">Book Collections</p>
					<p>
						<a class="btn btn-default" href="index.php" role="button"><span class="glyphicon glyphicon-home"></span> Start</a> 
						<a class="btn btn-default" href="add.php" role="button"><span class="glyphicon glyphicon-plus-sign"></span> Add book(s)</a>
					</p>
			</div>
		</div>

		<div class="container main_container">
			<div class="row">

				<h1>Edit collection with books</h1>

				<?php if ( empty($collection) == FALSE ): ?>
					<form id="edit_form" class="book-form">
						<input type="hidden" id="old_collection_id" name="old_collection_id" value="<?php echo $collection->id; ?>" />

						<div class="panel">
							<div class="panel-body">
								<h3>Book Collection</h3>

								<div class="form-group">
									<label for="collectionName">Name</label> <input type="text" id="collectionName" name="collectionName" value="<?php echo $collection->name; ?>" />
								</div>

								<div class="form-group">
									<label for="collectionSpecialPrice">Special price</label> <input type="text" id="collectionSpecialPrice" name="collectionSpecialPrice" value="<?php echo (empty($collection->collection_price) == FALSE ? $collection->collection_price : ''); ?>" /> <em><small>Leave empty to use sum of all books</small></em>
								</div>

							</div>		
						</div>

						<div class="panel">
							<div class="panel-body panel-books">
								<h3>Books <button id="add_row" class="btn btn-primary btn-sm btn-add-books"><span class="glyphicon glyphicon-plus-sign"></span> Add book</button></h3>

								<?php
								$numbooks = 1;
								foreach($collection->books as $book):
									$row_id = md5(time().uniqid()); ?>
									<div class="book" id="<?php echo $row_id; ?>">
										<h4>Book #<?php echo $numbooks; ?>  <button id="delete_row" class="btn btn-danger btn-sm btn-delete-books" onclick="removeRow('<?php echo $row_id; ?>');"><span class="glyphicon glyphicon-remove"></span> Delete book</button></h4>
										<div class="form-group">
											<label for="bookName[]">Name</label> <input type="text" class="bookName" name="bookName[]" value="<?php echo $book->name; ?>" />
										</div>

										<div class="form-group">
											<label for="bookPrice[]">Price</label> <input type="text" class="bookPrice" name="bookPrice[]" value="<?php echo (empty($book->price) == FALSE ? $book->price : ''); ?>" />
										</div>
									</div>
								
									<?php
									$numbooks++; 
								endforeach;
								?>

							</div>		
						</div>

						
						<p class="submit_paragraph"><input type="submit" class="btn btn-success" value="Update collection" /></p>

					</form>

				<?php else: ?>

						<div class="alert alert-danger" role="alert">
							ERROR: Collection not found!
						</div>

				<?php endif; ?>
			</div>
		</div>

		<div class="modal fade" tabindex="-1" role="dialog" id="modal-1">
		  <div class="modal-dialog">
		    <div class="modal-content">
		      <div class="modal-header">
		        <h4 class="modal-title">Submitting new collection</h4>
		      </div>
		      <div class="modal-body">
		        <p class="wait">Please wait... <br/><img src="images/loading-gear.gif" /></p>
		      </div>
		      <div class="modal-footer"></div>
		    </div><!-- /.modal-content -->
		  </div><!-- /.modal-dialog -->
		</div><!-- /.modal -->

		<?php if ( empty($collection) == FALSE ): ?>
			<script type="text/javascript">

				var numBooks = <?php echo ($numbooks-1); ?>; 

				// when all loaded
				$(document).ready(function(){

					// add handler for form
					$('#edit_form').on("submit", function(e) {
						e.preventDefault();
						
						// show information
						$('#modal-1').modal('show');

						// prepare data to be submitted
						var data = $('#edit_form').serialize();

						// send with ajax
						$.post('processEdit.php', data)
						.done(function(response){
						
							try {
								// try to parse json message if needed
								response = ( response instanceof Object == false ? $.parseJSON(response) : response);

								if ( response.message ) {
									// show returned message
									showMessage(response.message);

								} else if ( response.error ) {
									// show returned error
									showError(response.error);
								
								} else {
									// default message
									showMessage();
								}

							} catch(e) {
								// catch failed json parsed message
								showError("Unkown response!");
							}

						})
						.fail(function(xhr, title, message){
							// server error
							showError(title + ": " + message);
						});

					});

					// add handler for add row
					$('#add_row').on("click", function(e) {
						// prevent default action
						e.preventDefault();

						// iterate number of books
						numBooks++;

						// create a new id for the element
						var new_id = generateId();

						// create html string
						var str = '<div class="book" id="'+new_id+'">'+
								'<h4>Book #'+numBooks+' <button id="delete_row" class="btn btn-danger btn-sm btn-delete-books" onclick="removeRow(\'' + new_id + '\');"><span class="glyphicon glyphicon-remove"></span> Delete book</button></h4>'+
								'<div class="form-group">'+
								'<label for="bookName[]">Name</label> <input type="text" class="bookName" name="bookName[]" value="" />'+
								'</div>'+
								'<div class="form-group">'+
								'<label for="bookPrice[]">Price</label> <input type="text" class="bookPrice" name="bookPrice[]" value="" />'+
								'</div>'+
								'</div>';

						// add after the last
						$('.panel-books .book').last().after(str);
				
					});
				});

				function showError(errorMessage) {
					// no error message?
					errorMessage = ( errorMessage == undefined || errorMessage == "" ? "Unkown error - failed to save" : errorMessage );

					// change popup message
					changePopup("Error", errorMessage, false);				
				}

				function showMessage(successMessage) {
					// no message?
					successMessage = ( successMessage == undefined || successMessage == "" ? "Unkown response from success" : successMessage );

					// change popup message
					changePopup("Success", successMessage, true);
				}

				function changePopup(title, message, returnToStart) {
					/* refactored */

					// change message in modal and add a button to close
					$('#modal-1 .modal-title').html( title );
					$('#modal-1 .modal-body').html("<p>" + message + "</p>");
					$('#modal-1 .modal-footer').html('<button class="btn btn-primary" data-dismiss="modal">Ok</button>');	

					// return to start after confirmation?		
					if ( returnToStart == true ) {	
						// set event handler
						$('#modal-1').on('hidden.bs.modal', function (e) {
					  		document.location = 'index.php';
						});				
					}
				}

				function removeRow(element_id) {
					// should be preceeded with a confirmation
					$('#'+element_id).remove();
				}
			
				function generateId() {
				    var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
				    var string_length = 32;
				    var randomstring = '';

				    for (var x=0;x<string_length;x++) {

				        var letterOrNumber = Math.floor(Math.random() * 2);
				        if (letterOrNumber == 0) {
				            var newNum = Math.floor(Math.random() * 9);
				            randomstring += newNum;
				        } else {
				            var rnum = Math.floor(Math.random() * chars.length);
				            randomstring += chars.substring(rnum,rnum+1);
				        }

				    }
				     return randomstring;
				}
			</script>

		<?php endif; ?>			

	</body>
</html>