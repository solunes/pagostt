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
        \Solunes\Pagostt\App\PttTransactionPayment::truncate();
        \Solunes\Pagostt\App\PttTransaction::truncate();
    }
}