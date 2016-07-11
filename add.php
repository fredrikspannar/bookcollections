<?php

// define app - all includes require this
define('APP', 'Book');

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

				<h1>Add new collection with books</h1>

				<form id="add_form" class="book-form">

					<div class="panel">
						<div class="panel-body">
							<h3>Book Collection</h3>

							<div class="form-group">
								<label for="collectionName">Name</label> <input type="text" id="collectionName" name="collectionName" value="" />
							</div>

							<div class="form-group">
								<label for="collectionSpecialPrice">Special price</label> <input type="text" id="collectionSpecialPrice" name="collectionSpecialPrice" value="" /> <em><small>Leave empty to use sum of all books</small></em>
							</div>

						</div>		
					</div>

					<div class="panel">
						<div class="panel-body panel-books">
							<h3>Books <button id="add_row" class="btn btn-primary btn-sm btn-add-books"><span class="glyphicon glyphicon-plus-sign"></span> Add book</button></h3>

							<div class="book">
								<h4>Book #1</h4>
								<div class="form-group">
									<label for="bookName[]">Name</label> <input type="text" class="bookName" name="bookName[]" value="" />
								</div>

								<div class="form-group">
									<label for="bookPrice[]">Price</label> <input type="text" class="bookPrice" name="bookPrice[]" value="" />
								</div>
							</div>

						</div>		
					</div>

					<p class="submit_paragraph"><input type="submit" class="btn btn-success" value="Save new collection" /></p>

				</form>

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

		<script type="text/javascript">

			var numBooks = 1; // start at one

			// when all loaded
			$(document).ready(function(){

				// add handler for form
				$('#add_form').on("submit", function(e) {
					e.preventDefault();
					
					// show information
					$('#modal-1').modal('show');

					// prepare data to be submitted
					var data = $('#add_form').serialize();

					// send with ajax
					$.post('processAdd.php', data)
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

					// create html string
					var str = '<div class="book">'+
							'<h4>Book #'+numBooks+'</h4>'+
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
		</script>

	</body>
</html>