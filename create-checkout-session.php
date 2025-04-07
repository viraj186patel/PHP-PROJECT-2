<?php
    session_start();
    require_once './stripe-php/init.php'; 

    \Stripe\Stripe::setApiKey('sk_test_51R9ldHPJFE4SlzeJ2Z2APBWdE2cpxmXGEYsBgViEeO7P7dXyAlQqwL08hkSu0VIYpjUeQMecxsgdjr2wCfg4UfXk00HLhEdWIE');

    $amount   = ($_SESSION['total_price'] + 3) * 100;
    $currency = 'usd';

    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => $currency,
                'product_data' => [
                    'name' => 'Book Purchase',
                ],
                'unit_amount' => $amount,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost/obs/process.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost/obs/purchase.php',
    ]);

    header("Location: " . $checkout_session->url);
    exit;
?>
