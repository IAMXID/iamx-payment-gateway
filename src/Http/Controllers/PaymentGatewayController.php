<?php

namespace IAMXID\IamxPaymentGateway\Http\Controllers;

use IAMXID\IamxPaymentGateway\Models\IamxUserPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentGatewayController extends Controller
{
    public function setNewPayment(Request $request) {
        $paymentUUID = $request->uuid;
        $walletReceiver = $request->wallet_receiver;
        $walletSender = $request->wallet_sender;
        $after_blockheight = $request->after_blockheight;
        $token_amount = $request->token_amount;
        $token_policy_id = $request->token_policy_id;
        $token_name_hex = $request->token_name_hex;

        $error_message = '';

        if (!isset($paymentUUID)) {
            $error_message .= 'uuid can not be null';
        }
        if (!isset($walletReceiver)) {
            $error_message .= 'Wallet receiver address can not be null';
        }
        if (!isset($walletSender)) {
            $error_message .= 'Wallet sender address can not be null';
        }
        if (!isset($after_blockheight)) {
            $error_message .= 'After blockheight can not be null';
        }
        if (!isset($token_amount)) {
            $error_message .= 'Token amount can not be null';
        }

        if ($error_message != '') {
            return array('status' => $error_message);
        }

        $newPayment = IamxUserPayment::updateOrCreate(
            [
                'payment_uuid' => $paymentUUID
            ],[
                'wallet_receiver' => $walletReceiver,
                'wallet_sender' => $walletSender,
                'after_blockheight' => $after_blockheight,
                'token_amount' => $token_amount,
                'token_policy' => $token_policy_id,
                'asset_name_hex' => $token_name_hex
            ]
        );

        if ($newPayment) {
            return array('status' => 'Payment UUID has been inserted to the database.');
        } else {
            return array('status' => 'Something went wrong while inserting the new row to the database.');
        }

    }

    public function checkPayment(Request $request) {
        $paymentUUID = $request->uuid;

        $error_message = '';

        if (!isset($paymentUUID)) {
            $error_message .= 'uuid can not be null';
        }

        if ($error_message != '') {
            return array('status' => $error_message);
        }

        $userpayment = DB::table('iamx_user_payments')
            ->select('is_paid', 'tx_id')
            ->where('payment_uuid', '=', $paymentUUID)
            ->first();

        if (!$userpayment) {
            return array('status' => 'UUID not found in the database');
        }

        if ($userpayment->is_paid == 1) {
            return array('status' => 'payment found', 'tx_hash' => $userpayment->tx_id);
        } else {
            return array('status' => 'Payment not yet found');
        }
    }
}