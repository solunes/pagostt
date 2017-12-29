<?php

namespace Solunes\Pagostt\App;

use Illuminate\Database\Eloquent\Model;

class PttTransactionPayment extends Model {
	
	protected $table = 'ptt_transaction_payments';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'parent_id'=>'required',
		'payment_id'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'parent_id'=>'required',
		'payment_id'=>'required',
	);
    
    public function ptt_transaction() {
        return $this->belongsTo('Solunes\Pagostt\App\PttTransaction', 'parent_id');
    }

}