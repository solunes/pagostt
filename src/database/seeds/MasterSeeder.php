<?php

namespace Solunes\Pagostt\Database\Seeds;

use Illuminate\Database\Seeder;
use DB;

class MasterSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Módulo General de Empresa ERP
        $node_ptt_transaction = \Solunes\Master\App\Node::create(['name'=>'ptt-transaction', 'location'=>'pagostt', 'folder'=>'payments']);
        \Solunes\Master\App\Node::create(['name'=>'ptt-transaction-payment', 'location'=>'pagostt', 'folder'=>'payments', 'type'=>'subchild', 'parent_id'=>$node_ptt_transaction->id]);
        if(config('pagostt.enable_preinvoice')){
            $node_preinvoice = \Solunes\Master\App\Node::create(['name'=>'preinvoice', 'location'=>'pagostt', 'folder'=>'parameters']);
            \Solunes\Master\App\Node::create(['name'=>'preinvoice-item', 'location'=>'pagostt', 'folder'=>'payments', 'type'=>'child', 'parent_id'=>$node_preinvoice->id]);
        }

        // Usuarios
        $admin = \Solunes\Master\App\Role::where('name', 'admin')->first();
        $member = \Solunes\Master\App\Role::where('name', 'member')->first();
        if(!\Solunes\Master\App\Permission::where('name','payments')->first()){
            $payments_perm = \Solunes\Master\App\Permission::create(['name'=>'payments', 'display_name'=>'Pagos']);
            $admin->permission_role()->attach([$payments_perm->id]);
        }

    }
}