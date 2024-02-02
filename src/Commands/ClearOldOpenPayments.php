<?php

namespace IAMXID\IamxPaymentGateway\Commands;

use IAMXID\IamxPaymentGateway\Models\IamxUserPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearOldOpenPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iamx_payment_gateway:clearOldOpenPayments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes pending payments older than 7 days from the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if(env('PAYMENT_GATEWAY_LOGGER')) {
            Log::channel('paymentGateway')->info('---------------------------------------------------------------');
            Log::channel('paymentGateway')->info('clearOldOpenPayments started.');
        }

        // Select open payments
        $oldOpenPayments = DB::table('iamx_user_payments')
            ->where('is_paid', '=', false)
            ->whereRaw('created_at <= DATE_SUB(NOW(), INTERVAL 7 DAY) ')
            ->get();


        // Loop over open payments
        foreach ($oldOpenPayments as $oldOpenPayment) {
            $payment = IamxUserPayment::find($oldOpenPayment->id);
            $payment->delete();
            Log::channel('paymentGateway')->info('Payment with uuid '.$oldOpenPayment->payment_uuid.' created at '.$oldOpenPayment->created_at.' has been deleted from the database.');
        }

        Log::channel('paymentGateway')->info('clearOldOpenPayments finished.');
    }
}