<?php
/*
Author: Javed Ur Rehman
Website: https://www.allphptricks.com
*/
require_once 'vendor/autoload.php';
require_once "config.php";
require_once('dbclass.php');
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

// Create a unique reference ID of transaction
$ref_id = 'ref_id_' . time();

// Create a MerchantAuthenticationType object with the authentication details
// which are availale in the config.php file
$merchant_authentication = new AnetAPI\MerchantAuthenticationType();
$merchant_authentication->setName(AUTHORIZE_API_LOGIN_ID);
$merchant_authentication->setTransactionKey(AUTHORIZE_TRANSACTION_KEY); 

// Create the payment data object using a credit card details
$credit_card = new AnetAPI\CreditCardType();
$credit_card->setCardNumber($cc_number);
$credit_card->setExpirationDate($cc_exp_year_month);
$credit_card->setCardCode($cvc_code); // Optional

 // Now add the payment data to a PaymentType object
$payment_type = new AnetAPI\PaymentType();
$payment_type->setCreditCard($credit_card);

// Create an order information object (Optional)
$order_info = new AnetAPI\OrderType();
$order_info->setInvoiceNumber("20230001"); // Optional
$order_info->setDescription("Laptop Bag");

// Create a customer's identifying information object (Optional)
$customer_data = new AnetAPI\CustomerDataType(); 
$customer_data->setType("individual"); 
$customer_data->setId("9998659"); // Optional
$customer_data->setEmail("javed@allphptricks.com"); 

 // Create customer's Bill To address object and set data (Optional)
$customer_billing = new AnetAPI\CustomerAddressType();
$customer_billing->setFirstName("Javed Ur");
$customer_billing->setLastName("Rehman");
$customer_billing->setCompany("AllPHPTricks.com");
$customer_billing->setAddress("12 Sunset Street");
$customer_billing->setCity("Karachi");
$customer_billing->setState("Sindh");
$customer_billing->setZip("75080");
$customer_billing->setCountry("Pakistan");
$customer_billing->setPhoneNumber("123456789");

 // Create customer's Ship To address object and set data (Optional)
$customer_shipping = new AnetAPI\CustomerAddressType();
$customer_shipping->setFirstName("Javed Ur");
$customer_shipping->setLastName("Rehman");
$customer_shipping->setAddress("12 Sunset Street");
$customer_shipping->setCity("Karachi");
$customer_shipping->setState("Sindh");
$customer_shipping->setZip("75080");
$customer_shipping->setCountry("Pakistan");

// Create a TransactionRequestType object and set all created objects in it
$transaction_request_type = new AnetAPI\TransactionRequestType();
$transaction_request_type->setTransactionType("authCaptureTransaction");
$transaction_request_type->setAmount($amount);
$transaction_request_type->setPayment($payment_type);
$transaction_request_type->setOrder($order_info);
$transaction_request_type->setCustomer($customer_data);
$transaction_request_type->setBillTo($customer_billing);
$transaction_request_type->setShipTo($customer_shipping);

// Create a complete transaction request
$transaction_request = new AnetAPI\CreateTransactionRequest();
$transaction_request->setMerchantAuthentication($merchant_authentication);
$transaction_request->setRefId($ref_id);
$transaction_request->setTransactionRequest($transaction_request_type);

// Create the controller and get the final response
$controller = new AnetController\CreateTransactionController($transaction_request);
$response = $controller->executeWithApiResponse(constant("\\net\authorize\api\constants\ANetEnvironment::".AUTHORIZE_ENV));

if ($response != null) {
    // Check if the Authorize.net has received the API request successfully
    // and return transaction response back to us
    if ($response->getMessages()->getResultCode() == "Ok") { 
        // Ok means API request was successful
        // Get transaction response and store in DB
        $tresponse = $response->getTransactionResponse(); 
        $responseDesc = array("1"=>"Approved", "2"=>"Declined", "3"=>"Error", "4"=>"Held for Review");

        if ($tresponse != null && $tresponse->getMessages() != null) {
            // Create variables to store transaction information in DB
            $cc_brand = $tresponse->getaccountType();
            $cc_number = $tresponse->getaccountNumber();
            $transaction_id = $tresponse->getTransId(); 
            $auth_code = $tresponse->getAuthCode();
            $response_code = $tresponse->getResponseCode();
            $response_desc = $responseDesc[$response_code];
            $payment_response = $tresponse->getMessages()[0]->getDescription();

            $db = new DB;
            $db->query("INSERT INTO `authorize_payment` (`cc_brand`, `cc_number`, `amount`, `transaction_id`, `auth_code`, `response_code`, `response_desc`, `payment_response`) VALUES (:cc_brand, :cc_number, :amount, :transaction_id, :auth_code, :response_code, :response_desc, :payment_response)");
            $db->bind(":cc_brand", $cc_brand);
            $db->bind(":cc_number", $cc_number);
            $db->bind(":amount", $amount);
            $db->bind(":transaction_id", $transaction_id);
            $db->bind(":auth_code", $auth_code);
            $db->bind(":response_code", $response_code);
            $db->bind(":response_desc", $response_desc);
            $db->bind(":payment_response", $payment_response);
            $db->execute();
            $db->close();
            $status = '<li>Your payment has been received successfully!</li>'; 
            $status .=  "<li>Transaction ID : ".$transaction_id."</li>"; 
            $status .=  "<li>Auth Code : ".$auth_code."</li>"; 
            $response_type = "success";
        } else { 
            $status =  "<li>Transaction has failed!</li>"; 
            if ($tresponse->getErrors() != null) { 
                $status .=  "<li>Error Code : ".$tresponse->getErrors()[0]->getErrorCode()."</li>"; 
                $status .=  "<li>Error Message : ".$tresponse->getErrors()[0]->getErrorText()."</li>"; 
            } 
        } 
    } else {
        // If the Authorize.net API request wasn't successful 
        // then display errors on the screen
        $tresponse = $response->getTransactionResponse(); 
        $status =  "<li>Transaction has failed!</li>"; 
        if ($tresponse != null && $tresponse->getErrors() != null) { 
            $status .=  "<li>Error Code : ".$tresponse->getErrors()[0]->getErrorCode()."</li>"; 
            $status .=  "<li>Error Message : ".$tresponse->getErrors()[0]->getErrorText()."</li>"; 
        } else { 
            $status .=  "<li>Error Code : ".$response->getMessages()->getMessage()[0]->getCode()."</li>"; 
            $status .=  "<li>Error Message : ".$response->getMessages()->getMessage()[0]->getText()."</li>"; 
        }
    } 
} else { 
    $status =  "<li>Transaction has failed! No response returned.</li>"; 
}