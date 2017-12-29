<?php

namespace Solunes\Pagostt\App;

use Illuminate\Database\Eloquent\Model;

class PttTransaction extends Model {
	
	protected $table = 'ptt_transactions';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'customer_id'=>'required',
		'payment_code'=>'required',
		'transaction_id'=>'required',
		'status'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'id'=>'required',
		'customer_id'=>'required',
		'payment_code'=>'required',
		'transaction_id'=>'required',
		'status'=>'required',
	);
    
    public function ptt_transaction_payments() {
        return $this->hasMany('Solunes\Pagostt\App\PttTransactionPayment');
    }

}