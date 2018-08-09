<?php

namespace Solunes\Pagostt\App;

use Illuminate\Database\Eloquent\Model;

class PreinvoiceItem extends Model {
	
	protected $table = 'preinvoice_items';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'parent_id'=>'required',
		'detail'=>'required',
		'amount'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'parent_id'=>'required',
		'detail'=>'required',
		'amount'=>'required',
	);
    
    public function parent() {
        return $this->belongsTo('Solunes\PagosTT\App\Preinvoice');
    }
    
    public function preinvoice() {
        return $this->belongsTo('Solunes\Pagostt\App\Preinvoice', 'parent_id');
    }
 
}