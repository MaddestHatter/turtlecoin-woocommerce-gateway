/*
 * Copyright (c) 2018, Ryo Currency Project
*/
function turtlecoin_showNotification(message, type='success') {
    var toast = jQuery('<div class="' + type + '"><span>' + message + '</span></div>');
    jQuery('#turtlecoin_toast').append(toast);
    toast.animate({ "right": "12px" }, "fast");
    setInterval(function() {
        toast.animate({ "right": "-400px" }, "fast", function() {
            toast.remove();
        });
    }, 2500)
}
function turtlecoin_showQR(show=true) {
    jQuery('#turtlecoin_qr_code_container').toggle(show);
}
function turtlecoin_fetchDetails() {
    var data = {
        '_': jQuery.now(),
        'order_id': turtlecoin_details.order_id
    };
    jQuery.get(turtlecoin_ajax_url, data, function(response) {
        if (typeof response.error !== 'undefined') {
            console.log(response.error);
        } else {
            turtlecoin_details = response;
            turtlecoin_updateDetails();
        }
    });
}

function turtlecoin_updateDetails() {

    var details = turtlecoin_details;

    jQuery('#turtlecoin_payment_messages').children().hide();
    switch(details.status) {
        case 'unpaid':
            jQuery('.turtlecoin_payment_unpaid').show();
            jQuery('.turtlecoin_payment_expire_time').html(details.order_expires);
            break;
        case 'partial':
            jQuery('.turtlecoin_payment_partial').show();
            jQuery('.turtlecoin_payment_expire_time').html(details.order_expires);
            break;
        case 'paid':
            jQuery('.turtlecoin_payment_paid').show();
            jQuery('.turtlecoin_confirm_time').html(details.time_to_confirm);
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'confirmed':
            jQuery('.turtlecoin_payment_confirmed').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'expired':
            jQuery('.turtlecoin_payment_expired').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'expired_partial':
            jQuery('.turtlecoin_payment_expired_partial').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
    }

    jQuery('#turtlecoin_exchange_rate').html('1 TRTL = '+details.rate_formatted+' '+details.currency);
    jQuery('#turtlecoin_total_amount').html(details.amount_total_formatted);
    jQuery('#turtlecoin_total_paid').html(details.amount_paid_formatted);
    jQuery('#turtlecoin_total_due').html(details.amount_due_formatted);

    jQuery('#turtlecoin_integrated_address').html(details.integrated_address);

    if(turtlecoin_show_qr) {
        var qr = jQuery('#turtlecoin_qr_code').html('');
        new QRCode(qr.get(0), details.qrcode_uri);
    }

    if(details.txs.length) {
        jQuery('#turtlecoin_tx_table').show();
        jQuery('#turtlecoin_tx_none').hide();
        jQuery('#turtlecoin_tx_table tbody').html('');
        for(var i=0; i < details.txs.length; i++) {
            var tx = details.txs[i];
            var height = tx.height == 0 ? 'N/A' : tx.height;
	    var explorer_url = turtlecoin_explorer_url+'/transaction.html?hash='+tx.txid;
            var row = ''+
                '<tr>'+
                '<td style="word-break: break-all">'+
                '<a href="'+explorer_url+'" target="_blank">'+tx.txid+'</a>'+
                '</td>'+
                '<td>'+height+'</td>'+
                '<td>'+tx.amount_formatted+' TRTL</td>'+
                '</tr>';

            jQuery('#turtlecoin_tx_table tbody').append(row);
        }
    } else {
        jQuery('#turtlecoin_tx_table').hide();
        jQuery('#turtlecoin_tx_none').show();
    }

    // Show state change notifications
    var new_txs = details.txs;
    var old_txs = turtlecoin_order_state.txs;
    if(new_txs.length != old_txs.length) {
        for(var i = 0; i < new_txs.length; i++) {
            var is_new_tx = true;
            for(var j = 0; j < old_txs.length; j++) {
                if(new_txs[i].txid == old_txs[j].txid && new_txs[i].amount == old_txs[j].amount) {
                    is_new_tx = false;
                    break;
                }
            }
            if(is_new_tx) {
                turtlecoin_showNotification('Transaction received for '+new_txs[i].amount_formatted+' TRTL');
            }
        }
    }

    if(details.status != turtlecoin_order_state.status) {
        switch(details.status) {
            case 'paid':
                turtlecoin_showNotification('Your order has been paid in full');
                break;
            case 'confirmed':
                turtlecoin_showNotification('Your order has been confirmed');
                break;
            case 'expired':
            case 'expired_partial':
                turtlecoin_showNotification('Your order has expired', 'error');
                break;
        }
    }

    turtlecoin_order_state = {
        status: turtlecoin_details.status,
        txs: turtlecoin_details.txs
    };

}
jQuery(document).ready(function($) {
    if (typeof turtlecoin_details !== 'undefined') {
        turtlecoin_order_state = {
            status: turtlecoin_details.status,
            txs: turtlecoin_details.txs
        };
        setInterval(turtlecoin_fetchDetails, 30000);
        turtlecoin_updateDetails();
        new ClipboardJS('.clipboard').on('success', function(e) {
            e.clearSelection();
            if(e.trigger.disabled) return;
            switch(e.trigger.getAttribute('data-clipboard-target')) {
                case '#turtlecoin_integrated_address':
                    turtlecoin_showNotification('Copied destination address!');
                    break;
                case '#turtlecoin_total_due':
                    turtlecoin_showNotification('Copied total amount due!');
                    break;
            }
            e.clearSelection();
        });
    }
});
