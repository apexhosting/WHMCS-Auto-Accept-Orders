<?php
/*
*
* Auto Accept Orders
* Created By Idan Ben-Ezra
*
* Copyrights @ Jetserver Web Hosting
* www.jetserver.net
*
* Hook version 1.0.1
*
**/
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

/*********************
 Auto Accept Orders Settings
*********************/
function jetserverAutoAcceptOrders_settings() {
    return array(
        'autosetup' => true, // determines whether product provisioning is performed
        'sendregistrar' => false, // determines whether domain automation is performed
        'sendemail' => true, // sets if welcome emails for products and registration confirmation emails for domains should be sent 
        'ispaid' => true, // set to true if you want to accept only paid orders
    );
}
/********************/

function jetserverAutoAcceptOrders_accept($vars)  {
    $settings = jetserverAutoAcceptOrders_settings();
    $ispaid = false;

    // Make sure proper variables are passed
    if(!isset($vars['invoiceId']) || !isset($vars['orderId'])) {
        logActivity('[Auto Accept] Variables not set, something went wrong', 0);
        return;
    }

    $invoiceid = $vars['invoiceId'];
    $orderid = $vars['orderId'];

    // Check if invoice is paid
    $result = localAPI('GetInvoice', array('invoiceid' => $invoiceid));
    if(isset($result['result']) && $result['result'] == 'success') {
        $ispaid = ($result['balance'] <= 0) ? true : false;
    } else {
        return;
    }
    
    // If 'ispaid' is set and invoice is not paid don't accept
    if($settings['ispaid'] && !$ispaid) {
        logActivity('[Auto Accept] Order not paid - Order ID: ' . $orderid, 0);
        return;
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
add_hook('OrderPaid', 0, 'jetserverAutoAcceptOrders_accept');
