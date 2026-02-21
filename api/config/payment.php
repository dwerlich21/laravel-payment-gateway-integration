<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gateway Padrão
    |--------------------------------------------------------------------------
    |
    | Define qual gateway de pagamento será utilizado por padrão quando
    | nenhum for especificado explicitamente na criação de cobranças.
    |
    */

    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'stripe'),

    /*
    |--------------------------------------------------------------------------
    | Gateways Disponíveis
    |--------------------------------------------------------------------------
    |
    | Configuração de cada gateway de pagamento suportado pelo sistema.
    | Cada gateway define sua classe de serviço e credenciais de acesso.
    |
    */

    'gateways' => [

        'stripe' => [
            'class'          => App\Services\Payment\StripeGatewayService::class,
            'secret_key'     => env('STRIPE_SECRET_KEY'),
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],

        'asaas' => [
            'class'          => App\Services\Payment\AsaasGatewayService::class,
            'api_key'        => env('ASAAS_API_KEY'),
            'webhook_token'  => env('ASAAS_WEBHOOK_TOKEN'),
            'sandbox'        => env('ASAAS_SANDBOX', true),
        ],

    ],

];
