<?php

namespace IAMXID\IamxPaymentGateway\Traits;

use IAMXID\IamxPaymentGateway\Models\IamxUserPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait UsePaymentGateway {

    public function setPayment($uuid, $wallet_receiver, $wallet_sender, $after_blockheight, $token_amount, $token_policy_id = null, $token_name_hex = null)
    {
        if(env('PAYMENT_GATEWAY_LOGGER')) {
            Log::channel('paymentGateway')->info('---------------------------------------------------------------');
            Log::channel('paymentGateway')->info('setPayment has been called.');
            Log::channel('paymentGateway')->info('uuid: '.$uuid);
            Log::channel('paymentGateway')->info('wallet_receiver: '.$wallet_receiver);
            Log::channel('paymentGateway')->info('wallet_sender: '.$wallet_sender);
            Log::channel('paymentGateway')->info('after_blockheight: '.$after_blockheight);
            Log::channel('paymentGateway')->info('token_amount: '.$token_amount);
            Log::channel('paymentGateway')->info('token_policy_id: '.$token_policy_id);
            Log::channel('paymentGateway')->info('token_name_hex: '.$token_name_hex);
        }

        $error_message = '';

        if (!isset($uuid)) {
            $error_message .= 'uuid can not be null. ';
        }
        if (!isset($wallet_receiver)) {
            $error_message .= 'Wallet receiver address can not be null. ';
        }
        if (!isset($wallet_sender)) {
            $error_message .= 'Wallet sender address can not be null. ';
        }
        if (!isset($after_blockheight)) {
            $error_message .= 'After blockheight can not be null. ';
        }
        if (!isset($token_amount)) {
            $error_message .= 'Token amount can not be null.';
        }

        if ($error_message != '') {
            Log::channel('paymentGateway')->info('Error: '.$error_message);
            Log::channel('paymentGateway')->info('setPayment finished.');
            return false;
        }

        $newPayment = IamxUserPayment::updateOrCreate(
            [
                'payment_uuid' => $uuid
            ],[
                'wallet_receiver' => $wallet_receiver,
                'wallet_sender' => $wallet_sender,
                'after_blockheight' => $after_blockheight,
                'token_amount' => $token_amount,
                'token_policy' => $token_policy_id,
                'asset_name_hex' => $token_name_hex
            ]
        );

        if ($newPayment) {
            if(env('PAYMENT_GATEWAY_LOGGER')) {
                Log::channel('paymentGateway')->info('New payment with uuid '.$uuid.' has been inserted to the database');
                Log::channel('paymentGateway')->info('setPayment finished.');
            }
            return true;
        } else {
            if(env('PAYMENT_GATEWAY_LOGGER')) {
                Log::channel('paymentGateway')->info('New payment with uuid '.$uuid.' has not been inserted to the database');
                Log::channel('paymentGateway')->info('setPayment finished.');
            }
            return false;
        }
    }

    public function checkForPayment($uuid)
    {
        if(env('PAYMENT_GATEWAY_LOGGER')) {
            Log::channel('paymentGateway')->info('---------------------------------------------------------------');
            Log::channel('paymentGateway')->info('checkForPayment has been called.');
            Log::channel('paymentGateway')->info('uuid: '.$uuid);
        }

        $error_message = '';

        if (!isset($uuid)) {
            $error_message .= 'uuid can not be null';
        }

        if ($error_message != '') {
            Log::channel('paymentGateway')->info('Error: '.$error_message);
            Log::channel('paymentGateway')->info('checkForPayment finished.');
            return false;
        }

        $userpayment = DB::table('iamx_user_payments')
            ->select('is_paid', 'tx_id')
            ->where('payment_uuid', '=', $uuid)
            ->first();

        if (!$userpayment) {
            if(env('PAYMENT_GATEWAY_LOGGER')) {
                Log::channel('paymentGateway')->info('UUID '.$uuid.' not found in the database.');
                Log::channel('paymentGateway')->info('checkForPayment finished.');
            }
            return false;
        }

        if ($userpayment->is_paid == 1) {
            if(env('PAYMENT_GATEWAY_LOGGER')) {
                Log::channel('paymentGateway')->info('Payment for UUID '.$uuid.' found in the database. Tx-hash: '.$userpayment->tx_id);
                Log::channel('paymentGateway')->info('checkForPayment finished.');
            }
            return true;
        } else {
            if(env('PAYMENT_GATEWAY_LOGGER')) {
                Log::channel('paymentGateway')->info('Payment for UUID '.$uuid.' not yet found.');
                Log::channel('paymentGateway')->info('checkForPayment finished.');
            }
            return false;
        }
    }

    public function isPendingPayment($uuid)
    {
        if(env('PAYMENT_GATEWAY_LOGGER')) {
            Log::channel('paymentGateway')->info('---------------------------------------------------------------');
            Log::channel('paymentGateway')->info('isPending has been called.');
            Log::channel('paymentGateway')->info('uuid: '.$uuid);
        }

        $error_message = '';

        if (!isset($uuid)) {
            $error_message .= 'uuid can not be null';
        }

        if ($error_message != '') {
            Log::channel('paymentGateway')->info('Error: '.$error_message);
            Log::channel('paymentGateway')->info('isPending finished.');
            return false;
        }

        $userpayment = DB::table('iamx_user_payments')
            ->select('tx_id')
            ->where('payment_uuid', '=', $uuid)
            ->where('is_paid', '=', false)
            ->first();

        if (!$userpayment) {
            if(env('PAYMENT_GATEWAY_LOGGER')) {
                Log::channel('paymentGateway')->info('UUID '.$uuid.' not found.');
                Log::channel('paymentGateway')->info('checkForPayment finished.');
            }
            return false;
        } else {
            if(env('PAYMENT_GATEWAY_LOGGER')) {
                Log::channel('paymentGateway')->info('UUID '.$uuid.' found. Payment is pending.');
                Log::channel('paymentGateway')->info('checkForPayment finished.');
            }
            return true;
        }

    }
}