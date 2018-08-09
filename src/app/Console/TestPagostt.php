<?php

namespace Solunes\Pagostt\App\Console;

use Illuminate\Console\Command;

class TestPagostt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-pagostt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revisa el sistema de PagosTT integrado.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){       
        $this->info('Comenzando la prueba, iniciando modo de pruebas.');
        // Prueba de Deuda
        $this->info('Comenzando la prueba de generacion de deudas.');
        $customer = ['email'=>'edumejia30@gmail.com','nit_name'=>'Mejia','nit_number'=>'4768578017','ci_number'=>'4768578','first_name'=>'Eduardo','first_name'=>'Eduardo','last_name'=>'Mejia'];
        $payment_lines = [\Pagostt::generatePaymentItem('Pago por muestra 1', 1, 100), \Pagostt::generatePaymentItem('Pago por muestra 2', 1, 100)];
        $payment = ['has_invoice'=>1,'name'=>'Pago de muestra 1','items'=>$payment_lines];
        $pagostt_transaction = \Pagostt::generatePaymentTransaction(1, [1], 200);
        $final_fields = \Pagostt::generateTransactionArray($customer, $payment, $pagostt_transaction);
        $api_url = \Pagostt::generateTransactionQuery($pagostt_transaction, $final_fields);
        $this->info('Respuesta de Generar Deuda: '.$api_url);
        // Prueba de Prefactura
        $this->info('Comenzando la prueba de prefacturas.');
        $payments_array = [];
        $payments_array[] = ['nit_name'=>'Mejia', 'nit_number'=>'4768578017', 'detalle'=>[['concepto'=>'Pago por muestra 1', 'codigo_producto'=>'Producto 1', 'cantidad'=>'1', 'costo_unitario'=>'100'],['concepto'=>'Pago por muestra 2', 'codigo_producto'=>'Producto 2', 'cantidad'=>'2', 'costo_unitario'=>'200']]];
        $payments_array[] = ['nit_name'=>'Mejia', 'nit_number'=>'4768578017', 'detalle'=>[['concepto'=>'Pago por muestra 3', 'codigo_producto'=>'Producto 3', 'cantidad'=>'2', 'costo_unitario'=>'500']]];
        $response = \Pagostt::generatePreInovices($payments_array);
        $this->info('Respuesta de Prefactura: '.$response);
    }
}
