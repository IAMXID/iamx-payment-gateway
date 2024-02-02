<?php

use Illuminate\Support\Facades\Route;
use IAMXID\IamxPaymentGateway\Http\Controllers\PaymentGatewayController;

Route::get('/iamx_payment_gateway/checkPayment', [PaymentGatewayController::class, 'checkPayment']);
