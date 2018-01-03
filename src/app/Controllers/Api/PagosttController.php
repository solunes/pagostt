<?php

namespace Solunes\Pagostt\App\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller\Api;

class PagosttController extends BaseController {

    public function getCustomerPayments($app_key, $customer_id, $transaction_id = NULL){
        if($app_key==config('pagostt.app_key')){
            $customer = \PagosttBridge::getCustomer($customer_id, true, true);
            if($customer&&is_array($customer)){
                $pending_payments = $customer['pending_payments'];
                $final_pending_payments = [];
                foreach($pending_payments as $payment_id => $pending_payment){
                    $final_pending_payments[$payment_id] = $pending_payment;
                    foreach($pending_payment['items'] as $key => $item){
                        $new_item = json_decode($item, true);
                        $pagostt_transaction = \Pagostt::generatePaymentTransaction($customer['id'], [$payment_id], $customer['amount']);
                        if($transaction_id){
                            $pagostt_transaction->transaction_id = $transaction_id;
                            $pagostt_transaction->save();
                        }
                        $callback_url = \Pagostt::generatePaymentCallback($pagostt_transaction->payment_code);
                        $new_item['appkey_empresa_final'] = $app_key;
                        $new_item['call_back_url'] = $callback_url;
                        $new_item = json_encode($new_item);
                        $new_item = \Pagostt::encrypt($new_item);
                        $final_pending_payments[$payment_id]['items'][$key] = urlencode($new_item);
                    }
                }
                return $this->response->array(['enabled'=>config('pagostt.customer_recurrent_payments'), 'app_key'=>$app_key, 'app_name'=>config('pagostt.app_name'), 'codigo_cliente'=>$customer_id, 'transaction_id'=>$transaction_id, 'pagos_pendientes'=>$final_pending_payments])->setStatusCode(200);
            } else {
                throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException('El cliente introducido no se encuentra.');
            }
        } else {
            throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException('El token no fue autorizado.');
        } 
    }

    public function getSuccessfulPayment($payment_code, $transaction_id = NULL){
        \Log::info('PaymentCode'.$payment_code);
        \Log::info('transaction_id'.$transaction_id);
        \Log::info('input'.request()->input('transaction_id');
        if($payment_code&&request()->has('transaction_id')){
            if($transaction_id&&$ptt_transaction = \Solunes\Pagostt\App\PttTransaction::where('payment_code',$payment_code)->where('transaction_id',$transaction_id)->where('status','holding')->first()){
            
            } else if($ptt_transaction = \Solunes\Pagostt\App\PttTransaction::where('payment_code',$payment_code)->where('transaction_id',request()->input('transaction_id'))->where('status','holding')->first()){

            } else {
                throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException('Pago no encontrado en verificación.');
            }
            $ptt_transaction->status = 'confirmed';
            $ptt_transaction->save();
            $payment_registered = \PagosttBridge::transactionSuccesful($ptt_transaction);
            if(config('pagostt.notify_email')){
                $customer = \PagosttBridge::getCustomer($ptt_transaction->customer_id);
                \Mail::send('pagostt::emails.successful-payment', ['amount'=>$ptt_transaction->amount, 'email'=>$customer['email']], function($m) use($customer) {
                    if($customer['name']){
                        $name = $customer['name'];
                    } else {
                        $name = 'Cliente';
                    }
                    $m->to($customer['email'], $name)->subject(config('solunes.app_name').' | '.trans('pagostt::mail.successful_payment_title'));
                });
            }
            return redirect('');
            return $this->response->array(['payment_registered'=>$payment_registered])->setStatusCode(200);
        } else {
            throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException('Operación no permitida.');
        }
    }

}