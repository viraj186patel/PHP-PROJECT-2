<?php
	session_start();
	$_SESSION['err'] = 1;
	foreach($_POST as $key => $value){
		if(trim($value) == ''){
			$_SESSION['err'] = 0;
		}
		break;
	}

	if($_SESSION['err'] == 0){
		header("Location: checkout.php");
	} else {
		unset($_SESSION['err']);
	}


	$_SESSION['ship'] = array();
	foreach($_POST as $key => $value){
		if($key != "submit"){
			$_SESSION['ship'][$key] = $value;
		}
	}
	require_once "./functions/database_functions.php";
	// print out header here
	$title = "Purchase";
	require "./template/header.php";
	// connect database
	?>
	<h4 class="fw-bolder text-center">Payment</h4>
      <center>
        <hr class="bg-warning" style="width:5em;height:3px;opacity:1">
      </center>
<?php
	if(isset($_SESSION['cart']) && (array_count_values($_SESSION['cart']))){
?>
	<div class="card rounded-0 shadow mb-3">
		<div class="card-body">
			<div class="container-fluid">
				<table class="table">
					<tr>
						<th>Item</th>
						<th>Price</th>
						<th>Quantity</th>
						<th>Total</th>
					</tr>
						<?php
							foreach($_SESSION['cart'] as $isbn => $qty){
								$conn = db_connect();
								$book = mysqli_fetch_assoc(getBookByIsbn($conn, $isbn));
						?>
					<tr>
						<td><?php echo $book['book_title'] . " by " . $book['book_author']; ?></td>
						<td><?php echo "$" . $book['book_price']; ?></td>
						<td><?php echo $qty; ?></td>
						<td><?php echo "$" . $qty * $book['book_price']; ?></td>
					</tr>
					<?php } ?>
					<tr>
						<th>&nbsp;</th>
						<th>&nbsp;</th>
						<th><?php echo $_SESSION['total_items']; ?></th>
						<th><?php echo "$" . $_SESSION['total_price']; ?></th>
					</tr>
					<tr>
						<td>Shipping</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>$3</td>
					</tr>
					<tr>
						<th>Total Including Shipping</th>
						<th>&nbsp;</th>
						<th>&nbsp;</th>
						<th><?php echo "$" . ($_SESSION['total_price'] + 3); ?></th>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<div class="row justify-content-center">
		<div class="col-lg-5 col-md-8 col-sm-10 col-xs-12">
			<div class="card rounded-0 shadow">
				<div class="card-header">
					<div class="card-title h6 fw-bold">Payment</div>
				</div>
				<div class="card-body">
					<div class="container-fluid">
						<form method="post" action="create-checkout-session.php" class="form-horizontal">
							<div class="form-group mb-3">
								<div class="d-grid gap-2">
									<button type="submit" class="btn btn-primary rounded-0">Pay with Stripe</button>
									<button type="reset" class="btn btn-default bg-light bg-gradient border rounded-0">Cancel</button>
								</div>
							</div>
						</form>
						<p class="fw-light fst-italic"><small class="text-muted">Please press Purchase to confirm your purchase, or Continue Shopping to add or remove items.</small></p>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
	} else {
		echo "<p class=\"text-warning\">Your cart is empty! Please make sure you add some books in it!</p>";
	}
	if(isset($conn)){ mysqli_close($conn); }
	require_once "./template/footer.php";
?>