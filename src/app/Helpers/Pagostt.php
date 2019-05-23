<?php 

namespace Solunes\Pagostt\App\Helpers;

use Validator;

class Pagostt {

    public static function generateAppKey() {
        $token = \Pagostt::generateToken([8,4,4,4,12]);
        return $token;
    }

    public static function putInoviceParameters($ptt_transaction) {
        $save = false;
        if(request()->has('nit_company')){
            $ptt_transaction->nit_company = urldecode(request()->input('nit_company'));
            $save = true;
        }
        if(request()->has('invoice_number')){
            $ptt_transaction->invoice_number = request()->input('invoice_number');
            $save = true;
        }
        if(request()->has('auth_number')){
            $ptt_transaction->auth_number = request()->input('auth_number');
            $save = true;
        }
        if(request()->has('control_code')){
            $ptt_transaction->control_code = urldecode(request()->input('control_code'));
            $save = true;
        }
        if(request()->has('customer_name')){
            $ptt_transaction->customer_name = urldecode(request()->input('customer_name'));
            $save = true;
        }
        if(request()->has('customer_nit')){
            $ptt_transaction->customer_nit = request()->input('customer_nit');
            $save = true;
        }
        if(request()->has('invoice_type')){
            $ptt_transaction->invoice_type = request()->input('invoice_type');
            $save = true;
        }
        if(request()->has('invoice_id')){
            $ptt_transaction->invoice_id = request()->input('invoice_id');
            $save = true;
        }
        if(config('pagostt.enable_cycle')&&$ptt_transaction->invoice_type=='C'){
            if(request()->has('billing_cycle_dosage')){
                $dosage_decrypt = \Pagostt::pagosttDecrypt(request()->input('billing_cycle_dosage'));
                $ptt_transaction->billing_cycle_dosage = $dosage_decrypt;
                $save = true;
            }
            if(request()->has('billing_cycle_start_date')){
                $ptt_transaction->billing_cycle_start_date = request()->input('billing_cycle_start_date');
                $save = true;
            }
            if(request()->has('billing_cycle_end_date')){
                $ptt_transaction->billing_cycle_end_date = request()->input('billing_cycle_end_date');
                $save = true;
            }
            if(request()->has('billing_cycle_eticket')){
                $ptt_transaction->billing_cycle_eticket = request()->input('billing_cycle_eticket');
                $save = true;
            }
            if(request()->has('billing_cycle_legend')){
                $ptt_transaction->billing_cycle_legend = request()->input('billing_cycle_legend');
                $save = true;
            }
            if(request()->has('billing_cycle_parallel')){
                $ptt_transaction->billing_cycle_parallel = request()->input('billing_cycle_parallel');
                $save = true;
            }
            if(request()->has('billing_cycle_invoice_title')){
                $ptt_transaction->billing_cycle_invoice_title = request()->input('billing_cycle_invoice_title');
                $save = true;
            }
            if(request()->has('company_code')){
                $ptt_transaction->company_code = request()->input('company_code');
                $save = true;
            }
        }
        return ['ptt_transaction'=>$ptt_transaction,'save'=>$save];
    }

    public static function putInoviceParametersCashier($ptt_transaction, $factura_electronica) {
        $ptt_transaction->nit_company = $factura_electronica->nit;
        $ptt_transaction->invoice_number = $factura_electronica->numero_factura;
        $ptt_transaction->auth_number = $factura_electronica->numero_autorizacion;
        $ptt_transaction->control_code = $factura_electronica->codigo_control;
        $ptt_transaction->customer_name = $factura_electronica->cliente_razon_social;
        $ptt_transaction->customer_nit = $factura_electronica->cliente_nit;
        $ptt_transaction->invoice_type = $factura_electronica->tipo_dosificacion;
        $ptt_transaction->invoice_id = $factura_electronica->identificador;
        if(config('pagostt.enable_cycle')&&$ptt_transaction->invoice_type=='C'){
            // TODO REVISAR
            if($factura_electronica->dosificacion){
                $dosage_decrypt = \Pagostt::pagosttDecrypt($factura_electronica->dosificacion);
                $ptt_transaction->billing_cycle_dosage = $dosage_decrypt;
            }
            $ptt_transaction->billing_cycle_start_date = $factura_electronica->fecha_inicio_ciclo;
            $ptt_transaction->billing_cycle_end_date = $factura_electronica->fecha_final_ciclo;
            $ptt_transaction->billing_cycle_eticket = $factura_electronica->eticket_dosificacion;
            $ptt_transaction->billing_cycle_legend = $factura_electronica->leyenda_dosificacion;
            $ptt_transaction->billing_cycle_parallel = $factura_electronica->paralela_dosificacion;
        }
        return $ptt_transaction;
    }

