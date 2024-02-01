# iamx-payment-gateway

IAMX payment gateway is a Laravel package which allows users to pay a fee using cardano native token.

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
    php artisan vendor:publish --provider="IAMXID\PaymentGateway\PaymentGatewayServiceProvider" --tag="migrations"
```

Run the migration:

```sh
    php artisan migrate
```

Add the Blockfrost project id to the .env file. Example:

```
BLOCKFROST_PROJECT_ID=mainnet...
```

Add the Blockfrost project id to the file /config/blockfrost.php

```
return [
'project_id' => 'mainnet...',
];
```

## Usage

Use the POST route [ROOT_URL]/iamx_payment_gateway/setNewPayment to insert a new payment to the database

Payment in native token

```
curl --location --request POST '[ROOT_URL]/iamx_payment_gateway/setNewPayment?uuid=[UUID of the transaction]&wallet_receiver=[wallet address of the receiver]&wallet_sender=[wallet address of the sender]&after_blockheight=[blockheight before the transaction]&token_amount=[amount of token]&token_policy_id=[token policy id]&token_name_hex=[token name in hex format]'
```

Payment in ADA

```
curl --location --request POST '[ROOT_URL]/iamx_payment_gateway/setNewPayment?uuid=[UUID of the transaction]&wallet_receiver=[wallet address of the receiver]&wallet_sender=[wallet address of the sender]&after_blockheight=[blockheight before the transaction]&token_amount=[amount of ADA in lovelace]'
```

Use the GET route [ROOT_URL]/iamx_payment_gateway/checkPayment to check if the payment is confirmed by the blockchain

```
curl --location '[ROOT_URL]/iamx_payment_gateway/checkPayment?uuid=[UUID of the transaction]'
```

Setup a cronjob which executes the command iamx_payment_gateway:checkTokenPayment every minute

```
* * * * * cd /[PATH TO THE PROJECT]; php artisan iamx_payment_gateway:checkTokenPayment >> /dev/null 2>&1
```


## Bugs and Suggestions

## Copyright and License

[MIT](https://choosealicense.com/licenses/mit/)
