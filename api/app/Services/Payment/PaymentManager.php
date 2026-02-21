<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

/**
 * Gerenciador de gateways de pagamento.
 *
 * Responsável por resolver a instância correta do gateway
 * com base na configuração ou no nome informado.
 */
class PaymentManager
{
    /**
     * Retorna a instância do gateway solicitado.
     *
     * Caso nenhum nome seja informado, utiliza o gateway padrão
     * definido em config('payment.default').
     */
    public function gateway(?string $name = null): PaymentGatewayInterface
    {
        $name = $name ?: config('payment.default');

        $config = config("payment.gateways.{$name}");

        if (! $config || ! isset($config['class'])) {
            throw new InvalidArgumentException("Gateway de pagamento [{$name}] não configurado.");
        }

        return app($config['class']);
    }
}
