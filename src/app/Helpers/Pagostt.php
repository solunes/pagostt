<?php 

namespace Solunes\Pagostt\App\Helpers;

use Validator;

class Pagostt {

    public static function generateAppKey() {
        $token = \Pagostt::generateToken([8,4,4,4,12]);
        return $token;
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

    public static function generatePaymentTransaction($customer_id, $payment_id, $amount = NULL) {
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

    public static function generateTransactionArray($customer, $payment, $pagostt_transaction) {
        $callback_url = \Pagostt::generatePaymentCallback($pagostt_transaction->payment_code);
        $final_fields = array(
            "appkey" => config('pagostt.app_key'),
            "email_cliente" => $customer['email'],
            "callback_url" => $callback_url,
            "razon_social" => $customer['nit_name'],
            "nit" => $customer['nit_number'],
            "valor_envio" => 0,
            "descripcion_envio" => "Sin costo de envío",
        );
        if($payment){
            $final_fields['descripcion'] = $payment['name'];
            $final_fields['lineas_detalle_deuda'] = $payment['items'];
        } else {
            $final_fields['descripcion'] = 'Múltiples Pagos';
            $final_fields['lineas_detalle_deuda'] = $customer['pending_payments'];
        }
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
        
        // Guardado de transaction_id generado por PagosTT
        $transaction_id = $decoded_result->id_transaccion;
        $pagostt_payment->transaction_id = $transaction_id;
        $pagostt_payment->save();
        
        // URL para redireccionar
        $api_url = $decoded_result->url_pasarela_pagos;
        return $api_url;
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

    public static function generatePaymentCallback($payment_code) {
        return url('api/pago-confirmado/'.$payment_code);
    }

}