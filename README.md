# iamx-payment-gateway

IAMX payment gateway is a Laravel package which allows users to pay a fee using ADA or cardano native token.

- [IAMX-paymwnt-gateway](#iamx-wallet-connect)
    - [Installation](#Installation)
    - [Configuration](#Configuration)
    - [Usage](#Usage)
    - [Bugs, Suggestions, Contributions and Support](#bugs-and-suggestions)
    - [Copyright and License](#copyright-and-license)

## Installation

Install the current version of the `iamxid/iamx-payment-gateway` package via composer:

```sh
    composer require iamxid/iamx-payment-gateway:dev-main
```

## Configuration

Publish the config file:

```sh
    php artisan vendor:publish --provider="IAMXID\IamxPaymentGateway\IamxPaymentGatewayServiceProvider" --tag="config"
```

Publish the migration file:

```sh
    php artisan vendor:publish --provider="IAMXID\IamxPaymentGateway\IamxPaymentGatewayServiceProvider" --tag="migrations"
```

Run the migration:

```sh
    php artisan migrate
```

Add the Blockfrost project id to the file /config/blockfrost.php

```
return [
'project_id' => 'mainnet...',
];
```

Add the variable PAYMENT_GATEWAY_LOGGER to the env file to active logging of the package.

```
PAYMENT_GATEWAY_LOGGER=true
```

## Usage

Use the trait UsePaymentController in your controller to insert a new payment to the database, check if the payment is pending or confirmed.
```php
<?php

namespace App\Http\Controllers;

use IAMXID\IamxPaymentGateway\Traits\UsePaymentGateway;

class TestController extends Controller
{

    use UsePaymentGateway;
    public function test() {

        // Insert a new payment into the database

        $uuid = '1122ABC';
        $wallet_receiver = 'addr1...';
        $wallet_sender = 'stake1...';
        $after_blockheight = 9881942;
        $token_amount = 10;
        $token_policy_id = '12d5f4fefe222d52a4fdcee56f4b272911d7c2202b068a08ebf53270';
        $token_name_hex = '49414d58';

        // Payment in native token (Example 10 IAMX token)
        $returnValue1 = $this->setPayment(
            $uuid,
            $wallet_receiver,
            $wallet_sender,
            $after_blockheight,
            $token_amount,
            $token_policy_id,
            $token_name_hex
        );

        // Payment in ADA (Example 10 ADA)
        $returnValue2 = $this->setPayment(
            $uuid,
            $wallet_receiver,
            $wallet_sender,
            $after_blockheight,
            $token_amount
        );

        // Check if the payment is still pending
        $returnValue3 = $this->isPendingPayment($uuid);

        // Check if the payment is confirmed
        $returnValue4 = $this->checkForPayment($uuid);


    }
}
```

Use the GET route [ROOT_URL]/iamx_payment_gateway/checkPayment to check if the payment is confirmed by the blockchain

```
curl --location '[ROOT_URL]/iamx_payment_gateway/checkPayment?uuid=[UUID of the transaction]'
```

Setup a cronjob which executes the command iamx_payment_gateway:checkTokenPayment every minute. This command will check blockfrost for the confirmation of the open payments.

```
* * * * * cd /[PATH TO THE PROJECT]; php artisan iamx_payment_gateway:checkTokenPayment >> /dev/null 2>&1
```

Setup a cronjob which executes the command iamx_payment_gateway:clearOldOpenPayments every day. This command will delete all pending payments older than 7 days from the database

```
0 1 * * * cd /[PATH TO THE PROJECT]; php artisan iamx_payment_gateway:clearOldOpenPayments >> /dev/null 2>&1
```


## Bugs and Suggestions

## Copyright and License

[MIT](https://choosealicense.com/licenses/mit/)
