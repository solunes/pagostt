<?php

namespace Solunes\Pagostt\App\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use Validator;
use Asset;
use AdminList;
use AdminItem;
use PDF;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ProcessController extends Controller {

	protected $request;
	protected $url;

	public function __construct(UrlGenerator $url) {
	  $this->prev = $url->previous();
	}

    public function getMakeAllPayments($customer_id) {
    	$customer = \PagosttBridge::getCustomer($customer_id, true, false);
	    if($customer){
	      $pagostt_transaction = \Pagostt::generatePaymentTransaction($customer_id, $customer['payment_ids'], $customer['amount']);
	      $final_fields = \Pagostt::generateTransactionArray($customer, NULL, $pagostt_transaction);
	      $api_url = \Pagostt::generateTransactionQuery($pagostt_transaction, $final_fields);
	      return redirect($api_url);
	    } else {
	      return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago.');
	    }
    }

    public function getMakeSinglePayment($customer_id, $payment_id) {
    	$customer = \PagosttBridge::getCustomer($customer_id, false, false);
    	$payment = \PagosttBridge::getPayment($payment_id);
	    if($customer&&$payment){
	      $pagostt_transaction = \Pagostt::generatePaymentTransaction($customer_id, [$payment_id], $customer['amount']);
	      $final_fields = \Pagostt::generateTransactionArray($customer, $payment, $pagostt_transaction);
	      $api_url = \Pagostt::generateTransactionQuery($pagostt_transaction, $final_fields);
	      return redirect($api_url);
	    } else {
	      return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago.');
	    }
    }

}