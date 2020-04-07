<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use WHMCS\Database\Capsule;

/*********************
 Auto Accept Orders Settings
*********************/
function apexAutoAcceptOrders_settings() {
    return array(
        'autosetup' => true, // determines whether product provisioning is performed
        'sendregistrar' => false, // determines whether domain automation is performed
        'sendemail' => true, // sets if welcome emails for products and registration confirmation emails for domains should be sent 
        'ispaid' => true, // set to true if you want to accept only paid orders
    );
}
/********************/

function apexAutoAcceptOrder($invoiceid, $orderid, $ispaid = false) {
    $settings = apexAutoAcceptOrders_settings();
    
    // If 'ispaid' is set and invoice is not paid don't accept
    if($settings['ispaid'] && !$ispaid) {
        // Check if invoice is paid
        $result = localAPI('GetInvoice', array('invoiceid' => $invoiceid));
        if(!isset($result['result']) || $result['result'] != 'success') {
            logActivity('[Auto Accept] Failed to check paid status - Order ID: ' . $orderid, 0);
            return;
        } else {
            if($result['balance'] > 0) {
                logActivity('[Auto Accept] Order not paid - Order ID: ' . $orderid, 0);
                return;
            }
        }
    }

    // Accept order through API
    $resultAccept = localAPI('AcceptOrder', array(
        'orderid' => $orderid,
        'autosetup' => $settings['autosetup'],
        'sendregistrar' => $settings['sendregistrar'],
        'sendemail' => $settings['sendemail'],
    ));
    
    // Check if order accepted successfully
    if(isset($resultAccept["result"]) && $resultAccept["result"] == "success") {
        logActivity('[Auto Accept] Successfully accepted - Order ID: ' . $orderid, 0);
    } else {
        logActivity('[Auto Accept] Failed to accept! - Order ID: ' . $orderid, 0);
    }
}

function apexOrderPaid_accept($vars)  {
    // Make sure proper variables are passed
    if(!isset($vars['invoiceId']) || !isset($vars['orderId'])) {
        logActivity('[Auto Accept] Variables not set, something went wrong', 0);
        return;
    }

    apexAutoAcceptOrder($vars['invoiceId'], $vars['orderId']);
}
add_hook('OrderPaid', 0, 'apexOrderPaid_accept');

function apexAfterProductUpgrade_accept($vars)  {
    // Make sure proper variables are passed
    if(!isset($vars['upgradeid'])) {
        logActivity('[Auto Accept] Variables not set, something went wrong', 0);
        return;
    }

    logActivity('[Auto Accept] Found upgrade to accept - Upgrade ID: ' . $vars['upgradeid'], 0);

    $orderid = @Capsule::table('tblupgrades')->where('id', $vars['upgradeid'])->value('orderid');

    logActivity('[Auto Accept] Found orderid from upgrade - Order ID: ' . $orderid, 0);

    if($orderid > 0) {
        apexAutoAcceptOrder(0, $orderid, true);
    }
}
add_hook('AfterProductUpgrade', 0, 'apexAfterProductUpgrade_accept');