<?php

namespace IAMXID\IamxPaymentGateway\Http\Controllers;

use IAMXID\IamxPaymentGateway\Traits\UsePaymentGateway;
use Illuminate\Http\Request;


class PaymentGatewayController extends Controller
{

    use UsePaymentGateway;
    public function setNewPayment(Request $request) {

        $paymentUUID = $request->uuid;
        $walletReceiver = $request->wallet_receiver;
        $walletSender = $request->wallet_sender;
        $after_blockheight = $request->after_blockheight;
        $token_amount = $request->token_amount;
        $token_policy_id = $request->token_policy_id;
        $token_name_hex = $request->token_name_hex;

        $newPayment = $this->setPayment($paymentUUID, $walletReceiver, $walletSender, $after_blockheight, $token_amount, $token_policy_id, $token_name_hex);

        if ($newPayment) {
            return array('status' => 'Payment UUID has been inserted to the database.');
        } else {
            return array('status' => 'Something went wrong while inserting the new row to the database.');
        }

    }

    public function checkPayment(Request $request) {
        $paymentUUID = $request->uuid;

        $userpayment = $this->checkForPayment($paymentUUID);

        if (!$userpayment) {
            return array('status' => 'UUID not found in the database.');
        } else {
            return array('status' => 'payment found');
        }
    }
}