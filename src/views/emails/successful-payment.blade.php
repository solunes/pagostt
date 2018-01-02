@extends('master::layouts/email')

@section('icon')
Money
@endsection

@section('content')
	<h2 style="font-family: Arial, Helvetica, sans-serif;margin-top: 16px;margin-bottom: 8px;word-break: break-word;font-size: 28px;line-height: 38px;font-weight: bold;">
		Pago Recibido
	</h2>
	<p style="font-family: Arial, Helvetica, sans-serif;margin-top: 0px;margin-bottom: 32px;word-break: break-word;font-size: 19px;line-height: 31px;">
		{{ trans('pagostt::mail.successful_payment_content') }}
	</p>
@endsection

@section('unsuscribe-email')
	{{ url('auth/unsuscribe/'.urlencode($email)) }}
@endsection