<?php
/*
Plugin Name: TurtleCoin Woocommerce Gateway
Plugin URI:
Description: Extends WooCommerce by adding a TurtleCoin Gateway
Version: 3.0.0
Tested up to: 4.9.8
Author: mosu-forge, SerHack
Author URI: https://monerointegrations.com/
*/
// This code isn't for Dark Net Markets, please report them to Authority!

defined( 'ABSPATH' ) || exit;

// Constants, you can edit these if you fork this repo
define('TURTLECOIN_GATEWAY_EXPLORER_URL', 'https://explorer.turtlecoin.lol');
define('TURTLECOIN_GATEWAY_ATOMIC_UNITS', 2);
define('TURTLECOIN_GATEWAY_ATOMIC_UNIT_THRESHOLD', 100); // Amount under in atomic units payment is valid
define('TURTLECOIN_GATEWAY_DIFFICULTY_TARGET', 30);

// Do not edit these constants
define('TURTLECOIN_GATEWAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TURTLECOIN_GATEWAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TURTLECOIN_GATEWAY_ATOMIC_UNITS_POW', pow(10, TURTLECOIN_GATEWAY_ATOMIC_UNITS));
define('TURTLECOIN_GATEWAY_ATOMIC_UNITS_SPRINTF', '%.'.TURTLECOIN_GATEWAY_ATOMIC_UNITS.'f');

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action('plugins_loaded', 'turtlecoin_init', 1);
function turtlecoin_init() {

    // If the class doesn't exist (== WooCommerce isn't installed), return NULL
    if (!class_exists('WC_Payment_Gateway')) return;

    // If we made it this far, then include our Gateway Class
    require_once('include/class-turtlecoin-gateway.php');

    // Create a new instance of the gateway so we have static variables set up
    new TurtleCoin_Gateway($add_action=false);

    // Include our Admin interface class
    require_once('include/admin/class-turtlecoin-admin-interface.php');

    add_filter('woocommerce_payment_gateways', 'turtlecoin_gateway');
    function turtlecoin_gateway($methods) {
        $methods[] = 'TurtleCoin_Gateway';
        return $methods;
    }

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'turtlecoin_payment');
    function turtlecoin_payment($links) {
        $plugin_links = array(
            '<a href="'.admin_url('admin.php?page=turtlecoin_gateway_settings').'">'.__('Settings', 'turtlecoin_gateway').'</a>'
        );
        return array_merge($plugin_links, $links);
    }

    add_filter('cron_schedules', 'turtlecoin_cron_add_one_minute');
    function turtlecoin_cron_add_one_minute($schedules) {
        $schedules['one_minute'] = array(
            'interval' => 60,
            'display' => __('Once every minute', 'turtlecoin_gateway')
        );
        return $schedules;
    }

    add_action('wp', 'turtlecoin_activate_cron');
    function turtlecoin_activate_cron() {
        if(!wp_next_scheduled('turtlecoin_update_event')) {
            wp_schedule_event(time(), 'one_minute', 'turtlecoin_update_event');
        }
    }

    add_action('turtlecoin_update_event', 'turtlecoin_update_event');
    function turtlecoin_update_event() {
        TurtleCoin_Gateway::do_update_event();
    }

    add_action('woocommerce_thankyou_'.TurtleCoin_Gateway::get_id(), 'turtlecoin_order_confirm_page');
    add_action('woocommerce_order_details_after_order_table', 'turtlecoin_order_page');
    add_action('woocommerce_email_after_order_table', 'turtlecoin_order_email');

    function turtlecoin_order_confirm_page($order_id) {
        TurtleCoin_Gateway::customer_order_page($order_id);
    }
    function turtlecoin_order_page($order) {
        if(!is_wc_endpoint_url('order-received'))
            TurtleCoin_Gateway::customer_order_page($order);
    }
    function turtlecoin_order_email($order) {
        TurtleCoin_Gateway::customer_order_email($order);
    }

    add_action('wc_ajax_turtlecoin_gateway_payment_details', 'turtlecoin_get_payment_details_ajax');
    function turtlecoin_get_payment_details_ajax() {
        TurtleCoin_Gateway::get_payment_details_ajax();
    }

    add_filter('woocommerce_currencies', 'turtlecoin_add_currency');
    function turtlecoin_add_currency($currencies) {
        $currencies['TurtleCoin'] = __('TurtleCoin', 'turtlecoin_gateway');
        return $currencies;
    }

    add_filter('woocommerce_currency_symbol', 'turtlecoin_add_currency_symbol', 10, 2);
    function turtlecoin_add_currency_symbol($currency_symbol, $currency) {
        switch ($currency) {
        case 'TurtleCoin':
            $currency_symbol = 'TRTL';
            break;
        }
        return $currency_symbol;
    }

    if(TurtleCoin_Gateway::use_turtlecoin_price()) {

        // This filter will replace all prices with amount in TurtleCoin (live rates)
        add_filter('wc_price', 'turtlecoin_live_price_format', 10, 3);
        function turtlecoin_live_price_format($price_html, $price_float, $args) {
            if(!isset($args['currency']) || !$args['currency']) {
                global $woocommerce;
                $currency = strtoupper(get_woocommerce_currency());
            } else {
                $currency = strtoupper($args['currency']);
            }
            return TurtleCoin_Gateway::convert_wc_price($price_float, $currency);
        }

        // These filters will replace the live rate with the exchange rate locked in for the order
        // We must be careful to hit all the hooks for price displays associated with an order,
        // else the exchange rate can change dynamically (which it should for an order)
        add_filter('woocommerce_order_formatted_line_subtotal', 'turtlecoin_order_item_price_format', 10, 3);
        function turtlecoin_order_item_price_format($price_html, $item, $order) {
            return TurtleCoin_Gateway::convert_wc_price_order($price_html, $order);
        }

        add_filter('woocommerce_get_formatted_order_total', 'turtlecoin_order_total_price_format', 10, 2);
        function turtlecoin_order_total_price_format($price_html, $order) {
            return TurtleCoin_Gateway::convert_wc_price_order($price_html, $order);
        }

        add_filter('woocommerce_get_order_item_totals', 'turtlecoin_order_totals_price_format', 10, 3);
        function turtlecoin_order_totals_price_format($total_rows, $order, $tax_display) {
            foreach($total_rows as &$row) {
                $price_html = $row['value'];
                $row['value'] = TurtleCoin_Gateway::convert_wc_price_order($price_html, $order);
            }
            return $total_rows;
        }

    }

    add_action('wp_enqueue_scripts', 'turtlecoin_enqueue_scripts');
    function turtlecoin_enqueue_scripts() {
        if(TurtleCoin_Gateway::use_turtlecoin_price())
            wp_dequeue_script('wc-cart-fragments');
        if(TurtleCoin_Gateway::use_qr_code())
            wp_enqueue_script('turtlecoin-qr-code', TURTLECOIN_GATEWAY_PLUGIN_URL.'assets/js/qrcode.min.js');

        wp_enqueue_script('turtlecoin-clipboard-js', TURTLECOIN_GATEWAY_PLUGIN_URL.'assets/js/clipboard.min.js');
        wp_enqueue_script('turtlecoin-gateway', TURTLECOIN_GATEWAY_PLUGIN_URL.'assets/js/turtlecoin-gateway-order-page.js');
        wp_enqueue_style('turtlecoin-gateway', TURTLECOIN_GATEWAY_PLUGIN_URL.'assets/css/turtlecoin-gateway-order-page.css');
    }

    // [turtlecoin-price currency="USD"]
    // currency: BTC, GBP, etc
    // if no none, then default store currency
    function turtlecoin_price_func( $atts ) {
        global  $woocommerce;
        $a = shortcode_atts( array(
            'currency' => get_woocommerce_currency()
        ), $atts );

        $currency = strtoupper($a['currency']);
        $rate = TurtleCoin_Gateway::get_live_rate($currency);
        if($currency == 'BTC')
            $rate_formatted = sprintf('%.8f', $rate / 1e8);
        else
            $rate_formatted = sprintf('%.8f', $rate / 1e8);

        return "<span class=\"turtlecoin-price\">1 TRTL = $rate_formatted $currency</span>";
    }
    add_shortcode('turtlecoin-price', 'turtlecoin_price_func');


    // [turtlecoin-accepted-here]
    function turtlecoin_accepted_func() {
        return '<img src="'.TURTLECOIN_GATEWAY_PLUGIN_URL.'assets/images/turtlecoin-accepted-here.png" />';
    }
    add_shortcode('turtlecoin-accepted-here', 'turtlecoin_accepted_func');

}

register_deactivation_hook(__FILE__, 'turtlecoin_deactivate');
function turtlecoin_deactivate() {
    $timestamp = wp_next_scheduled('turtlecoin_update_event');
    wp_unschedule_event($timestamp, 'turtlecoin_update_event');
}

register_activation_hook(__FILE__, 'turtlecoin_install');
function turtlecoin_install() {
    global $wpdb;
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . "turtlecoin_gateway_quotes";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               order_id BIGINT(20) UNSIGNED NOT NULL,
               payment_id VARCHAR(64) DEFAULT '' NOT NULL,
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               paid TINYINT NOT NULL DEFAULT 0,
               confirmed TINYINT NOT NULL DEFAULT 0,
               pending TINYINT NOT NULL DEFAULT 1,
               created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (order_id)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "turtlecoin_gateway_quotes_txids";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
               payment_id VARCHAR(64) DEFAULT '' NOT NULL,
               txid VARCHAR(64) DEFAULT '' NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               height MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
               PRIMARY KEY (id),
               UNIQUE KEY (payment_id, txid, amount)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "turtlecoin_gateway_live_rates";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (currency)
               ) $charset_collate;";
        dbDelta($sql);
    }
}
