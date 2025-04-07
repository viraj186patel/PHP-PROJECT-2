<?php
    session_start();
    require_once './stripe-php/init.php';
    require_once './functions/database_functions.php';

    // Set Stripe secret key
    \Stripe\Stripe::setApiKey('sk_test_51R9ldHPJFE4SlzeJ2Z2APBWdE2cpxmXGEYsBgViEeO7P7dXyAlQqwL08hkSu0VIYpjUeQMecxsgdjr2wCfg4UfXk00HLhEdWIE');

    // Check if Stripe session_id is present
    if (!isset($_GET['session_id'])) {
        echo "<h2>Payment Failed!</h2>";
        exit;
    }

    $conn = db_connect();

    // Fetch session details from Stripe
    $session_id = $_GET['session_id'];
    $session    = \Stripe\Checkout\Session::retrieve($session_id);

    // Get payment details
    $stripe_payment_id = $session->payment_intent;
    $amount = $session->amount_total / 100; 
    $currency = strtoupper($session->currency);
    $payment_status = $session->payment_status;

    // Extract shipping details from session
    extract($_SESSION['ship']);
    $cart = $_SESSION['cart'];
    $total_price = $_SESSION['total_price'];

    // Save customer
    $customerid = getCustomerId($name, $address, $city, $zip_code, $country);
    if (!$customerid) {
        $customerid = setCustomerId($name, $address, $city, $zip_code, $country);
    }

    // Save order
    $date = date("Y-m-d H:i:s");
    insertIntoOrder($conn, $customerid, $total_price, $date, $name, $address, $city, $zip_code, $country);

    // Get new order ID
    $orderid = getOrderId($conn, $customerid);

    // Save order items
    foreach ($cart as $isbn => $qty) {
        $bookprice = getbookprice($isbn);
        $query = "INSERT INTO order_items (orderid, book_isbn, item_price, quantity) VALUES ('$orderid', '$isbn', '$bookprice', '$qty')";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            echo "Failed to insert order items: " . mysqli_error($conn);
            exit;
        }
    }

    // Save payment details
    $stmt = $conn->prepare("INSERT INTO payments (order_id, stripe_payment_id, amount, currency, payment_status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $orderid, $stripe_payment_id, $amount, $currency, $payment_status);
    $stmt->execute();
    $stmt->close();

    // Cleanup session
    session_unset();
    session_destroy();

    // Display success message
    require './template/header.php';
    ?>
    <div class="alert alert-success rounded-0 my-4">
        <h4 class="text-success">Payment Successful!</h4>
        <p>Your order has been processed. We'll be reaching out to confirm. Thanks for shopping!</p>
    </div>
    <?php
    require './template/footer.php';
?>