    public static function transformCurrency($amount, $currency_exchange) {
        $amount = $amount * $currency_exchange;
        return $amount;
    }

    public static function generatePaymentItem($concept, $quantity, $cost, $invoice = true) {
        $item = [];
        $item['concepto'] = $concept;
        $item['cantidad'] = $quantity;
        $item['costo_unitario'] = $cost;
        if($invoice==false){
            $item['ignorar_factura'] = true;
        }
        $encoded_item = json_encode($item);
        return $encoded_item;
    }

    public static function generatePaymentMetadata($nombre, $dato) {
        $item = [];
        $item['nombre'] = $nombre;
        $item['dato'] = $dato;
        $encoded_item = json_encode($item);
        return $encoded_item;
    }

    public static function generatePaymentTransaction($customer_id, $payment_ids, $amount = NULL) {
        $payment_code = \Pagostt::generatePaymentCode();
        $pagostt_transaction = new \Solunes\Pagostt\App\PttTransaction;
        $pagostt_transaction->customer_id = $customer_id;
        $pagostt_transaction->payment_code = $payment_code;
        $pagostt_transaction->amount = $amount;
        $pagostt_transaction->status = 'holding';
        $pagostt_transaction->save();
        foreach($payment_ids as $payment_id){
            $pagostt_payment = new \Solunes\Pagostt\App\PttTransactionPayment;
            $pagostt_payment->parent_id = $pagostt_transaction->id;
            $pagostt_payment->payment_id = $payment_id;
            $pagostt_payment->save();
        }
        return $pagostt_transaction;
    }

    public static function generatePaymentCode() {
        $token = \Pagostt::generateToken([8,4,4,4,12]);
        if(\Solunes\Pagostt\App\PttTransaction::where('payment_code', $token)->first()){
            $token = \Pagostt::generatePaymentCode();
        }
        return $token;
    }

    public static function generateToken($array) {
        $full_token = '';
        foreach($array as $key => $lenght){
            $token = bin2hex(openssl_random_pseudo_bytes($lenght/2));
            if($key!=0){
                $full_token .= '-';
            }
            $full_token .= $token;
        }
        return $full_token;
    }

    public static function generateTransactionArray($customer, $payment, $pagostt_transaction, $custom_app_key = NULL) {
        $callback_url = \Pagostt::generatePaymentCallback($pagostt_transaction->payment_code);
        $app_key = \Pagostt::getAppKey(NULL, $custom_app_key);
        if(config('pagostt.finish_payment_verification')){
            $payment = \PagosttBridge::finishPaymentVerification($payment, $pagostt_transaction);
        }
        $final_fields = array(
            "appkey" => $app_key,
            "email_cliente" => $customer['email'],
            "callback_url" => $callback_url,
            "razon_social" => $customer['nit_name'],
            "nit" => $customer['nit_number'],
        );
        if(isset($customer['ci_number'])){
            $final_fields['ci'] = $customer['ci_number'];
        }
        if(isset($customer['first_name'])){
            $final_fields['nombre_cliente'] = $customer['first_name'];
        }
        if(isset($customer['last_name'])){
            $final_fields['apellido_cliente'] = $customer['last_name'];
        }
        if(isset($payment['has_invoice'])){
            $final_fields['emite_factura'] = $payment['has_invoice'];
        }
        if(isset($payment['shipping_amount'])){
            $final_fields['valor_envio'] = $payment['shipping_amount'];
        } else {
            $final_fields['valor_envio'] = 0;
        }
        if(isset($payment['shipping_detail'])){
            $final_fields['descripcion_envio'] = $payment['shipping_detail'];
        } else {
            $final_fields['descripcion_envio'] = "Costo de envío no definido.";
        }
        $final_fields['descripcion'] = $payment['name'];
        if(isset($payment['preinvoices'])){
            $final_fields['prefacturas'] = $payment['preinvoices'];
        } else {
            $final_fields['lineas_detalle_deuda'] = $payment['items'];
        }
        // Habilitar Pago en Caja
        if(config('pagostt.enable_cashier')&&isset($payment['canal_caja'])&&$payment['canal_caja']==true){
            $cashierKey = \Pagostt::getCashierKey();
            if($cashierKey&&isset($payment['canal_caja_sucursal'])&&isset($payment['canal_caja_usuario'])){
                $final_fields['canal_caja'] = $cashierKey;
                $final_fields['canal_caja_sucursal'] = $payment['canal_caja_sucursal'];
                $final_fields['canal_caja_usuario'] = $payment['canal_caja_usuario'];
            }
        }
        // Definir Metadata
        if(isset($payment['metadata'])){
            $final_fields['lineas_metadatos'] = $payment['metadata'];
        }
        return $final_fields;
    }

