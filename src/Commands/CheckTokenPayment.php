<?php

namespace IAMXID\IamxPaymentGateway\Commands;

use IAMXID\IamxPaymentGateway\Models\IamxUserPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckTokenPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iamx_payment_gateway:checkTokenPayment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks the blockchain for the transaction of the open payments.';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        if(env('PAYMENT_GATEWAY_LOGGER')) {
            Log::channel('paymentGateway')->info('checkTokenPayment started.');
        }

        // Select open payments
        $openPayments = DB::table('iamx_user_payments')
            ->where('is_paid', '=', false)
            ->get();


        // Loop over open payments
        foreach ($openPayments as $openPayment) {

            if(env('PAYMENT_GATEWAY_LOGGER')) {
                Log::channel('paymentGateway')->info('Open payment for address '.$openPayment->wallet_sender.' found');
            }

            $paymentFound = false;
            $paymentTxID = null;

            $responseAccountAddresses = Http::retry(5, 100)
                ->timeout(30)
                ->withHeaders(['Accept' => 'application/json', 'project_id' => config('blockfrost.project_id')])
                ->get('https://cardano-mainnet.blockfrost.io/api/v0/accounts/'.$openPayment->wallet_sender.'/addresses');

            $accountAddresses = json_decode($responseAccountAddresses->body());

            foreach ($accountAddresses as $accountAddress) {

                if(env('PAYMENT_GATEWAY_LOGGER')) {
                    Log::channel('paymentGateway')->info('Wallet address '.$accountAddress->address.' is used for search');
                }

                $responseAddressTransactions = Http::retry(5, 100)
                    ->timeout(30)
                    ->withHeaders(['Accept' => 'application/json', 'project_id' => config('blockfrost.project_id')])
                    ->get('https://cardano-mainnet.blockfrost.io/api/v0/addresses/'.$accountAddress->address.'/transactions', [
                            'from' => $openPayment->after_blockheight,
                            'order' => 'desc'
                        ]
                    );

                $addressTransactions = json_decode($responseAddressTransactions->body());

                foreach ($addressTransactions as $addressTransaction) {

                    if(env('PAYMENT_GATEWAY_LOGGER')) {
                        Log::channel('paymentGateway')->info('tx-hash '.$addressTransaction->tx_hash.' used.');
                    }

                    $responseTxInfo = Http::retry(5, 100)
                        ->timeout(30)
                        ->withHeaders(['Accept' => 'application/json', 'project_id' => config('blockfrost.project_id')])
                        ->get('https://cardano-mainnet.blockfrost.io/api/v0/txs/'.$addressTransaction->tx_hash.'/utxos');

                    $txInfo = json_decode($responseTxInfo->body());

                    $outputs = $txInfo->outputs;
                    foreach ($outputs as $output) {

                        if(env('PAYMENT_GATEWAY_LOGGER')) {
                            Log::channel('paymentGateway')->info('Output address '.$output->address.' -- receiver address '.$openPayment->wallet_receiver);
                        }

                        if ($output->address == $openPayment->wallet_receiver) {
                            foreach ($output->amount as $amount) {

                                $unit = 'lovelace';

                                if(env('PAYMENT_GATEWAY_LOGGER')) {
                                    Log::channel('paymentGateway')->info('Unit '.$amount->unit.' -- search unit '.$unit);
                                    Log::channel('paymentGateway')->info('Amount '.$amount->quantity.' -- search amount '.$openPayment->token_amount);
                                }

                                if ($openPayment->token_policy) {
                                    $unit = $openPayment->token_policy || $openPayment->asset_name_hex;
                                }

                                if ($amount->unit == $unit
                                    && $amount->quantity == $openPayment->token_amount) {

                                    if(env('PAYMENT_GATEWAY_LOGGER')) {
                                        Log::channel('paymentGateway')->info('Payment found');
                                    }
                                    $paymentFound = true;
                                    $paymentTxID = $addressTransaction->tx_hash;
                                    break;
                                } else {
                                    if(env('PAYMENT_GATEWAY_LOGGER')) {
                                        Log::channel('paymentGateway')->info('Payment not yet found');
                                    }
                                }
                            }
                        }
                        if ($paymentFound) {
                            break;
                        }
                    }
                    if ($paymentFound) {
                        break;
                    }
                }
                if ($paymentFound) {
                    break;
                }
            }

            if ($paymentFound) {
                IamxUserPayment::where('id', '=', $openPayment->id)
                    ->update(['is_paid' => true, 'tx_id' => $paymentTxID]);
            }

        }
        if(env('PAYMENT_GATEWAY_LOGGER')) {
            Log::channel('paymentGateway')->info('checkTokenPayment finished.');
        }
    }
}