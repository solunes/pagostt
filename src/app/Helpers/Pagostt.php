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
        return ['ptt_transaction'=>$ptt_transaction,'save'=>$save];
    }

    public static function generatePaymentItem($concept, $quantity, $cost, $invoice = true) {
        $item = [];
        $item['concepto'] = $concept;
        $item['cantidad'] = $quantity;
        $item['costo_unitario'] = $cost;
        $item['factura_independiente'] = $invoice;
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
        if($custom_app_key&&config('pagostt.custom_app_keys.'.$custom_app_key)){
            $app_key = config('pagostt.custom_app_keys.'.$custom_app_key);
        } else {
            $app_key = config('pagostt.app_key');
        }
        if(config('pagostt.finish_payment_verification')){
            $payment = \PagosttBridge::finishPaymentVerification($payment);
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
        $final_fields['lineas_detalle_deuda'] = $payment['items'];
        return $final_fields;
    }

    public static function generateTransactionQuery($pagostt_payment, $final_fields) {
        // Consulta CURL a Web Service
        $url = 'http://www.todotix.com:10365/rest/deuda/registrar';
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

        // Decodificar resultado
        $decoded_result = json_decode($result);
        
        if(!isset($decoded_result->url_pasarela_pagos)){
            \Log::info('Error en PagosTT: '.json_encode($decoded_result));
            return NULL;
        }

        // Guardado de transaction_id generado por PagosTT
        $transaction_id = $decoded_result->id_transaccion;
        $pagostt_payment->transaction_id = $transaction_id;
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

    public static function generatePaymentCallback($payment_code, $transaction_id = NULL) {
        $url = url('api/pago-confirmado/'.$payment_code);
        if($transaction_id){
            $url .= '/'.$transaction_id.'?transaction_id='.$transaction_id;
        }
        return $url;
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