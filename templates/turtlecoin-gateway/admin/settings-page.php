<?php foreach($errors as $error): ?>
<div class="error"><p><strong>TurtleCoin Gateway Error</strong>: <?php echo $error; ?></p></div>
<?php endforeach; ?>

<h1>TurtleCoin Gateway Settings</h1>

<div style="border:1px solid #ddd;padding:5px 10px;">
    <?php
         echo 'Wallet height: ' . $balance['height'] . '</br>';
         echo 'Your balance is: ' . $balance['balance'] . '</br>';
         echo 'Unlocked balance: ' . $balance['unlocked_balance'] . '</br>';
         ?>
</div>

<table class="form-table">
    <?php echo $settings_html ?>
</table>

<h4><a href="https://github.com/turtlecoin/turtlecoin-woocommerce-gateway">Learn more about using the TurtleCoin payment gateway</a></h4>

<script>
function turtlecoinUpdateFields() {
    var useTurtleCoinPrices = jQuery("#woocommerce_turtlecoin_gateway_use_turtlecoin_price").is(":checked");
    if(useTurtleCoinPrices) {
        jQuery("#woocommerce_turtlecoin_gateway_use_turtlecoin_price_decimals").closest("tr").show();
    } else {
        jQuery("#woocommerce_turtlecoin_gateway_use_turtlecoin_price_decimals").closest("tr").hide();
    }
}
turtlecoinUpdateFields();
jQuery("#woocommerce_turtlecoin_gateway_use_turtlecoin_price").change(turtlecoinUpdateFields);
</script>

<style>
#woocommerce_turtlecoin_gateway_turtlecoin_address,
#woocommerce_turtlecoin_gateway_viewkey {
    width: 100%;
}
</style>
