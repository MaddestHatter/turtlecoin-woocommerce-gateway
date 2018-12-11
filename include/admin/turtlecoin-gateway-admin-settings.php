<?php

defined( 'ABSPATH' ) || exit;

return array(
    'enabled' => array(
        'title' => __('Enable / Disable', 'turtlecoin_gateway'),
        'label' => __('Enable this payment gateway', 'turtlecoin_gateway'),
        'type' => 'checkbox',
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Title', 'turtlecoin_gateway'),
        'type' => 'text',
        'desc_tip' => __('Payment title the customer will see during the checkout process.', 'turtlecoin_gateway'),
        'default' => __('TurtleCoin Gateway', 'turtlecoin_gateway')
    ),
    'description' => array(
        'title' => __('Description', 'turtlecoin_gateway'),
        'type' => 'textarea',
        'desc_tip' => __('Payment description the customer will see during the checkout process.', 'turtlecoin_gateway'),
        'default' => __('Pay securely using TurtleCoin. You will be provided payment details after checkout.', 'turtlecoin_gateway')
    ),
    'discount' => array(
        'title' => __('Discount for using TurtleCoin', 'turtlecoin_gateway'),
        'desc_tip' => __('Provide a discount to your customers for making a private payment with TurtleCoin', 'turtlecoin_gateway'),
        'description' => __('Enter a percentage discount (i.e. 5 for 5%) or leave this empty if you do not wish to provide a discount', 'turtlecoin_gateway'),
        'type' => __('number'),
        'default' => '0'
    ),
    'valid_time' => array(
        'title' => __('Order valid time', 'turtlecoin_gateway'),
        'desc_tip' => __('Amount of time order is valid before expiring', 'turtlecoin_gateway'),
        'description' => __('Enter the number of seconds that the funds must be received in after order is placed. 3600 seconds = 1 hour', 'turtlecoin_gateway'),
        'type' => __('number'),
        'default' => '3600'
    ),
    'confirms' => array(
        'title' => __('Number of confirmations', 'turtlecoin_gateway'),
        'desc_tip' => __('Number of confirms a transaction must have to be valid', 'turtlecoin_gateway'),
        'description' => __('Enter the number of confirms that transactions must have. Enter 0 to zero-confim. Each confirm will take approximately four minutes', 'turtlecoin_gateway'),
        'type' => __('number'),
        'default' => '10'
    ),
    'turtlecoin_address' => array(
        'title' => __('TurtleCoin Address', 'turtlecoin_gateway'),
        'label' => __('Public TurtleCoin Address'),
        'type' => 'text',
        'desc_tip' => __('TurtleCoin Wallet Address (TRTL)', 'turtlecoin_gateway')
    ),
    'daemon_host' => array(
        'title' => __('Turtle-Service Host/IP', 'turtlecoin_gateway'),
        'type' => 'text',
        'desc_tip' => __('This is the turtle-service Host/IP to authorize the payment with', 'turtlecoin_gateway'),
        'default' => '127.0.0.1',
    ),
    'daemon_port' => array(
        'title' => __('Turtle-Service Port', 'turtlecoin_gateway'),
        'type' => __('number'),
        'desc_tip' => __('This is the turtle-service port to authorize the payment with', 'turtlecoin_gateway'),
        'default' => '8070',
    ),
    'daemon_password' => array(
        'title' => __('Turtle-Service Password', 'turtlecoin_gateway'),
        'type' => 'text',
        'desc_tip' => __('This is the turtle-service password to authorize the payment with', 'turtlecoin_gateway'),
        'default' => '',
    ),
    'show_qr' => array(
        'title' => __('Show QR Code', 'turtlecoin_gateway'),
        'label' => __('Show QR Code', 'turtlecoin_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to show a QR code after checkout with payment details.'),
        'default' => 'no'
    ),
    'use_turtlecoin_price' => array(
        'title' => __('Show Prices in TurtleCoin', 'turtlecoin_gateway'),
        'label' => __('Show Prices in TurtleCoin', 'turtlecoin_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to convert ALL prices on the frontend to TurtleCoin (experimental)'),
        'default' => 'no'
    ),
    'use_turtlecoin_price_decimals' => array(
        'title' => __('Display Decimals', 'turtlecoin_gateway'),
        'type' => __('number'),
        'description' => __('Number of decimal places to display on frontend. Upon checkout exact price will be displayed.'),
        'default' => 2,
    ),
);
