<?php

use Illuminate\Support\Facades\Route;
use IAMXID\IamxPaymentGateway\Http\Controllers\PaymentGatewayController;

Route::post('/iamx_payment_gateway/setNewPayment', [PaymentGatewayController::class, 'setNewPayment']);
Route::get('/iamx_payment_gateway/checkPayment', [PaymentGatewayController::class, 'checkPayment']);
