# TurtleCoin Gateway for WooCommerce

## Features

* Payment validation done through `turtle-service`.
* Validates payments with `cron`, so does not require users to stay on the order confirmation page for their order to validate.
* Order status updates are done through AJAX instead of Javascript page reloads.
* Customers can pay with multiple transactions and are notified as soon as transactions hit the mempool.
* Configurable block confirmations, from `0` for zero confirm to `60` for high ticket purchases.
* Live price updates every minute; total amount due is locked in after the order is placed for a configurable amount of time (default 60 minutes) so the price does not change after order has been made.
* Hooks into emails, order confirmation page, customer order history page, and admin order details page.
* View all payments received to your wallet with links to the blockchain explorer and associated orders.
* Optionally display all prices on your store in terms of TurtleCoin.
* Shortcodes! Display exchange rates in numerous currencies.

## Requirements

* TurtleCoin wallet to receive payments.
* [BCMath](http://php.net/manual/en/book.bc.php) - A PHP extension used for arbitrary precision maths

## Installing the plugin

* Download the plugin from the [releases page](https://github.com/turtlecoin/turtlecoin-woocommerce-gateway/releases) or clone with `git clone https://github.com/turtlecoin/turtlecoin-woocommerce-gateway.git`
* Unzip or place the `turtlecoin-woocommerce-gateway` folder in the `wp-content/plugins` directory.
* Activate "TurtleCoin Woocommerce Gateway" in your WordPress admin dashboard.
* It is highly recommended that you use native cronjobs instead of WordPress's "Poor Man's Cron" by adding `define('DISABLE_WP_CRON', true);` into your `wp-config.php` file and adding `* * * * * wget -q -O - https://yourstore.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1` to your crontab.

# Set-up TurtleCoin daemon and Turtle-Service

* Root access to your webserver
* Latest [TurtleCoin-currency binaries](https://github.com/turtlecoin/turtlecoin/releases)

After downloading (or compiling) the TurtleCoin binaries on your server, run `TurtleCoind` and `turtle-service`. You can skip running `TurtleCoind` by using a remote node with `turtle-service` by adding `--daemon-address` and the address of a public node.

Note on security: using this option, while the most secure, requires you to run the Turtle-Service program on your server. Best practice for this is to use a view-only wallet since otherwise your server would be running a hot-wallet and a security breach could allow hackers to empty your funds.

## Configuration

* `Enable / Disable` - Turn on or off TurtleCoin gateway. (Default: Disable)
* `Title` - Name of the payment gateway as displayed to the customer. (Default: TurtleCoin Gateway)
* `Discount for using TurtleCoin` - Percentage discount applied to orders for paying with TurtleCoin. Can also be negative to apply a surcharge. (Default: 0)
* `Order valid time` - Number of seconds after order is placed that the transaction must be seen in the mempool. (Default: 3600 [1 hour])
* `Number of confirmations` - Number of confirmations the transaction must recieve before the order is marked as complete. Use `0` for nearly instant confirmation. (Default: 5)
* `TurtleCoin Address` - Your public TurtleCoin address starting with TRTL. (No default)
* `Turtle-Service Host/IP` - IP address where `turtle-service` is running. It is highly discouraged to run the wallet anywhere other than the local server! (Default: 127.0.0.1)
* `Turtle-Service Port` - Port `turtle-service` is bound to with the `--bind-port` argument. (Default 8070)
* `Turtle-Service Password` - Password `turtle-service` was started with using the `--rpc-password` argument. (Default: blank)
* `Show QR Code` - Show payment QR codes. (Default: unchecked)
* `Show Prices in TurtleCoin` - Convert all prices on the frontend to TurtleCoin. Experimental feature, only use if you do not accept any other payment option. (Default: unchecked)
* `Display Decimals` (if show prices in TurtleCoin is enabled) - Number of decimals to round prices to on the frontend. The final order amount will not be rounded. (Default: 2)

## Shortcodes

This plugin makes available two shortcodes that you can use in your theme.

#### Live price shortcode

This will display the price of TurtleCoin in the selected currency. If no currency is provided, the store's default currency will be used.

```
[turtlecoin-price]
[turtlecoin-price currency="BTC"]
[turtlecoin-price currency="USD"]
```
Will display:
```
1 TRTL = 0.00000149 LTC
1 TRTL = 0.00003815 USD
```


#### TurtleCoin accepted here badge

This will display a badge showing that you accept TurtleCoin-currency.

`[turtlecoin-accepted-here]`

![TurtleCoin Accepted Here](/assets/images/turtlecoin-accepted-here.png?raw=true "TurtleCoin Accepted Here")

## Donations

mosu-forge: TRTLuy85x1U8LN37NcMQr4VyyqkxpkmTsBj7iF1zNg2mjjNW4m41RbXPi1tZvvEpcs4WR7SBLj1eSRH3h7pQRRMFFNSQqEoBB7L
