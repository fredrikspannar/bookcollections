<?php

// define app - all includes require this
define('APP', 'Book');

// get model
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'BookModel.php';

// create class and connect to db
$bookDB = new BookModel();

// init variable that will hold all bookcollections
$books = array();

// no errors?
if ( $bookDB->hasError() === FALSE ) {

	// GET ALL
	$books = $bookDB->getAll();

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
				<h1>Viewing all collections</h1>

				<?php /* any error from db? */
					if ( $bookDB->hasError() ) : ?>

						<div class="alert alert-danger" role="alert">
							ERROR: <?php echo $bookDB->getError(); ?>
						</div>

				<?php else: /* render list with all collections and sublist with books */

					foreach($books as $row):
						// start new collection
						$collectionSum = 0;
					?>
						<ul class="collection">
							<li><p class="collectionTitle"><?php echo $row->name; ?> <a href="edit.php?id=<?php echo $row->id; ?>" class="btn btn-info btn-sm btn-edit"><span class="glyphicon glyphicon-pencil"></span> Edit</a></p>
								<ul>
									<?php foreach($row->books as $row2):
										// add sum collection
										if ( empty($row->collection_price) ):
											$collectionSum += $row2->price;
										endif;
										?>
										<li>
											<?php echo $row2->name.', '.$row2->price.' SKR'; ?>
										</li>
									<?php endforeach; ?>
								</ul>
								<p><br/>Collection price: <?php echo ( empty($row->collection_price) ? $collectionSum : $row->collection_price); ?> SKR</p>
							</li>
						</ul>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>

	</body>
</html>