    public static function generateTransactionQuery($pagostt_payment, $final_fields) {
        $url = \Pagostt::queryTransactiontUrl('deuda/registrar');
        $decoded_result = \Pagostt::queryCurlTransaction($url, $final_fields);
        
        if(!isset($decoded_result->url_pasarela_pagos)){
            if($decoded_result->error==0&&isset($decoded_result->id_transaccion)){
                \Log::info('Iniciando Pago en Caja: '.json_encode($decoded_result));
                if(isset($decoded_result->facturas_electronicas)){
                    foreach($decoded_result->facturas_electronicas as $factura_electronica){
                        $pagostt_payment = \Pagostt::putInoviceParametersCashier($pagostt_payment, $factura_electronica);
                    }
                }
                $pagostt_payment->transaction_id = $decoded_result->id_transaccion;
                $pagostt_payment->status = 'paid';
                $pagostt_payment->save();
                $pagostt_payment->load('ptt_transaction_payments');
                if(config('pagostt.enable_bridge')){
                    $payment_registered = \PagosttBridge::transactionSuccesful($pagostt_payment);
                } else {
                    $payment_registered = \Customer::transactionSuccesful($pagostt_payment);
                }
                \Log::info('Pago en Caja Generado: '.json_encode($payment_registered));
                return 'success-cashier';
            } else {
                \Log::info('Error en PagosTT Deuda: '.json_encode($decoded_result));
                return NULL;
            }
        } else {
            \Log::info('Success en PagosTT Deuda: '.json_encode($decoded_result));
        }

        // Guardado de transaction_id generado por PagosTT
        $pagostt_payment->transaction_id = $decoded_result->id_transaccion;
        $pagostt_payment->save();
        
        // URL para redireccionar
        $api_url = $decoded_result->url_pasarela_pagos;
        return $api_url;
    }

    public static function calculateMultiplePayments($payments_array, $amount = 0) {
        $total_amount = 0;
        $payment_ids = [];
        $items = [];
        foreach($payments_array as $payment_id => $pending_payment){
            $total_amount += $pending_payment['amount'];
            $payment_ids[] = $payment_id;
            foreach($pending_payment['items'] as $single_payment){
                $items[] = $single_payment;
            }
        }
        return ['items'=>$items, 'payment_ids'=>$payment_ids, 'total_amount'=>$amount];
    }

    public static function encrypt($plainTextToEncrypt) {
        $secret_key = config('pagostt.salt');
        $secret_iv = config('pagostt.secret_iv');
          
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
     
        $output = base64_encode( openssl_encrypt( $plainTextToEncrypt, $encrypt_method, $key, 0, $iv ) );
        return $output;
    }
    
    public static function decrypt($textToDecrypt) {
        $secret_key = config('pagostt.salt');
        $secret_iv = config('pagostt.secret_iv');
     
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
     
        $output = openssl_decrypt( base64_decode( $textToDecrypt ), $encrypt_method, $key, 0, $iv );
        return $output;
    }

    public static function pagosttEncrypt($plainTextToEncrypt) {
        $key = config('pagostt.secret_pagostt_iv');
        $ivArray=array( 0x12, 0x34, 0x56, 0x78, 0x90, 0xAB, 0xCD, 0xEF  );
        $iv=null;
        $block = mcrypt_get_block_size('des', 'cbc');
        $pad = $block - (strlen($plainTextToEncrypt) % $block);
        $plainTextToEncrypt .= str_repeat(chr($pad), $pad);
        foreach ($ivArray as $element){
            $iv.=CHR($element);
        }
        $encrypted_string = mcrypt_encrypt(MCRYPT_DES, $key, $plainTextToEncrypt, MCRYPT_MODE_CBC, $iv);
        return strtr(base64_encode($encrypted_string), '+/=', '._-');
    }

