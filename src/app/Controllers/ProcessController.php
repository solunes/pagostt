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
    	$customer = \PagosttBridge::get_customer($customer_id, true, false);
	    if($customer){
	      $pagostt_payment = \Pagostt::generatePaymentCode($customer_id, $customer['payment_ids']);
	      $final_fields = \Pagostt::generateTransactionArray($customer, NULL, $pagostt_payment);
	      $api_url = \Pagostt::generateTransactionQuery($pagostt_payment, $final_fields);
	      return redirect($api_url);
	    } else {
	      return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago.');
	    }
    }

    public function getMakeSinglePayment($customer_id, $payment_id) {
    	$customer = \PagosttBridge::get_customer($customer_id, false, false);
    	$payment = \PagosttBridge::get_payment($payment_id);
	    if($customer&&$payment){
	      $pagostt_payment = \Pagostt::generatePaymentCode($customer_id, [$payment_id]);
	      $final_fields = \Pagostt::generateTransactionArray($customer, $payment, $pagostt_payment);
	      $api_url = \Pagostt::generateTransactionQuery($pagostt_payment, $final_fields);
	      return redirect($api_url);
	    } else {
	      return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago.');
	    }
    }

}