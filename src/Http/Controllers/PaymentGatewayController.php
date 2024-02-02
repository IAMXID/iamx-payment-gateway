<?php

namespace IAMXID\IamxPaymentGateway\Http\Controllers;

use IAMXID\IamxPaymentGateway\Traits\UsePaymentGateway;
use Illuminate\Http\Request;


class PaymentGatewayController extends Controller
{

    use UsePaymentGateway;

    public function checkPayment(Request $request) {
        $paymentUUID = $request->uuid;

        $userpayment = $this->checkForPayment($paymentUUID);

        if (!$userpayment) {
            return array('status' => 'UUID or payment confirmation not found in the database.');
        } else {
            return array('status' => 'Payment found');
        }
    }
}