    public static function pagosttDecrypt($plainTextToDecrypt) {
        $key = config('pagostt.secret_pagostt_iv');
        $ivArray=array( 0x12, 0x34, 0x56, 0x78, 0x90, 0xAB, 0xCD, 0xEF  );
        $iv=null;
        foreach ($ivArray as $element){
            $iv.=CHR($element);
        }
        $plainTextToDecrypt = base64_decode(strtr($plainTextToDecrypt, '._-', '+/='));
        $decrypted_string = mcrypt_decrypt(MCRYPT_DES, $key, $plainTextToDecrypt, MCRYPT_MODE_CBC, $iv);
        $block = mcrypt_get_block_size('des', 'cbc');
        $pad = ord($decrypted_string[strlen($decrypted_string)-1]);
        $response = substr($decrypted_string, 0, strlen($decrypted_string) - $pad);
        return $response;
    }

    public static function generatePaymentCallback($payment_code, $transaction_id = NULL) {
        $url = url('api/pago-confirmado/'.$payment_code);
        if($transaction_id){
            $url .= '/'.$transaction_id.'?transaction_id='.$transaction_id;
        }
        return $url;
    }

    public static function queryTransactiontUrl($action) {
        if(config('pagostt.testing')){
            $url = config('pagostt.test_server');
        } else {
            $url = config('pagostt.main_server');
        }
        $url .= $action;
        return $url;
    }

    public static function getAppKey($appkey = NULL, $custom_key = NULL) {
        if(!$appkey){
            if($custom_key){
                if(config('pagostt.testing')==true&&config('pagostt.custom_test_app_keys.'.$custom_key)){
                    $appkey = config('pagostt.custom_test_app_keys.'.$custom_key);
                } else if(config('pagostt.testing')==false&&config('pagostt.custom_app_keys.'.$custom_key)) {
                    $appkey = config('pagostt.custom_app_keys.'.$custom_key);
                }
            }
            if(!$appkey){
                if(config('pagostt.testing')){
                    $appkey = config('pagostt.test_app_key');
                } else {
                    $appkey = config('pagostt.app_key');
                }
            }
        }
        return $appkey;
    }

    public static function getCashierKey($appkey = NULL, $custom_key = 'default') {
        if(!$appkey){
            if(config('pagostt.testing')==true&&config('pagostt.test_cashier_payments.'.$custom_key)){
                $appkey = config('pagostt.test_cashier_payments.'.$custom_key);
            } else if(config('pagostt.testing')==false&&config('pagostt.cashier_payments.'.$custom_key)) {
                $appkey = config('pagostt.cashier_payments.'.$custom_key);
            }
        }
        return $appkey;
    }

    public static function queryCurlTransaction($url, $final_fields) {
        $ch = curl_init();
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($final_fields),
            CURLOPT_RETURNTRANSFER => true,
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);  
        \Log::info(json_encode($url));

