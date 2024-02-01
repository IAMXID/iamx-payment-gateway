<?php

namespace IAMXID\IamxPaymentGateway\Commands;

use IAMXID\IamxPaymentGateway\Models\IamxUserPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
//use Illuminate\Support\Facades\Log;

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
        // Select open payments
        $openPayments = DB::table('iamx_user_payments')
            ->where('is_paid', '=', false)
            ->get();


        // Loop over open payments
        foreach ($openPayments as $openPayment) {

            //Log::info('Open payment for address '.$openPayment->wallet_sender.' found');

            $paymentFound = false;
            $paymentTxID = null;

            $responseAddressTransactions = Http::retry(5, 100)
                ->timeout(30)
                ->withHeaders(['Accept' => 'application/json', 'project_id' => config('blockfrost.project_id')])
                ->get('https://cardano-mainnet.blockfrost.io/api/v0/addresses/'.$openPayment->wallet_sender.'/transactions', [
                        'from' => $openPayment->after_blockheight,
                        'order' => 'desc'
                    ]
                );

            $addressTransactions = json_decode($responseAddressTransactions->body());

            foreach ($addressTransactions as $addressTransaction) {

                //Log::info('tx-hash '.$addressTransaction->tx_hash.' used.');

                $responseTxInfo = Http::retry(5, 100)
                    ->timeout(30)
                    ->withHeaders(['Accept' => 'application/json', 'project_id' => config('blockfrost.project_id')])
                    ->get('https://cardano-mainnet.blockfrost.io/api/v0/txs/'.$addressTransaction->tx_hash.'/utxos');

                $txInfo = json_decode($responseTxInfo->body());

                $outputs = $txInfo->outputs;
                foreach ($outputs as $output) {

                    //Log::info('Output address '.$output->address.' -- receiver address '.$openPayment->wallet_receiver);

                    if ($output->address == $openPayment->wallet_receiver) {
                        foreach ($output->amount as $amount) {

                            //Log::info('Unit '.$amount->unit.' -- search unit '.$openPayment->token_policy || $openPayment->asset_name_hex);
                            //Log::info('Amount '.$amount->quantity.' -- search amount '.$openPayment->token_amount);

                            if ($amount->unit == $openPayment->token_policy || $openPayment->asset_name_hex
                                && $amount->quantity = $openPayment->token_amount) {

                                //Log::info('Payment founnd');
                                $paymentFound = true;
                                $paymentTxID = $addressTransaction->tx_hash;
                                break;
                            } else {
                                //Log::info('Payment not yet founnd');
                            }
                        }
                    }

                    if ($paymentFound) {
                        break;
                    }
                }
            }

            if ($paymentFound) {
                IamxUserPayment::where('id', '=', $openPayment->id)
                    ->update(['is_paid' => true, 'tx_id' => $paymentTxID]);
            }

        }
    }
}