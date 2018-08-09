<?php

namespace Solunes\Pagostt\Database\Seeds;

use Illuminate\Database\Seeder;
use DB;

class TruncateSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(config('pagostt.enable_preinvoice')){
            \Solunes\Pagostt\App\PreinvoiceItem::truncate();
            \Solunes\Pagostt\App\Preinvoice::truncate();
        }
        \Solunes\Pagostt\App\PttTransactionPayment::truncate();
        \Solunes\Pagostt\App\PttTransaction::truncate();
    }
}