        // Decodificar resultado
        $decoded_result = json_decode($result);
        return $decoded_result;
    }

    public static function generateTestingPayment() {
        $customer = ['email'=>'edumejia30@gmail.com','nit_name'=>'Mejia','nit_number'=>'4768578017','ci_number'=>'4768578','first_name'=>'Eduardo','first_name'=>'Eduardo','last_name'=>'Mejia'];
        $payment_lines = [\Pagostt::generatePaymentItem('Pago por muestra 1', 1, 100), \Pagostt::generatePaymentItem('Pago por muestra 2', 1, 100)];
        $payment = ['has_invoice'=>1,'name'=>'Pago de muestra 1','items'=>$payment_lines];
        $pagostt_transaction = \Pagostt::generatePaymentTransaction(1, [1], 200);
        $final_fields = \Pagostt::generateTransactionArray($customer, $payment, $pagostt_transaction);
        $api_url = \Pagostt::generateTransactionQuery($pagostt_transaction, $final_fields);
        return $api_url;
    }

    // Generar Pre Facturas - payments_array { }
    public static function generatePreInvoices($payments_array, $appkey = NULL) {
        if(!config('pagostt.enable_preinvoice')||count($payments_array)==0){
            return false;
        }
        $appkey = \Pagostt::getAppKey($appkey);
        $final_fields = [];
        $count = 0;
        $invoice_batch = time().'_'.rand(100000,900000);
        \Log::info(json_encode($payments_array));
        foreach($payments_array as $payment_item){
            if(isset($payment_item['key_name'])){
                $key_name = $payment_item['key_name'];
            } else {
                $count++;
                $key_name = 'deuda_'.$count;
            }
            $final_fields[] = \Pagostt::generatePreInovicesItem($payment_item, $invoice_batch, $key_name, $appkey);
        }

        $url = \Pagostt::queryTransactiontUrl('prefacturas/registrar');
        //\Log::info('Test en PagosTT: '.json_encode($final_fields));
        $decoded_result = \Pagostt::queryCurlTransaction($url, $final_fields);
        
        if(!$decoded_result||$decoded_result->error==1){
            \Log::info('Error en PagosTT Prefactura: '.json_encode($decoded_result->mensaje));
            return NULL;
        } else if($decoded_result->id_transaccion) {
            \Log::info('Success en PagosTT Prefactura: '.json_encode($decoded_result));
        }

        $correct_count = 0;
        $preinvoice_array = [];
        $preinvoice_errors = [];
        foreach($decoded_result->datos as $payment_response){
            if($preinvoice = \Solunes\Pagostt\App\Preinvoice::where('invoice_batch', $invoice_batch)->where('return_code', $payment_response->identificador_retorno)->first()){
                if($payment_response->error_generacion==false){
                    $correct_count++;
                    $transaction_id = $decoded_result->id_transaccion;
                    $preinvoice->pagostt_iterator = $payment_response->identificador_iteracion;
                    $preinvoice->pagostt_code = $payment_response->identificador_prefactura;
                    $preinvoice->pagostt_url = $payment_response->url;
                    $preinvoice_array[$payment_response->identificador_retorno] = ['code'=>$payment_response->identificador_prefactura,'url'=>$payment_response->url];
                } else {
                    $preinvoice->pagostt_error = 1;
                    $preinvoice->pagostt_message = $payment_response->mensaje;
                    $preinvoice_errors[$payment_response->identificador_retorno] = $payment_response->mensaje;
                }
                $preinvoice->save();
            } else {
                \Log::info('Preinvoice no encontrado luego de success');
            }
        }
        return ['success'=>true, 'invoice_batch'=>$invoice_batch, 'count'=>$correct_count, 'preinvoice_array'=>$preinvoice_array, 'preinvoice_errors'=>$preinvoice_errors];
    }

    public static function generatePreInovicesItem($payment_item, $invoice_batch, $key_name, $app_key) {
        $preinvoice = new \Solunes\Pagostt\App\Preinvoice;
        $preinvoice->payment_id = $payment_item['id'];
        $preinvoice->invoice_batch = $invoice_batch;
        $preinvoice->nit_name = $payment_item['nit_name'];
        $preinvoice->nit_number = $payment_item['nit_number'];
        $preinvoice->return_code = $key_name;
        $preinvoice->save();
        $final_fields = array(
            "appkey" => $app_key,
            "identificador_retorno" => $key_name,
            "razon_social" => $payment_item['nit_name'],
            "nit" => $payment_item['nit_number'],
            "lineas_detalle_factura" => $payment_item['detalle'],
        );
        foreach($payment_item['detalle'] as $detalle){
            $preinvoice_item = new \Solunes\Pagostt\App\PreinvoiceItem;
            $preinvoice_item->parent_id = $preinvoice->id;
            $preinvoice_item->name = $detalle['concepto'];
            if(isset($detalle['detalle'])){
                $preinvoice_item->detail = $detalle['detalle'];
            } else {
                $preinvoice_item->name = $detalle['concepto'];
            }
            if(isset($detalle['codigo_producto'])){
                $preinvoice_item->product_code = $detalle['codigo_producto'];
            } else {
                $preinvoice_item->product_code = $payment_item['id'];
            }
            $preinvoice_item->quantity = $detalle['cantidad'];
            $preinvoice_item->price = $detalle['costo_unitario'];
            $preinvoice_item->amount = round(floatval($preinvoice_item->quantity) * floatval($preinvoice_item->price), 2);
            $preinvoice_item->save();
        }
        return $final_fields;
    }

    public static function sendCustomerTo($url, $customer) {
        $url .= '/api/customer/new';
        
        $final_fields = [];
        $final_fields['app_key'] = config('pagostt.app_key');
        $final_fields['email'] = $customer->email;
        $final_fields['first_name'] = $customer->first_name;
        $final_fields['last_name'] = $customer->last_name;
        $final_fields['ci_number'] = $customer->ci_number;
        $final_fields['ci_expedition'] = $customer->ci_expedition;
        $final_fields['member_code'] = $customer->member_code;
        $final_fields['phone'] = $customer->phone;
        $final_fields['address'] = $customer->address;
        $final_fields['nit_number'] = $customer->nit_number;
        $final_fields['nit_name'] = $customer->nit_name;
        $final_fields['birth_date'] = $customer->birth_date;

        $ch = curl_init();
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $final_fields,
            CURLOPT_RETURNTRANSFER => true,
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

}