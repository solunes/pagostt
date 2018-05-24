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
                        $pagostt_transaction = \Pagostt::generatePaymentTransaction($customer['id'], [$payment_id], $pending_payment['amount']);
                        if($transaction_id){
                            $pagostt_transaction->transaction_id = $transaction_id;
                            $pagostt_transaction->save();
                        }
                        $callback_url = \Pagostt::generatePaymentCallback($pagostt_transaction->payment_code, $transaction_id);
                        $new_item['appkey_empresa_final'] = $app_key;
                        $new_item['call_back_url'] = $callback_url;
                        $new_item = json_encode($new_item, JSON_UNESCAPED_SLASHES);
                        $new_item = \Pagostt::encrypt($new_item);
                        $final_pending_payments[$payment_id]['items'][$key] = urlencode($new_item);
                    }
                }
                return $this->response->array(['enabled'=>config('pagostt.customer_recurrent_payments'), 'app_key'=>$app_key, 'app_name'=>config('pagostt.app_name'), 'codigo_cliente'=>$customer_id, 'transaction_id'=>$transaction_id, 'pagos_pendientes'=>$final_pending_payments])->setStatusCode(200);
            } else {
                return $this->response->array(['enabled'=>config('pagostt.customer_recurrent_payments'), 'app_key'=>$app_key, 'app_name'=>config('pagostt.app_name'), 'codigo_cliente'=>false, 'pagos_pendientes'=>[]])->setStatusCode(200);
            }
        } else {
            throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException('El token no fue autorizado.');
        } 
    }

    public function getSuccessfulPayment($payment_code, $transaction_id = NULL){
        if($payment_code&&request()->has('transaction_id')){
            $api_transaction = false;
            if($transaction_id&&$ptt_transaction = \Solunes\Pagostt\App\PttTransaction::where('payment_code',$payment_code)->where('transaction_id',$transaction_id)->where('status','holding')->first()){
                $api_transaction = true;
            } else if($ptt_transaction = \Solunes\Pagostt\App\PttTransaction::where('payment_code',$payment_code)->where('transaction_id',request()->input('transaction_id'))->where('status','holding')->first()){
                $api_transaction = false;
            } else if($ptt_transaction = \Solunes\Pagostt\App\PttTransaction::where('payment_code',$payment_code)->where('transaction_id',request()->input('transaction_id'))->where('status','paid')->first()){
                $putInoviceParameters = \Pagostt::putInoviceParameters($ptt_transaction);
                $ptt_transaction = $putInoviceParameters['ptt_transaction'];
                if($putInoviceParameters['save']){
                    $ptt_transaction->save();
                }
                return redirect('admin/my-payments')->with('message_success', 'Su pago fue realizado correctamente');
            } else if($ptt_transaction = \Solunes\Pagostt\App\PttTransaction::where('payment_code',$payment_code)->where('transaction_id',request()->input('transaction_id'))->where('status','cancelled')->first()){
                return redirect('admin/my-payments')->with('message_success', 'Su pago fue cancelado. Para más información contáctese con el administrador.');
            } else {
                throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException('Pago no encontrado en verificación.');
            }
            $putInoviceParameters = \Pagostt::putInoviceParameters($ptt_transaction);
            $ptt_transaction = $putInoviceParameters['ptt_transaction'];
            $ptt_transaction->status = 'paid';
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
            if($api_transaction){
                return $this->response->array(['payment_registered'=>$payment_registered])->setStatusCode(200);
            } else {
                return redirect('');
            }
        } else {
            throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException('Operación no permitida.');
        }
    }

}